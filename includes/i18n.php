<?php
/**
 * MIDHILA VOYAGES — Multilingual Support System (i18n)
 * Full Translation Engine supporting English, Malayalam (മലയാളം), and Tamil (தமிழ்)
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Supported Languages Definition
 */
function get_supported_languages(): array {
    return [
        'en' => [
            'code'   => 'en',
            'name'   => 'English',
            'native' => 'English',
            'dir'    => 'ltr'
        ],
        'ml' => [
            'code'   => 'ml',
            'name'   => 'Malayalam',
            'native' => 'മലയാളം',
            'dir'    => 'ltr'
        ],
        'ta' => [
            'code'   => 'ta',
            'name'   => 'Tamil',
            'native' => 'தமிழ்',
            'dir'    => 'ltr'
        ]
    ];
}

/**
 * Initialize and detect active language
 */
function init_language(): string {
    $supported = ['en', 'ml', 'ta'];
    $lang = 'en';

    // 1. URL parameter (?lang=ml)
    if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), $supported, true)) {
        $lang = strtolower($_GET['lang']);
        $_SESSION['mv_lang'] = $lang;
        if (!headers_sent()) {
            setcookie('mv_lang', $lang, time() + (86400 * 30), '/');
        }
    }
    // 2. Session
    elseif (!empty($_SESSION['mv_lang']) && in_array($_SESSION['mv_lang'], $supported, true)) {
        $lang = $_SESSION['mv_lang'];
    }
    // 3. Cookie
    elseif (!empty($_COOKIE['mv_lang']) && in_array($_COOKIE['mv_lang'], $supported, true)) {
        $lang = $_COOKIE['mv_lang'];
        $_SESSION['mv_lang'] = $lang;
    }

    return $lang;
}

$GLOBALS['current_lang'] = init_language();

/**
 * Get current active language code ('en', 'ml', 'ta')
 */
function current_lang(): string {
    if (!empty($_SESSION['mv_lang']) && in_array($_SESSION['mv_lang'], ['en', 'ml', 'ta'], true)) {
        return $_SESSION['mv_lang'];
    }
    return $GLOBALS['current_lang'] ?? 'en';
}

/**
 * Set active language dynamically
 */
function set_language(string $lang): void {
    if (in_array($lang, ['en', 'ml', 'ta'], true)) {
        $_SESSION['mv_lang'] = $lang;
        $GLOBALS['current_lang'] = $lang;
    }
}

/**
 * Generate URL with active or new language parameter
 */
function lang_url(string $langCode): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $query = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }
    $query['lang'] = $langCode;
    $path = $parts['path'] ?? '/';
    return $path . '?' . http_build_query($query);
}

/**
 * Multilingual Dictionary Repository
 */
function get_translation_dictionary(): array {
    static $dict = null;
    if ($dict !== null) {
        return $dict;
    }

    $dict = [
        // Navigation & General
        'nav_home' => [
            'en' => 'Home',
            'ml' => 'പ്രധാന പേജ്',
            'ta' => 'முகப்பு'
        ],
        'nav_about' => [
            'en' => 'About Us',
            'ml' => 'ഞങ്ങളെക്കുറിച്ച്',
            'ta' => 'எங்களைப் பற்றி'
        ],
        'nav_kerala_tourism' => [
            'en' => 'Kerala Tourism',
            'ml' => 'കേരള ടൂറിസം',
            'ta' => 'கேரளா சுற்றுலா'
        ],
        'nav_pilgrimage' => [
            'en' => 'Pilgrimage Tourism',
            'ml' => 'തീർത്ഥാടന ടൂറിസം',
            'ta' => 'ஆன்மீக சுற்றுலா'
        ],
        'nav_hill_stations' => [
            'en' => 'Hill Stations',
            'ml' => 'ഹിൽ സ്റ്റേഷനുകൾ',
            'ta' => 'மலை வாசஸ்தலங்கள்'
        ],
        'nav_backwaters' => [
            'en' => 'Backwater Tourism',
            'ml' => 'കായൽ ടൂറിസം',
            'ta' => 'காயல் சுற்றுலா'
        ],
        'nav_beaches' => [
            'en' => 'Beach Tourism',
            'ml' => 'ബീച്ച് ടൂറിസം',
            'ta' => 'கடற்கரை சுற்றுலா'
        ],
        'nav_ayurveda' => [
            'en' => 'Ayurveda & Wellness',
            'ml' => 'ആയുർവേദ & വെൽനസ്',
            'ta' => 'ஆயுர்வேதம் & நல்வாழ்வு'
        ],
        'nav_destinations' => [
            'en' => 'Destinations',
            'ml' => 'സഞ്ചാര കേന്ദ്രങ്ങൾ',
            'ta' => 'சுற்றுலா தலங்கள்'
        ],
        'nav_packages' => [
            'en' => 'Tour Packages',
            'ml' => 'ടൂർ പാക്കേജുകൾ',
            'ta' => 'சுற்றுலா தொகுப்புகள்'
        ],
        'nav_accommodation' => [
            'en' => 'Accommodation',
            'ml' => 'താമസ സൗകര്യങ്ങൾ',
            'ta' => 'தங்கும் விடுதிகள்'
        ],
        'nav_custom_tour' => [
            'en' => 'Customised Tour',
            'ml' => 'കസ്റ്റമൈസ്ഡ് ടൂർ',
            'ta' => 'விருப்பத்திற்கேற்ப சுற்றுலா'
        ],
        'nav_corporate_events' => [
            'en' => 'Corporate Events',
            'ml' => 'കോർപ്പറേറ്റ് ഇവന്റുകൾ',
            'ta' => 'கார்ப்பரேட் நிகழ்வுகள்'
        ],
        'nav_conference_halls' => [
            'en' => 'Conference Halls',
            'ml' => 'കോൺഫറൻസ് ഹാളുകൾ',
            'ta' => 'மாநாட்டு அரங்குகள்'
        ],
        'nav_seminar_halls' => [
            'en' => 'Seminar Halls',
            'ml' => 'സെമിനാർ ഹാളുകൾ',
            'ta' => 'கருத்தரங்கு அரங்குகள்'
        ],
        'nav_convention_centres' => [
            'en' => 'Convention Centres',
            'ml' => 'കൺവെൻഷൻ സെന്ററുകൾ',
            'ta' => 'மாபெரும் மாநாட்டு மையங்கள்'
        ],
        'nav_services' => [
            'en' => 'Services',
            'ml' => 'സേവനങ്ങൾ',
            'ta' => 'சேவைகள்'
        ],
        'nav_gallery' => [
            'en' => 'Gallery',
            'ml' => 'ചിത്രശാല',
            'ta' => 'புகைப்படங்கள்'
        ],
        'nav_contact' => [
            'en' => 'Contact Us',
            'ml' => 'ബന്ധപ്പെടുക',
            'ta' => 'தொடர்புக்கு'
        ],
        'nav_faqs' => [
            'en' => 'FAQs',
            'ml' => 'സംശയങ്ങൾ',
            'ta' => 'கேள்வி பதில்'
        ],
        'btn_plan_trip' => [
            'en' => 'Plan My Trip',
            'ml' => 'യാത്ര പ്ലാൻ ചെയ്യാം',
            'ta' => 'பயணம் திட்டமிடலாம்'
        ],
        'btn_book_now' => [
            'en' => 'Book Now',
            'ml' => 'ബുക്ക് ചെയ്യുക',
            'ta' => 'முன்பதிவு செய்க'
        ],
        'btn_submit_enquiry' => [
            'en' => 'Submit Booking Enquiry',
            'ml' => 'ബുക്കിംഗ് അന്വേഷണം സമർപ്പിക്കുക',
            'ta' => 'முன்பதிவு விசாரணையை சமர்ப்பிக்கவும்'
        ],
        'btn_view_details' => [
            'en' => 'View Details',
            'ml' => 'വിശദാംശങ്ങൾ കാണുക',
            'ta' => 'விவரங்களை காண்க'
        ],
        'btn_whatsapp_us' => [
            'en' => 'WhatsApp Specialist',
            'ml' => 'വാട്സ്ആപ്പിൽ സംസാരിക്കാം',
            'ta' => 'வாட்ஸ்அப் மூலம் தொடர்புகொள்ள'
        ],
        'btn_filter' => [
            'en' => 'Filter Options',
            'ml' => 'ഫിൽട്ടർ ചെയ്യുക',
            'ta' => 'வடிகட்டுக'
        ],
        'btn_explore_kerala' => [
            'en' => 'Explore Kerala Horizons',
            'ml' => 'കേരള കാഴ്ചകൾ കാണാം',
            'ta' => 'கேரளாவை கண்டறியுங்கள்'
        ],
        'btn_customise_this' => [
            'en' => 'Request Customised Tour',
            'ml' => 'കസ്റ്റമൈസ്ഡ് ടൂർ ആവശ്യപ്പെടുക',
            'ta' => 'விருப்பத்திற்கேற்ப மாற்றவும்'
        ],

        // Kerala Tourism Module
        'kerala_headline' => [
            'en' => 'God’s Own Country • Handcrafted Kerala Tourism',
            'ml' => 'ദൈവത്തിന്റെ സ്വന്തം നാട് • ഹൃദ്യമായ കേരള ടൂറിസം',
            'ta' => 'கடவுளின் சொந்த நாடு • பாரம்பரிய கேரளா சுற்றுலா'
        ],
        'kerala_subtext' => [
            'en' => 'Discover sacred temples, emerald tea highlands, peaceful backwaters, pristine coasts, and ancient Ayurvedic sanctuaries with Midhila Voyages.',
            'ml' => 'പുണ്യക്ഷേത്രങ്ങൾ, തേയിലത്തോട്ടങ്ങൾ, കായലുകൾ, സുവർണ്ണ തീരങ്ങൾ, ആയുർവേദ കേന്ദ്രങ്ങൾ എന്നിവ മിഥില വോയേജസിനൊപ്പം ആസ്വദിക്കൂ.',
            'ta' => 'புனித கோவில்கள், பசுமை மலைகள், அமைதியான காயல்கள் மற்றும் பாரம்பரிய ஆயுർவேத ஓய்வு இல்லங்களை மிதிலா வாயேஜஸ் உடன் கண்டறியுங்கள்.'
        ],
        'kerala_5_categories' => [
            'en' => 'Five Premier Kerala Tourism Experiences',
            'ml' => 'കേരളത്തിലെ അഞ്ച് പ്രധാന ടൂറിസം അനുഭവങ്ങൾ',
            'ta' => 'கேரளாவின் ஐந்து முதன்மை சுற்றுலா அனுபவங்கள்'
        ],

        // Travel Form Labels
        'form_full_name' => [
            'en' => 'Customer Full Name *',
            'ml' => 'പേര് *',
            'ta' => 'முழு பெயர் *'
        ],
        'form_phone' => [
            'en' => 'Phone / WhatsApp Number *',
            'ml' => 'ഫോൺ / വാട്സ്ആപ്പ് നമ്പർ *',
            'ta' => 'தொலைபேசி / வாட்ஸ்அப் எண் *'
        ],
        'form_email' => [
            'en' => 'Email Address *',
            'ml' => 'ഇമെയിൽ വിലാസം *',
            'ta' => 'மின்னஞ்சல் முகவரி *'
        ],
        'form_select_destination' => [
            'en' => 'Select Destination *',
            'ml' => 'സഞ്ചാര കേന്ദ്രം തിരഞ്ഞെടുക്കുക *',
            'ta' => 'இடத்தை தேர்வு செய்க *'
        ],
        'form_select_accommodation' => [
            'en' => 'Select Preferred Accommodation',
            'ml' => 'താമസ സൗകര്യം തിരഞ്ഞെടുക്കുക',
            'ta' => 'தங்கும் இடத்தை தேர்வு செய்க'
        ],
        'form_start_date' => [
            'en' => 'Travel Starting Date *',
            'ml' => 'യാത്ര ആരംഭിക്കുന്ന തീയതി *',
            'ta' => 'பயணம் தொடங்கும் தேதி *'
        ],
        'form_end_date' => [
            'en' => 'Travel Ending Date',
            'ml' => 'യാത്ര അവസാനിക്കുന്ന തീയതി',
            'ta' => 'பயணம் முடியும் தேதி'
        ],
        'form_adults' => [
            'en' => 'Number of Adults',
            'ml' => 'മുതിർന്നവർ (Adults)',
            'ta' => 'பெரியவர்கள்'
        ],
        'form_children' => [
            'en' => 'Number of Children',
            'ml' => 'കുട്ടികൾ (Children)',
            'ta' => 'குழந்தைகள்'
        ],
        'form_rooms' => [
            'en' => 'Number of Rooms Required',
            'ml' => 'ആവശ്യമായ മുറികളുടെ എണ്ണം',
            'ta' => 'தேவையான அறைகள்'
        ],
        'form_days' => [
            'en' => 'Number of Days',
            'ml' => 'ദിവസങ്ങളുടെ എണ്ണം',
            'ta' => 'நாட்களின் எண்ணிக்கை'
        ],
        'form_total_travellers' => [
            'en' => 'Total Travellers',
            'ml' => 'ആകെ യാത്രക്കാർ',
            'ta' => 'மொத்த பயணிகள்'
        ],
        'form_travel_type' => [
            'en' => 'Preferred Travel Type',
            'ml' => 'യാത്രാ രീതി',
            'ta' => 'பயண வகை'
        ],
        'form_starting_location' => [
            'en' => 'Starting City / Pickup Point',
            'ml' => 'ആരംഭിക്കുന്ന സ്ഥലം / നഗരം',
            'ta' => 'புறப்படும் இடம் / நகரம்'
        ],
        'form_budget_range' => [
            'en' => 'Estimated Budget Range',
            'ml' => 'പ്രതീക്ഷിക്കുന്ന ബജറ്റ്',
            'ta' => 'எதிர்பார்க்கப்படும் பட்ஜெட்'
        ],
        'form_additional_req' => [
            'en' => 'Additional Travel Requirements / Special Instructions',
            'ml' => 'മറ്റ് ആവശ്യങ്ങൾ / നിർദ്ദേശങ്ങൾ',
            'ta' => 'கூடுதல் தேவைகள் / சிறப்பு குறிப்புகள்'
        ],

        // Accommodation Preferences
        'pref_room_types' => [
            'en' => 'Preferred Room Type / View',
            'ml' => 'മുറിയുടെ കാഴ്ച്ച / തരം',
            'ta' => 'விரும்பும் அறை வகை / காட்சி'
        ],
        'room_mountain_view' => [
            'en' => 'Mountain-view room',
            'ml' => 'മൗണ്ടൻ വ്യൂ മുറി (Mountain-view)',
            'ta' => 'மலை காட்சி அறை'
        ],
        'room_tea_view' => [
            'en' => 'Tea plantation-view room',
            'ml' => 'തേയിലത്തോട്ട കാഴ്ച്ചയുള്ള മുറി',
            'ta' => 'தேயிலைத் தோட்ட காட்சி அறை'
        ],
        'room_valley_view' => [
            'en' => 'Valley-view room',
            'ml' => 'താഴ്‌വര കാഴ്ച്ചയുള്ള മുറി',
            'ta' => 'பள்ளத்தாக்கு காட்சி அறை'
        ],
        'room_lake_view' => [
            'en' => 'Lake-view room',
            'ml' => 'തടാക കാഴ്ച്ചയുള്ള മുറി',
            'ta' => 'ஏரி காட்சி அறை'
        ],
        'room_backwater_view' => [
            'en' => 'Backwater-view room',
            'ml' => 'കായൽ കാഴ്ച്ചയുള്ള മുറി / ഹൗസ്ബോട്ട്',
            'ta' => 'காயல் காட்சி அறை'
        ],
        'room_beach_view' => [
            'en' => 'Beach-view room',
            'ml' => 'കടൽത്തീര കാഴ്ച്ചയുള്ള മുറി',
            'ta' => 'கடற்கரை காட்சி அறை'
        ],
        'room_standard' => [
            'en' => 'Standard room',
            'ml' => 'സ്റ്റാൻഡേർഡ് മുറി',
            'ta' => 'நிலையான அறை (Standard)'
        ],
        'room_deluxe' => [
            'en' => 'Deluxe room',
            'ml' => 'ഡീലക്സ് മുറി',
            'ta' => 'டீலக்ஸ் அறை (Deluxe)'
        ],
        'room_premium' => [
            'en' => 'Premium room',
            'ml' => 'പ്രീമിയം മുറി',
            'ta' => 'பிரீமியம் அறை'
        ],
        'room_luxury' => [
            'en' => 'Luxury suite / villa',
            'ml' => 'ലക്ഷ്വറി സ്യൂട്ട് / വില്ല',
            'ta' => 'சொகுசு அறை / வில்லா'
        ],

        // Food Preferences
        'pref_food_plan' => [
            'en' => 'Food & Meal Preferences',
            'ml' => 'ഭക്ഷണ ക്രമം',
            'ta' => 'உணவு விருப்பங்கள்'
        ],
        'food_with' => [
            'en' => 'With food',
            'ml' => 'ഭക്ഷണത്തോടെ',
            'ta' => 'உணவுடன்'
        ],
        'food_without' => [
            'en' => 'Without food',
            'ml' => 'ഭക്ഷണം ആവശ്യമില്ല',
            'ta' => 'உணவு இல்லாமல்'
        ],
        'food_breakfast_only' => [
            'en' => 'Breakfast only',
            'ml' => 'പ്രഭാതഭക്ഷണം മാത്രം (Breakfast only)',
            'ta' => 'காலை உணவு மட்டும்'
        ],
        'food_breakfast_dinner' => [
            'en' => 'Breakfast and dinner',
            'ml' => 'പ്രഭാതഭക്ഷണവും അത്താഴവും (Breakfast & Dinner)',
            'ta' => 'காலை மற்றும் இரவு உணவு'
        ],
        'food_all_meals' => [
            'en' => 'All meals included (Breakfast, Lunch & Dinner)',
            'ml' => 'എല്ലാ നേരത്തെയും ഭക്ഷണം ഉൾപ്പെടെ',
            'ta' => 'அனைத்து நேர உணவுகளும்'
        ],

        // Food Types
        'pref_food_type' => [
            'en' => 'Food Type',
            'ml' => 'ഭക്ഷണ തരം',
            'ta' => 'உணவு வகை'
        ],
        'food_type_veg' => [
            'en' => 'Vegetarian',
            'ml' => 'സസ്യാഹാരം (Vegetarian)',
            'ta' => 'சைவம் (Vegetarian)'
        ],
        'food_type_non_veg' => [
            'en' => 'Non-vegetarian',
            'ml' => 'മാംസാഹാരം (Non-vegetarian)',
            'ta' => 'அசைவம் (Non-vegetarian)'
        ],
        'food_type_both' => [
            'en' => 'Both vegetarian and non-vegetarian',
            'ml' => 'സസ്യ-മാംസാഹാരങ്ങൾ രണ്ടും',
            'ta' => 'சைவம் மற்றும் அசைவம் இரண்டும்'
        ],
        'food_type_special' => [
            'en' => 'Special dietary requirements (Jain, Diabetic, Vegan)',
            'ml' => 'പ്രത്യേക പഥ്യാഹാരം (ജയിൻ, ഡയബറ്റിക്, വേഗൻ)',
            'ta' => 'சிறப்பு உணவு முறைகள் (ஜைன், நீரிழிவு, வேகன்)'
        ],
        'food_instructions' => [
            'en' => 'Special Food Instructions',
            'ml' => 'പ്രത്യേക ഭക്ഷണ നിർദ്ദേശങ്ങൾ',
            'ta' => 'சிறப்பு உணவு வழிகாட்டல்கள்'
        ],

        // Sightseeing Preferences
        'pref_sightseeing' => [
            'en' => 'Sightseeing Preferences',
            'ml' => 'സ്ഥലദർശന ക്രമീകരണങ്ങൾ',
            'ta' => 'சுற்றுலா பார்வை விருப்பங்கள்'
        ],
        'sightseeing_with' => [
            'en' => 'With sightseeing',
            'ml' => 'സൈറ്റ്‌സീയിങ് ഉൾപ്പെടെ',
            'ta' => 'சுற்றுலா பார்வையுடன்'
        ],
        'sightseeing_without' => [
            'en' => 'Without sightseeing (Relaxed Stay)',
            'ml' => 'സൈറ്റ്‌സീയിങ് ഇല്ലാതെ (വിശ്രമം മാത്രം)',
            'ta' => 'பார்வை இல்லாமல் (ஓய்வு மட்டும்)'
        ],
        'sightseeing_customised' => [
            'en' => 'Customised sightseeing',
            'ml' => 'ഇഷ്ടാനുസൃതമായ സൈറ്റ്‌സീയിങ്',
            'ta' => 'விருப்பத்திற்கேற்ப இடங்கள்'
        ],
        'sightseeing_private' => [
            'en' => 'Private sightseeing vehicle (AC Sedan / Innova / Tempo)',
            'ml' => 'പ്രൈവറ്റ് എസി വാഹനം',
            'ta' => 'தனிப்பட்ட ஏசி வாகனம்'
        ],
        'sightseeing_group' => [
            'en' => 'Group sightseeing coach',
            'ml' => 'ഗ്രൂപ്പ് യാത്രാ വാഹനം',
            'ta' => 'குழு சுற்றுலா வாகனம்'
        ],
        'sightseeing_places_wish' => [
            'en' => 'Specific places you wish to visit',
            'ml' => 'സന്ദർശിക്കാൻ ആഗ്രഹിക്കുന്ന പ്രത്യേക സ്ഥലങ്ങൾ',
            'ta' => 'நீங்கள் காண விரும்பும் குறிப்பிட்ட இடங்கள்'
        ],

        // Facilities Required
        'pref_facilities' => [
            'en' => 'Required Accommodation Facilities',
            'ml' => 'ആവശ്യമായ സൗകര്യങ്ങൾ',
            'ta' => 'தேவையான விடுதி வசதிகள்'
        ],
        'fac_wifi' => [
            'en' => 'Wi-Fi Internet',
            'ml' => 'വൈ-ഫൈ (Wi-Fi)',
            'ta' => 'வைஃபை இணையம்'
        ],
        'fac_ac' => [
            'en' => 'Air Conditioning',
            'ml' => 'എയർ കണ്ടീഷനിംഗ് (AC)',
            'ta' => 'ஏசி (Air Conditioning)'
        ],
        'fac_parking' => [
            'en' => 'Vehicle Parking',
            'ml' => 'വാഹന പാർക്കിംഗ്',
            'ta' => 'வாகன நிறுத்துமிடம்'
        ],
        'fac_pool' => [
            'en' => 'Swimming Pool',
            'ml' => 'നീന്തൽക്കുളം (Swimming Pool)',
            'ta' => 'நீச்சல் குளம்'
        ],
        'fac_balcony' => [
            'en' => 'Private Balcony',
            'ml' => 'ബാൽക്കണി (Balcony)',
            'ta' => 'பால்கனி'
        ],
        'fac_family_rooms' => [
            'en' => 'Family Rooms',
            'ml' => 'ഫാമിലി റൂംസ്',
            'ta' => 'குடும்ப அறைகள்'
        ],
        'fac_room_service' => [
            'en' => 'Room Service',
            'ml' => 'റൂം സർവീസ്',
            'ta' => 'அறை சேவை (Room Service)'
        ],
        'fac_restaurant' => [
            'en' => 'In-house Restaurant',
            'ml' => 'റെസ്റ്റോറന്റ്',
            'ta' => 'உணவகம்'
        ],
        'fac_accessibility' => [
            'en' => 'Senior / Accessibility Friendly',
            'ml' => 'മുതിർന്നവർക്കുള്ള സൗകര്യങ്ങൾ',
            'ta' => 'முதியவர்களுக்கான சிறப்பு வசதிகள்'
        ],

        // Corporate Events Module
        'corp_title' => [
            'en' => 'Business & Corporate Event Booking Management',
            'ml' => 'ബിസിനസ് & കോർപ്പറേറ്റ് ഇവന്റ് ബുക്കിംഗ്',
            'ta' => 'வணிக & கார்ப்பரேட் நிகழ்வுகள் முன்பதிவு'
        ],
        'corp_subtitle' => [
            'en' => 'Premier conference halls, seminar venues, and world-class convention centres managed with precision across Kerala.',
            'ml' => 'കോൺഫറൻസ് ഹാളുകൾ, സെമിനാർ വേദികൾ, കൺവെൻഷൻ സെന്ററുകൾ എന്നിവയുടെ സമഗ്രമായ ഏകോപനം.',
            'ta' => 'சிறந்த மாநாட்டு அரங்குகள், கருத்தரங்கு கூடங்கள் மற்றும் உலகத்தரம் வாய்ந்த மாநாட்டு மையங்கள்.'
        ],
        'corp_org_name' => [
            'en' => 'Organisation / Company Name *',
            'ml' => 'സ്ഥാപനത്തിന്റെ / കമ്പനിയുടെ പേര് *',
            'ta' => 'நிறுவனத்தின் பெயர் *'
        ],
        'corp_contact_person' => [
            'en' => 'Contact Person Name *',
            'ml' => 'ബന്ധപ്പെടേണ്ട ആളിന്റെ പേര് *',
            'ta' => 'தொடர்பு கொள்ள வேண்டியவர் பெயர் *'
        ],
        'corp_event_type' => [
            'en' => 'Event Type *',
            'ml' => 'ഇവന്റ് തരം *',
            'ta' => 'நிகழ்வின் வகை *'
        ],
        'corp_participants' => [
            'en' => 'Expected Number of Participants *',
            'ml' => 'പ്രതീക്ഷിക്കുന്ന പ്രതിനിധികളുടെ എണ്ണം *',
            'ta' => 'எதிர்பார்க்கப்படும் பங்கேற்பாளர்கள் எண்ணிக்கை *'
        ],
        'corp_seating' => [
            'en' => 'Preferred Seating Arrangement',
            'ml' => 'ഇരിപ്പിട ക്രമീകരണം (Seating)',
            'ta' => 'இருக்கை அமைப்பு'
        ],
        'corp_av_needs' => [
            'en' => 'Audio-Visual Equipment Requirements',
            'ml' => 'ഓഡിയോ-വിഷ്വൽ / പ്രൊജക്ടർ ആവശ്യങ്ങൾ',
            'ta' => 'ஒலி-ஒளி அமைப்பு தேவைகள்'
        ],
        'corp_catering_needs' => [
            'en' => 'Food & Catering Requirements',
            'ml' => 'ഭക്ഷണവും കാറ്ററിംഗും',
            'ta' => 'உணவு மற்றும் கேட்டரிங் தேவைகள்'
        ],
        'corp_need_acc' => [
            'en' => 'Require Delegate Accommodation',
            'ml' => 'പ്രതിനിധികൾക്ക് താമസ സൗകര്യം ആവശ്യമുണ്ട്',
            'ta' => 'பங்கேற்பாளர்களுக்கு தங்கும் வசதி தேவை'
        ],
        'corp_need_transport' => [
            'en' => 'Require Ground Transportation / Transfers',
            'ml' => 'വാഹന യാത്രാ സൗകര്യം ആവശ്യമുണ്ട്',
            'ta' => 'வாகன போக்குவரத்து வசதி தேவை'
        ],

        // Official Business Acknowledgement Disclaimer
        'disclaimer_notice' => [
            'en' => 'Important: Room availability and venue slots are independently verified by our team with respective properties before final booking. Submitting an enquiry does not constitute an instant confirmed booking.',
            'ml' => 'ശ്രദ്ധിക്കുക: മുറികളുടെയും വേദികളുടെയും ലഭ്യത ഞങ്ങളുടെ ടീം ബന്ധപ്പെട്ടവരുമായി സ്ഥിരീകരിച്ച ശേഷമേ അന്തിമ ബുക്കിംഗ് നൽകൂ. അന്വേഷണം സമർപ്പിക്കുന്നത് തത്സമയ കൺഫർമേഷൻ അല്ല.',
            'ta' => 'முக்கிய குறிப்பு: தங்கும் அறைகள் மற்றும் அரங்குகள் கிடைப்பதை எங்கள் குழு உறுதிசெய்த பின்னரே முன்பதிவு முடிவாகும். விசாரணை சமர்ப்பிப்பது உடனடி உறுதிப்படுத்தல் அல்ல.'
        ],
        'ack_modal_title' => [
            'en' => 'Booking Enquiry Received Successfully',
            'ml' => 'അന്വേഷണം വിജയകരമായി സമർപ്പിച്ചു',
            'ta' => 'முன்பதிவு விசாரணை வெற்றிகரமாக பெறப்பட்டது'
        ],
        'ack_modal_text' => [
            'en' => 'Your booking enquiry has been received successfully. Our team will verify the availability of your requested services and contact you shortly. This acknowledgement does not represent a confirmed booking.',
            'ml' => 'നിങ്ങളുടെ ബുക്കിംഗ് അന്വേഷണം വിജയകരമായി ലഭിച്ചിട്ടുണ്ട്. ആവശ്യപ്പെട്ട സേവനങ്ങളുടെ ലഭ്യത പരിശോധിച്ച ശേഷം ഞങ്ങളുടെ ടീം ഉടൻ നിങ്ങളുമായി ബന്ധപ്പെടും. ഈ അറിയിപ്പ് സ്ഥിരീകരിച്ച ബുക്കിംഗിനെ പ്രതിനിധീകരിക്കുന്നില്ല.',
            'ta' => 'உங்கள் முன்பதிவு விசாரணை வெற்றிகரமாக பெறப்பட்டது. நீங்கள் கோரிய சேவைகளின் இருப்பை சரிபார்த்துவிட்டு எங்கள் குழு விரைவில் உங்களைத் தொடர்பு கொள்ளும். இந்த அறிவிப்பு உறுதிப்படுத்தப்பட்ட முன்பதிவாகாது.'
        ],
        'ack_ref_label' => [
            'en' => 'Your Unique Booking Reference Number:',
            'ml' => 'നിങ്ങളുടെ ബുക്കിംഗ് റഫറൻസ് നമ്പർ:',
            'ta' => 'உங்கள் தனித்துவமான முன்பதிவு எண்:'
        ]
    ];

    return $dict;
}

/**
 * Translate a phrase key into current language with fallback to English
 */
function __t(string $key, ?string $default = null): string {
    $dict = get_translation_dictionary();
    $lang = current_lang();

    if (isset($dict[$key][$lang]) && $dict[$key][$lang] !== '') {
        return $dict[$key][$lang];
    }
    if (isset($dict[$key]['en']) && $dict[$key]['en'] !== '') {
        return $dict[$key]['en'];
    }
    return $default ?? ucwords(str_replace('_', ' ', $key));
}

/**
 * Localize database record field by current language
 * Checks $row[$field . '_' . $lang], then falls back to $row[$field . '_en'] or $row[$field]
 */
function loc(?array $row, string $field, string $default = ''): string {
    if (empty($row) || !is_array($row)) {
        return $default;
    }
    $lang = current_lang();
    $langCol = $field . '_' . $lang;
    if (!empty($row[$langCol])) {
        return (string)$row[$langCol];
    }
    $enCol = $field . '_en';
    if (!empty($row[$enCol])) {
        return (string)$row[$enCol];
    }
    if (!empty($row[$field])) {
        return (string)$row[$field];
    }
    return $default;
}

/**
 * Render Header Language Switcher Dropdown HTML
 */
function render_language_switcher(): string {
    $languages = get_supported_languages();
    $current = current_lang();
    
    $html = '<div class="lang-switcher" id="langSwitcher" style="position:relative;display:inline-flex;align-items:center;">';
    $html .= '<button type="button" class="lang-switcher__btn" id="langSwitcherBtn" aria-expanded="false" style="background:#FFFFFF;border:1.5px solid #E2E8F0;border-radius:20px;padding:6px 12px;font-size:0.82rem;font-weight:700;color:#0F172A;display:inline-flex;align-items:center;gap:6px;cursor:pointer;transition:all 0.2s;">';
    $html .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
    $html .= '<span>' . htmlspecialchars($languages[$current]['native']) . '</span>';
    $html .= '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>';
    $html .= '</button>';
    
    $html .= '<div class="lang-switcher__menu" id="langSwitcherMenu" style="display:none;position:absolute;top:calc(100% + 6px);right:0;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;box-shadow:0 10px 25px rgba(15,23,42,0.1);min-width:140px;z-index:999;overflow:hidden;">';
    foreach ($languages as $code => $info) {
        $isActive = ($code === $current);
        $url = htmlspecialchars(lang_url($code));
        $bg = $isActive ? '#F1F5F9' : '#FFFFFF';
        $weight = $isActive ? '700' : '500';
        $html .= "<a href=\"{$url}\" style=\"display:flex;align-items:center;justify-content:space-between;padding:10px 14px;color:#0F172A;text-decoration:none;font-size:0.84rem;font-weight:{$weight};background:{$bg};border-bottom:1px solid #F8FAFC;transition:background 0.15s;\">";
        $html .= "<span>{$info['native']}</span>";
        if ($isActive) {
            $html .= "<span style=\"color:#059669;font-size:0.85rem;\">✓</span>";
        }
        $html .= "</a>";
    }
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
