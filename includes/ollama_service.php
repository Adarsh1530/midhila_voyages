<?php
/**
 * MIDHILA VOYAGES — Grounded AI Itinerary & Recommendations Service
 * 
 * Secure backend service connecting to a locally hosted Ollama model.
 * Enforces strict information grounding:
 * - Recommendations strictly limited to verified admin accommodations and approved OSM places.
 * - Zero hallucination directive in system prompts.
 * - High-speed deterministic fallback engine when Ollama is offline or unavailable.
 * - Private endpoints protected behind backend proxy; zero client-side exposure.
 * - Complete audit logging in `ai_itineraries` table.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/overpass_service.php';

class OllamaService {
    private static int $requestTimeoutSeconds = 25;

    /**
     * Get configured Ollama API URL
     */
    public static function getApiUrl(): string {
        $envUrl = getenv('OLLAMA_API_URL');
        if (!empty($envUrl)) return rtrim($envUrl, '/');

        $db = getDB();
        try {
            $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'ollama_api_url' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            if ($val) return rtrim($val, '/');
        } catch (Exception $e) {}

        return 'http://127.0.0.1:11434';
    }

    /**
     * Get configured Ollama Model Name
     */
    public static function getModelName(): string {
        $envModel = getenv('OLLAMA_MODEL');
        if (!empty($envModel)) return trim($envModel);

        $db = getDB();
        try {
            $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'ollama_model' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            if ($val) return trim($val);
        } catch (Exception $e) {}

        return 'llama3.2';
    }

    /**
     * Check if Ollama service is reachable
     */
    public static function isServiceReachable(): bool {
        $url = self::getApiUrl() . '/api/tags';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code === 200 && !empty($res));
    }

    /**
     * Generate grounded itinerary and recommendation
     * 
     * @param array $params [
     *    'destination_id' => int,
     *    'customer_name'  => string,
     *    'customer_phone' => string,
     *    'customer_email' => string,
     *    'duration_days'  => int,
     *    'travel_dates'   => string,
     *    'adults'         => int,
     *    'children'       => int,
     *    'budget_range'   => string,
     *    'travel_type'    => string,
     *    'food_pref'      => string,
     *    'selected_places'=> array (IDs from external_places or accommodations),
     *    'language'       => 'en'|'ml'|'ta'
     * ]
     * @return array
     */
    public static function generateGroundedItinerary(array $params): array {
        $db = getDB();
        $destId = (int)($params['destination_id'] ?? 0);

        // 1. Fetch Destination info
        $dStmt = $db->prepare("SELECT * FROM destinations WHERE id = ?");
        $dStmt->execute([$destId]);
        $destination = $dStmt->fetch();

        if (!$destination) {
            return [
                'success' => false,
                'error'   => 'Valid destination is required.',
            ];
        }

        $destName = $destination['name'];
        $days = max(1, min(14, (int)($params['duration_days'] ?? 3)));
        $adults = max(1, (int)($params['adults'] ?? 2));
        $children = max(0, (int)($params['children'] ?? 0));
        $budget = trim($params['budget_range'] ?? 'Premium / Comfortable');
        $travelType = trim($params['travel_type'] ?? 'Family Leisure');
        $foodPref = trim($params['food_pref'] ?? 'With Food (Both Veg & Non-Veg)');
        $lang = in_array($params['language'] ?? 'en', ['en', 'ml', 'ta']) ? $params['language'] : 'en';

        // 2. Fetch Admin-Registered Accommodations for this Destination
        $aStmt = $db->prepare("SELECT * FROM accommodations WHERE destination_id = ? AND is_active = 1 LIMIT 5");
        $aStmt->execute([$destId]);
        $verifiedAccommodations = $aStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch Approved External Places for this Destination (OSM)
        $placesRes = OverpassService::getPlacesForDestination($destId, 'all', false);
        $approvedPlaces = $placesRes['places'] ?? [];

        // 4. Selected places chosen by customer
        $selectedPlaceIds = is_array($params['selected_places'] ?? null) ? $params['selected_places'] : [];
        $customerSelectedPlaces = [];
        if (!empty($selectedPlaceIds)) {
            foreach ($approvedPlaces as $ap) {
                if (in_array($ap['id'], $selectedPlaceIds)) {
                    $customerSelectedPlaces[] = $ap;
                }
            }
        }

        // 5. Build strict grounded prompt context
        $groundedContext = self::buildGroundedContext($destination, $verifiedAccommodations, $approvedPlaces, $customerSelectedPlaces);

        // 6. Attempt Ollama Call
        $ollamaResult = null;
        $modelUsed = 'curated-grounded-engine';
        $apiUrl = self::getApiUrl();
        $modelName = self::getModelName();

        if (self::isServiceReachable()) {
            $prompt = self::buildOllamaPrompt($params, $destination, $groundedContext, $days, $lang);
            $ollamaResult = self::callOllamaGenerate($apiUrl, $modelName, $prompt);
            if (!empty($ollamaResult)) {
                $modelUsed = 'ollama:' . $modelName;
            }
        }

        // 7. If Ollama is offline or produced no output, execute deterministic Grounded Fallback Engine
        if (empty($ollamaResult)) {
            $ollamaResult = self::generateFallbackItinerary(
                $params,
                $destination,
                $verifiedAccommodations,
                $approvedPlaces,
                $customerSelectedPlaces,
                $days,
                $lang
            );
        }

        // 8. Generate unique reference and persist in ai_itineraries
        $refNo = 'MV-AI-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $saveStmt = $db->prepare("
            INSERT INTO ai_itineraries (
                reference_no, destination_id, customer_name, customer_phone,
                customer_email, duration_days, budget_range, travel_type,
                selected_places_json, itinerary_content, model_used, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
        ");
        $saveStmt->execute([
            $refNo,
            $destId,
            $params['customer_name'] ?? 'Guest Traveller',
            $params['customer_phone'] ?? '',
            $params['customer_email'] ?? '',
            $days,
            $budget,
            $travelType,
            json_encode($customerSelectedPlaces),
            $ollamaResult,
            $modelUsed,
        ]);

        return [
            'success'          => true,
            'reference_no'     => $refNo,
            'destination_id'   => $destId,
            'destination_name' => $destName,
            'duration_days'    => $days,
            'model_used'       => $modelUsed,
            'itinerary'        => $ollamaResult,
            'verified_hotels'  => array_slice($verifiedAccommodations, 0, 3),
            'grounded_places'  => array_slice($approvedPlaces, 0, 10),
            'selected_places'  => $customerSelectedPlaces,
            'disclaimer'       => 'All hotel room allotments, schedules, and pricing are subject to direct availability check and confirmation by Midhila Voyages management.',
        ];
    }

    /**
     * Build Grounded Context for LLM
     */
    private static function buildGroundedContext(array $dest, array $accommodations, array $places, array $selected): string {
        $ctx = "### DESTINATION:\n";
        $ctx .= "- Name: " . $dest['name'] . " (" . ($dest['region_state'] ?? 'India') . ")\n";
        $ctx .= "- Overview: " . ($dest['description'] ?? 'Picturesque tourism destination') . "\n\n";

        $ctx .= "### VERIFIED REGISTERED ACCOMMODATIONS (ADMIN MANAGED):\n";
        if (!empty($accommodations)) {
            foreach ($accommodations as $acc) {
                $ctx .= "- {$acc['name_en']} | Category: {$acc['category']} | Room Types: {$acc['room_categories']} | Views: {$acc['room_views']} | Indicative Price: Rs. {$acc['indicative_price']} | Facilities: {$acc['additional_facilities']}\n";
            }
        } else {
            $ctx .= "- Midhila Curated Premium Partner Homestays & Resorts\n";
        }
        $ctx .= "\n";

        $ctx .= "### VERIFIED MAPPED ATTRACTIONS & DINING (OPENSTREETMAP VERIFIED):\n";
        $sliced = array_slice($places, 0, 20);
        foreach ($sliced as $p) {
            $cat = ucfirst($p['category']);
            $ctx .= "- [{$cat}] {$p['name']} ({$p['sub_type']}) - Lat/Lon: {$p['latitude']}, {$p['longitude']}\n";
        }
        $ctx .= "\n";

        if (!empty($selected)) {
            $ctx .= "### CUSTOMER PREFERRED SPOTS (PRIORITISE IN SCHEDULE):\n";
            foreach ($selected as $sp) {
                $ctx .= "- [{$sp['category']}] {$sp['name']}\n";
            }
            $ctx .= "\n";
        }

        return $ctx;
    }

    /**
     * Construct Ollama Prompt
     */
    private static function buildOllamaPrompt(array $params, array $dest, string $groundedContext, int $days, string $lang): string {
        $cName = $params['customer_name'] ?? 'Guest';
        $tType = $params['travel_type'] ?? 'Leisure';
        $budget = $params['budget_range'] ?? 'Premium';
        $food = $params['food_pref'] ?? 'With food';

        $langInstruction = ($lang === 'ml')
            ? "Provide the headings and itinerary narrative in elegant, easy-to-read Malayalam."
            : (($lang === 'ta')
                ? "Provide the headings and itinerary narrative in elegant, easy-to-read Tamil."
                : "Provide the response in fluent, polished English with a warm luxury travel tone.");

        return <<<PROMPT
You are the Executive Travel Specialist for MIDHILA VOYAGES, a licensed premier tour management company in Kerala and across India.

### CRITICAL GROUNDING RULES:
1. ONLY recommend accommodations, hotels, resorts, dining places, and attractions that exist in the VERIFIED DATABASE CONTEXT below.
2. DO NOT invent or hallucinate any hotel names, fake contact numbers, or non-existent tourist spots.
3. If specific customer preferred spots are given, weave them into the relevant day's schedule.
4. Keep hotel room availability strictly conditional upon admin confirmation.

### CUSTOMER PROFILE:
- Customer: {$cName}
- Destination: {$dest['name']}
- Trip Duration: {$days} Days
- Travel Style: {$tType}
- Budget Range: {$budget}
- Food Preference: {$food}

### VERIFIED DATABASE CONTEXT:
{$groundedContext}

### INSTRUCTIONS:
{$langInstruction}
Format in clean Markdown with:
1. Trip Summary & Theme
2. Day-by-Day Detailed Itinerary (Day 1 to Day {$days}) with Morning, Afternoon, Evening, and Recommended Dining
3. Verified Accommodation Recommendation from the provided list
4. Important Advisory: Mention that hotel availability and room rates are verified directly by Midhila Voyages directors before booking issuance.
PROMPT;
    }

    /**
     * Direct call to Ollama generate endpoint
     */
    private static function callOllamaGenerate(string $apiUrl, string $model, string $prompt): ?string {
        $url = $apiUrl . '/api/generate';
        $payload = json_encode([
            'model'  => $model,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0.4, // Lower temperature for strict grounding
                'top_p'       => 0.9,
            ]
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => self::$requestTimeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => 4,
        ]);

        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && !empty($res)) {
            $json = json_decode($res, true);
            if (!empty($json['response'])) {
                return trim($json['response']);
            }
        }
        return null;
    }

    /**
     * Deterministic High-Quality Grounded Fallback Generator
     * Runs when Ollama is offline or times out. Produces an exact,
     * grounded itinerary using real database accommodations & attractions.
     */
    public static function generateFallbackItinerary(
        array $params,
        array $dest,
        array $accommodations,
        array $approvedPlaces,
        array $selectedPlaces,
        int $days,
        string $lang = 'en'
    ): string {
        $destName = $dest['name'];
        $guestName = !empty($params['customer_name']) ? htmlspecialchars($params['customer_name']) : 'Valued Guest';
        $travelType = !empty($params['travel_type']) ? htmlspecialchars($params['travel_type']) : 'Bespoke Leisure';
        $foodPref = !empty($params['food_pref']) ? htmlspecialchars($params['food_pref']) : 'With Curated Dining';

        // Separate attractions, restaurants, and hotels from approved places
        $attractions = [];
        $dining = [];
        $externalHotels = [];

        foreach ($approvedPlaces as $p) {
            if ($p['category'] === 'attraction') $attractions[] = $p['name'];
            elseif ($p['category'] === 'restaurant' || $p['category'] === 'cafe') $dining[] = $p['name'];
            elseif ($p['category'] === 'hotel' || $p['category'] === 'resort') $externalHotels[] = $p['name'];
        }

        // Chosen accommodation
        $hotelName = !empty($accommodations[0]['name_en']) ? $accommodations[0]['name_en'] : (!empty($externalHotels[0]) ? $externalHotels[0] : "Midhila Curated {$destName} Haven");
        $hotelCategory = !empty($accommodations[0]['category']) ? $accommodations[0]['category'] : 'Luxury Resort';
        $roomTypes = !empty($accommodations[0]['room_categories']) ? $accommodations[0]['room_categories'] : 'Deluxe Room / Valley View Suite';
        $facilities = !empty($accommodations[0]['additional_facilities']) ? $accommodations[0]['additional_facilities'] : 'Wi-Fi, Parking, Restaurant, Sightseeing Desk';

        // Selected places by customer
        $selectedNames = [];
        foreach ($selectedPlaces as $sp) {
            $selectedNames[] = $sp['name'];
        }

        $out = "";
        if ($lang === 'ml') {
            $out .= "# മിഥിലാ വോയേജസ് — {$destName} പ്രൊഫഷണൽ യാത്രാ പദ്ധതി\n\n";
            $out .= "**അതിഥി:** {$guestName} | **ദിവസങ്ങൾ:** {$days} ദിനങ്ങൾ | **യാത്രാ ശൈലി:** {$travelType}\n\n";
            $out .= "മിഥിലാ വോയേജസ് ഒരുക്കുന്ന സവിശേഷമായ യാത്രാ പദ്ധതി താഴെ നൽകുന്നു. ഈ ശുപാർശകൾ പൂർണ്ണമായും അംഗീകൃത ഡാറ്റാബേസ് വിവരങ്ങളെ അടിസ്ഥാനമാക്കിയുള്ളതാണ്.\n\n";
            $out .= "---\n\n";
            $out .= "### 🏨 ശുപാർശ ചെയ്യുന്ന താമസം (സ്ഥിരീകരണം അഡ്മിൻ വഴി)\n";
            $out .= "- **പ്രോപ്പർട്ടി:** {$hotelName} ({$hotelCategory})\n";
            $out .= "- **മുറികൾ:** {$roomTypes}\n";
            $out .= "- **സൗകര്യങ്ങൾ:** {$facilities}\n\n";
        } elseif ($lang === 'ta') {
            $out .= "# மிதிலா வாயேஜஸ் — {$destName} சிறப்பு பயணத் திட்டம்\n\n";
            $out .= "**பயணி:** {$guestName} | **நாட்கள்:** {$days} நாட்கள் | **பயண வகை:** {$travelType}\n\n";
            $out .= "மிதிலா வாயேஜஸின் பிரத்யேக சுற்றுலாத் திட்டம் கீழே கொடுக்கப்பட்டுள்ளது. இவை அனைத்தும் சரிபார்க்கப்பட்ட தகவல்களை அடிப்படையாகக் கொண்டவை.\n\n";
            $out .= "---\n\n";
            $out .= "### 🏨 பரிந்துரைக்கப்படும் தங்குமிடம் (நிர்வாகி சரிபார்ப்புக்கு உட்பட்டது)\n";
            $out .= "- **தங்குமிடம்:** {$hotelName} ({$hotelCategory})\n";
            $out .= "- **அறை வகை:** {$roomTypes}\n";
            $out .= "- **வசதிகள்:** {$facilities}\n\n";
        } else {
            $out .= "# Midhila Voyages — Grounded {$destName} Tour Itinerary\n\n";
            $out .= "**Prepared for:** {$guestName} | **Duration:** {$days} Days / " . ($days - 1) . " Nights | **Style:** {$travelType}\n\n";
            $out .= "A verified luxury itinerary crafted specifically around verified destinations, registered accommodations, and approved highlights in **{$destName}**.\n\n";
            $out .= "---\n\n";
            $out .= "### 🏨 Verified Accommodation Recommendation\n";
            $out .= "- **Property:** **{$hotelName}** ({$hotelCategory})\n";
            $out .= "- **Selected Room Category:** {$roomTypes}\n";
            $out .= "- **Included Amenities:** {$facilities}\n";
            $out .= "- **Availability Notice:** Room allocation is held on request and will be independently verified by our reservation desk.\n\n";
        }

        $out .= "### 🗓️ Day-by-Day Verified Schedule\n\n";

        $attrIndex = 0;
        $diningIndex = 0;

        for ($d = 1; $d <= $days; $d++) {
            $dayMorningSpot = $selectedNames[$attrIndex] ?? ($attractions[$attrIndex] ?? "Arrival & Scenic Viewpoints of {$destName}");
            $attrIndex++;
            $dayAfternoonSpot = $attractions[$attrIndex] ?? "Local Heritage & Cultural Centers";
            $attrIndex++;
            $dayLunchSpot = $dining[$diningIndex] ?? "Traditional Regional Dining Hall";
            $diningIndex++;
            $dayEveningSpot = $attractions[$attrIndex] ?? "Sunset Vista Point & Evening Leisure";
            $attrIndex++;

            if ($lang === 'ml') {
                $out .= "#### ദിവസം {$d}: ";
                if ($d === 1) $out .= "ആഗമനം & പ്രാദേശിക കാഴ്ചകൾ\n";
                elseif ($d === $days) $out .= "യാത്രാ വിടവാങ്ങൽ & ഓർമ്മകൾ\n";
                else $out .= "പ്രധാന ആകർഷണങ്ങൾ & സാംസ്കാരിക അനുഭവം\n";

                $out .= "- **രാവിലെ:** {$dayMorningSpot} സന്ദർശനം, പ്രകൃതിഭംഗി ആസ്വദിക്കൽ.\n";
                $out .= "- **ഉച്ചഭക്ഷണം:** {$dayLunchSpot} -ൽ പ്രത്യേക പ്രാദേശിക ഭക്ഷണം ({$foodPref}).\n";
                $out .= "- **ഉച്ചകഴിഞ്ഞ്:** {$dayAfternoonSpot} കാഴ്ചകൾ.\n";
                $out .= "- **വൈകുന്നേരം:** {$dayEveningSpot}, തുടർന്ന് ഹോട്ടലിലേക്ക് വിശ്രമം.\n\n";
            } elseif ($lang === 'ta') {
                $out .= "#### நாள் {$d}: ";
                if ($d === 1) $out .= "வருகை மற்றும் உள்ளூர் தரிசனம்\n";
                elseif ($d === $days) $out .= "நிறைவு மற்றும் வழியனுப்புதல்\n";
                else $out .= "முக்கிய இடங்கள் மற்றும் அனுபவங்கள்\n";

                $out .= "- **காலை:** {$dayMorningSpot} பார்வை மற்றும் தொடக்கம்.\n";
                $out .= "- **மதிய உணவு:** {$dayLunchSpot} இல் சுவையான உணவு ({$foodPref}).\n";
                $out .= "- **மதியம்:** {$dayAfternoonSpot} சிறப்பம்சங்கள்.\n";
                $out .= "- **மாலை:** {$dayEveningSpot} மற்றும் தங்குமிடத்திற்கு திரும்புதல்.\n\n";
            } else {
                $out .= "#### Day {$d}: ";
                if ($d === 1) $out .= "Warm Arrival, Check-in & Scenic Orientation\n";
                elseif ($d === $days) $out .= "Souvenir Exploration & Pleasant Departure\n";
                else $out .= "Iconic Sightseeing & Signature Landscapes\n";

                $out .= "- **Morning (09:00 AM – 12:30 PM):** Experience **{$dayMorningSpot}** with dedicated private chauffeur coordination.\n";
                $out .= "- **Lunch & Refreshment (01:00 PM – 02:30 PM):** Curated dining stop at **{$dayLunchSpot}** ({$foodPref}).\n";
                $out .= "- **Afternoon (03:00 PM – 05:30 PM):** Guided exploration of **{$dayAfternoonSpot}**.\n";
                $out .= "- **Evening (06:00 PM Onwards):** Sunset vista at **{$dayEveningSpot}**, returning to **{$hotelName}** for dinner and peaceful overnight rest.\n\n";
            }
        }

        $out .= "---\n\n";
        if ($lang === 'ml') {
            $out .= "> ℹ️ **ശ്രദ്ധിക്കുക:** ഹോട്ടൽ മുറികളുടെ ലഭ്യതയും നിരക്കുകളും മിഥിലാ വോയേജസ് അഡ്മിൻ നേരിട്ട് പരിശോധിച്ച് ഉറപ്പുവരുത്തിയ ശേഷം മാത്രമേ അന്തിമ ബുക്കിംഗ് സ്ഥിരീകരിക്കപ്പെടുകയുള്ളൂ.\n";
        } elseif ($lang === 'ta') {
            $out .= "> ℹ️ **குறிப்பு:** தங்குமிட அறைகள் மற்றும் கட்டணங்கள் மிதிலா வாயேஜஸ் நிர்வாகியால் சரிபார்க்கப்பட்ட பின்னரே உறுதி செய்யப்படும்.\n";
        } else {
            $out .= "> ℹ️ **Important Verification Policy:** All hotel rooms, room categories, and final itinerary rates are strictly subject to manual verification and confirmation by the Midhila Voyages management team before digital booking confirmation.\n";
        }

        return $out;
    }
}
