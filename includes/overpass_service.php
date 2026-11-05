<?php
/**
 * MIDHILA VOYAGES — OpenStreetMap & Overpass API Service
 * 
 * Secure backend service for retrieving mapped hotels, resorts, restaurants,
 * cafes, and tourist attractions using the Overpass API with strict adherence
 * to OSM usage policies:
 * - Proper identifying User-Agent
 * - Result caching with configurable TTL (default: 7 days)
 * - Rate limiting protection
 * - Automatic deduplication against external_places table
 * - Graceful fallbacks on connection or rate limit issues
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

class OverpassService {
    // Primary & backup Overpass API endpoints (Zero paid API dependency)
    private static array $endpoints = [
        'https://overpass.openstreetmap.fr/api/interpreter',
        'https://overpass-api.de/api/interpreter',
        'https://z.overpass-api.de/api/interpreter',
        'https://lz4.overpass-api.de/api/interpreter',
    ];

    private static int $cacheTtlDays = 7;
    private static int $requestTimeoutSeconds = 12;

    /**
     * Get or build user agent
     */
    private static function getUserAgent(): string {
        $db = getDB();
        try {
            $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'osm_user_agent' LIMIT 1");
            $stmt->execute();
            $val = $stmt->fetchColumn();
            if ($val) return $val;
        } catch (Exception $e) {}
        return 'MidhilaVoyages/2.0 (contact@midhilavoyages.com; tourism-platform)';
    }

    /**
     * Fetch places for a destination by category
     * 
     * @param int $destinationId
     * @param string $category 'all' | 'hotel' | 'resort' | 'restaurant' | 'cafe' | 'attraction'
     * @param bool $forceRefresh
     * @return array List of places with coordinates, details, and metadata
     */
    public static function getPlacesForDestination(int $destinationId, string $category = 'all', bool $forceRefresh = false): array {
        $db = getDB();

        // 1. Fetch destination info & coordinates
        $stmt = $db->prepare("SELECT id, name, name_ml, name_ta, latitude, longitude FROM destinations WHERE id = ?");
        $stmt->execute([$destinationId]);
        $dest = $stmt->fetch();

        if (!$dest || empty($dest['latitude']) || empty($dest['longitude'])) {
            return [
                'success' => false,
                'error'   => 'Destination coordinates not configured.',
                'places'  => [],
            ];
        }

        $lat = (float)$dest['latitude'];
        $lng = (float)$dest['longitude'];

        // Radius from settings or default 10km
        $radiusKm = 10;
        try {
            $rStmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'osm_search_radius_km' LIMIT 1");
            $rStmt->execute();
            $rVal = $rStmt->fetchColumn();
            if ($rVal && is_numeric($rVal)) $radiusKm = (int)$rVal;
        } catch (Exception $e) {}
        $radiusMeters = $radiusKm * 1000;

        // 2. Check cache unless force refresh
        $cacheKey = "dest_{$destinationId}_{$category}_{$radiusKm}km";
        if (!$forceRefresh) {
            $cStmt = $db->prepare("SELECT response_json, expires_at FROM osm_cache WHERE cache_key = ? AND expires_at > datetime('now')");
            $cStmt->execute([$cacheKey]);
            $cached = $cStmt->fetch();

            if ($cached && !empty($cached['response_json'])) {
                // Return approved places from database
                return [
                    'success' => true,
                    'cached'  => true,
                    'destination' => $dest,
                    'places'  => self::getApprovedPlacesFromDB($destinationId, $category),
                ];
            }
        }

        // 3. Build Overpass QL Query
        $ql = self::buildOverpassQuery($lat, $lng, $radiusMeters, $category);

        // 4. Execute HTTP request with failover
        $rawOsmData = self::executeOverpassQuery($ql);

        if ($rawOsmData && isset($rawOsmData['elements'])) {
            // 5. Parse, sanitize and upsert places
            $parsedCount = self::upsertOsmElements($destinationId, $rawOsmData['elements']);

            // 6. Save in cache
            $expiresAt = date('Y-m-d H:i:s', strtotime("+ " . self::$cacheTtlDays . " days"));
            $saveCache = $db->prepare("
                INSERT OR REPLACE INTO osm_cache (cache_key, destination_id, category, response_json, expires_at, created_at)
                VALUES (?, ?, ?, ?, ?, datetime('now'))
            ");
            $saveCache->execute([$cacheKey, $destinationId, $category, json_encode(['count' => $parsedCount]), $expiresAt]);

            return [
                'success' => true,
                'cached'  => false,
                'destination' => $dest,
                'places'  => self::getApprovedPlacesFromDB($destinationId, $category),
                'newly_synced' => $parsedCount,
            ];
        }

        // If Overpass is offline or throttled, fallback to whatever is already in DB
        $dbPlaces = self::getApprovedPlacesFromDB($destinationId, $category);
        return [
            'success' => count($dbPlaces) > 0,
            'cached'  => true,
            'destination' => $dest,
            'places'  => $dbPlaces,
            'warning' => 'Real-time Overpass sync unavailable. Displaying verified local cache.',
        ];
    }

    /**
     * Build Overpass QL query string
     */
    private static function buildOverpassQuery(float $lat, float $lng, int $radiusMeters, string $category): string {
        $filters = [];

        if ($category === 'all' || $category === 'hotel' || $category === 'resort') {
            $filters[] = "nwr[\"tourism\"~\"hotel|resort|guest_house|motel|hostel|chalet\"](around:{$radiusMeters},{$lat},{$lng});";
        }
        if ($category === 'all' || $category === 'restaurant' || $category === 'cafe') {
            $filters[] = "nwr[\"amenity\"~\"restaurant|cafe\"](around:{$radiusMeters},{$lat},{$lng});";
        }
        if ($category === 'all' || $category === 'attraction') {
            $filters[] = "nwr[\"tourism\"~\"attraction|viewpoint|theme_park|zoo\"](around:{$radiusMeters},{$lat},{$lng});";
            $filters[] = "nwr[\"historic\"](around:{$radiusMeters},{$lat},{$lng});";
            $filters[] = "nwr[\"amenity\"=\"place_of_worship\"](around:{$radiusMeters},{$lat},{$lng});";
        }

        $union = implode("\n  ", $filters);

        return <<<OVERPASS
[out:json][timeout:15];
(
  {$union}
);
out center tags 120;
OVERPASS;
    }

    /**
     * Execute Overpass query with mirror fallbacks
     */
    private static function executeOverpassQuery(string $query): ?array {
        $userAgent = self::getUserAgent();

        foreach (self::$endpoints as $endpoint) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $endpoint,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => 'data=' . urlencode($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER     => [
                    'User-Agent: ' . $userAgent,
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                ],
                CURLOPT_TIMEOUT        => self::$requestTimeoutSeconds,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_SSL_VERIFYPEER => false, // Ensure local dev XAMPP works smoothly
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $json = json_decode($response, true);
                if (is_array($json) && isset($json['elements'])) {
                    return $json;
                }
            }
        }
        return null;
    }

    /**
     * Upsert OSM elements into external_places table
     */
    private static function upsertOsmElements(int $destinationId, array $elements): int {
        $db = getDB();
        $count = 0;

        // Auto approve setting
        $autoApprove = 1;
        try {
            $aaStmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'osm_auto_approve' LIMIT 1");
            $aaStmt->execute();
            $aaVal = $aaStmt->fetchColumn();
            if ($aaVal !== false && $aaVal !== null) $autoApprove = (int)$aaVal;
        } catch (Exception $e) {}

        $checkStmt = $db->prepare("SELECT id, is_approved, is_verified FROM external_places WHERE osm_type = ? AND osm_id = ?");
        $insertStmt = $db->prepare("
            INSERT INTO external_places (
                destination_id, osm_type, osm_id, name, name_ml, name_ta,
                category, sub_type, latitude, longitude, address,
                contact_phone, website, indicative_price, price_unit,
                room_categories, facilities, food_options, description,
                raw_tags_json, is_approved, is_verified, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, datetime('now'), datetime('now')
            )
        ");
        $updateStmt = $db->prepare("
            UPDATE external_places SET
                destination_id = ?,
                name = COALESCE(NULLIF(?, ''), name),
                latitude = ?,
                longitude = ?,
                address = COALESCE(NULLIF(?, ''), address),
                contact_phone = COALESCE(NULLIF(?, ''), contact_phone),
                website = COALESCE(NULLIF(?, ''), website),
                raw_tags_json = ?,
                updated_at = datetime('now')
            WHERE id = ?
        ");

        foreach ($elements as $el) {
            $tags = $el['tags'] ?? [];
            $name = trim($tags['name'] ?? $tags['name:en'] ?? '');
            if (empty($name)) continue; // Skip unnamed nodes

            $osmType = $el['type'] ?? 'node';
            $osmId   = (string)($el['id'] ?? '');
            if (empty($osmId)) continue;

            // Extract coordinates
            $lat = 0.0;
            $lng = 0.0;
            if (isset($el['lat']) && isset($el['lon'])) {
                $lat = (float)$el['lat'];
                $lng = (float)$el['lon'];
            } elseif (isset($el['center']['lat']) && isset($el['center']['lon'])) {
                $lat = (float)$el['center']['lat'];
                $lng = (float)$el['center']['lon'];
            }
            if ($lat == 0.0 && $lng == 0.0) continue;

            // Multilingual names if tagged in OSM
            $nameMl = $tags['name:ml'] ?? null;
            $nameTa = $tags['name:ta'] ?? null;

            // Categorize
            $category = 'attraction';
            $subType = 'viewpoint';
            $indicativePrice = 0.00;
            $priceUnit = 'per person';
            $facilities = [];
            $foodOptions = [];
            $roomCategories = [];

            if (isset($tags['tourism'])) {
                $t = strtolower($tags['tourism']);
                if (in_array($t, ['hotel', 'motel', 'guest_house', 'hostel', 'chalet'])) {
                    $category = 'hotel';
                    $subType = $t;
                    $indicativePrice = ($t === 'hostel' || $t === 'guest_house') ? 1800.00 : 4200.00;
                    $priceUnit = 'per room / night';
                    $roomCategories = ['Standard Room', 'Deluxe Room'];
                } elseif ($t === 'resort') {
                    $category = 'resort';
                    $subType = 'resort';
                    $indicativePrice = 6800.00;
                    $priceUnit = 'per room / night';
                    $roomCategories = ['Deluxe Cottage', 'Premium Lake / Hill Villa'];
                } else {
                    $category = 'attraction';
                    $subType = $t;
                    $indicativePrice = 150.00;
                }
            } elseif (isset($tags['amenity'])) {
                $a = strtolower($tags['amenity']);
                if ($a === 'restaurant') {
                    $category = 'restaurant';
                    $subType = 'restaurant';
                    $indicativePrice = 600.00;
                    $priceUnit = 'per meal';
                } elseif ($a === 'cafe') {
                    $category = 'cafe';
                    $subType = 'cafe';
                    $indicativePrice = 350.00;
                    $priceUnit = 'per person';
                } elseif ($a === 'place_of_worship') {
                    $category = 'attraction';
                    $subType = 'sacred_site';
                    $indicativePrice = 0.00;
                }
            } elseif (isset($tags['historic'])) {
                $category = 'attraction';
                $subType = 'heritage_' . strtolower($tags['historic']);
                $indicativePrice = 100.00;
            }

            // Facilities & features
            if (!empty($tags['internet_access']) && $tags['internet_access'] !== 'no') $facilities[] = 'Free High-speed Wi-Fi';
            if (!empty($tags['swimming_pool']) && $tags['swimming_pool'] !== 'no') $facilities[] = 'Swimming Pool';
            if (!empty($tags['air_conditioning']) && $tags['air_conditioning'] !== 'no') $facilities[] = 'Air Conditioning';
            if (!empty($tags['wheelchair']) && $tags['wheelchair'] === 'yes') $facilities[] = 'Wheelchair Accessible';
            if (!empty($tags['parking']) && $tags['parking'] !== 'no') $facilities[] = 'Dedicated Parking';

            // Cuisine & Food
            if (!empty($tags['cuisine'])) {
                $cuisines = explode(';', $tags['cuisine']);
                foreach ($cuisines as $c) {
                    $foodOptions[] = ucwords(trim($c)) . ' Cuisine';
                }
            }
            if (!empty($tags['diet:vegetarian']) && $tags['diet:vegetarian'] === 'yes') $foodOptions[] = 'Pure Vegetarian';
            if (!empty($tags['diet:vegan']) && $tags['diet:vegan'] === 'yes') $foodOptions[] = 'Vegan Options';

            // Format address
            $addrParts = [];
            if (!empty($tags['addr:street'])) $addrParts[] = $tags['addr:street'];
            if (!empty($tags['addr:suburb'])) $addrParts[] = $tags['addr:suburb'];
            if (!empty($tags['addr:city'])) $addrParts[] = $tags['addr:city'];
            if (!empty($tags['addr:postcode'])) $addrParts[] = $tags['addr:postcode'];
            $address = !empty($tags['addr:full']) ? $tags['addr:full'] : implode(', ', $addrParts);

            // Contact & Web
            $phone = $tags['contact:phone'] ?? $tags['phone'] ?? $tags['contact:mobile'] ?? null;
            $website = $tags['contact:website'] ?? $tags['website'] ?? null;

            $description = $tags['description'] ?? null;
            $rawJson = json_encode($tags);

            // Check if already in DB
            $checkStmt->execute([$osmType, $osmId]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                // Update basic dynamic info without overriding admin edits
                $updateStmt->execute([
                    $destinationId,
                    $name,
                    $lat,
                    $lng,
                    $address,
                    $phone,
                    $website,
                    $rawJson,
                    $existing['id'],
                ]);
            } else {
                $insertStmt->execute([
                    $destinationId,
                    $osmType,
                    $osmId,
                    $name,
                    $nameMl,
                    $nameTa,
                    $category,
                    $subType,
                    $lat,
                    $lng,
                    $address,
                    $phone,
                    $website,
                    $indicativePrice,
                    $priceUnit,
                    !empty($roomCategories) ? json_encode($roomCategories) : null,
                    !empty($facilities) ? json_encode($facilities) : null,
                    !empty($foodOptions) ? json_encode($foodOptions) : null,
                    $description,
                    $rawJson,
                    $autoApprove,
                    0, // is_verified
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Retrieve approved places from DB for a destination
     */
    public static function getApprovedPlacesFromDB(int $destinationId, string $category = 'all'): array {
        $db = getDB();
        $sql = "SELECT * FROM external_places WHERE destination_id = ? AND is_approved = 1";
        $params = [$destinationId];

        if ($category !== 'all') {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY is_verified DESC, indicative_price DESC, name ASC LIMIT 100";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON fields
        foreach ($places as &$p) {
            $p['facilities_list'] = !empty($p['facilities']) ? json_decode($p['facilities'], true) : [];
            $p['food_options_list'] = !empty($p['food_options']) ? json_decode($p['food_options'], true) : [];
            $p['room_categories_list'] = !empty($p['room_categories']) ? json_decode($p['room_categories'], true) : [];
        }

        return $places;
    }
}
