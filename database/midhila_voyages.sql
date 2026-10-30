-- =====================================================================
-- MIDHILA VOYAGES — Relational Database Schema & Demonstration Seed
-- Engine: InnoDB | Character Set: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `midhila_voyages`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `midhila_voyages`;

-- ---------------------------------------------------------------------
-- 1. Table: admins (Administrative user accounts)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `role` VARCHAR(50) NOT NULL DEFAULT 'super_admin',
  `last_login` DATETIME DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Table: site_settings (Configurable key-value agency parameters)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(80) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Table: package_categories (Domestic, International, Pilgrimage, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `package_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(80) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon_svg` TEXT DEFAULT NULL,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Table: destinations (Domestic and International locations)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `destinations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `category` ENUM('domestic', 'international') NOT NULL DEFAULT 'domestic',
  `region_state` VARCHAR(100) NOT NULL,
  `country` VARCHAR(80) NOT NULL DEFAULT 'India',
  `tagline` VARCHAR(255) DEFAULT NULL,
  `description` TEXT NOT NULL,
  `highlights` TEXT DEFAULT NULL,
  `best_time_to_visit` VARCHAR(150) DEFAULT NULL,
  `featured_image` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(200) DEFAULT NULL,
  `latitude` DECIMAL(10, 7) DEFAULT NULL,
  `longitude` DECIMAL(10, 7) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_pilgrimage` TINYINT(1) NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `meta_title` VARCHAR(180) DEFAULT NULL,
  `meta_description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_dest_cat` (`category`),
  INDEX `idx_dest_pub` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Table: packages (Tour packages managed by the agency)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `destination_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NOT NULL UNIQUE,
  `subtitle` VARCHAR(255) DEFAULT NULL,
  `duration_days` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `duration_nights` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `short_description` VARCHAR(500) NOT NULL,
  `overview` TEXT NOT NULL,
  `starting_price` DECIMAL(10, 2) DEFAULT NULL,
  `is_price_on_request` TINYINT(1) NOT NULL DEFAULT 0,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `inclusions` TEXT DEFAULT NULL,
  `exclusions` TEXT DEFAULT NULL,
  `seasonal_info` VARCHAR(200) DEFAULT NULL,
  `featured_image` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(200) DEFAULT NULL,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_pilgrimage` TINYINT(1) NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `meta_title` VARCHAR(180) DEFAULT NULL,
  `meta_description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `package_categories`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_pkg_pub` (`is_published`),
  INDEX `idx_pkg_featured` (`is_featured`),
  INDEX `idx_pkg_pilgrim` (`is_pilgrimage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Table: package_itineraries (Day-by-day itinerary breakdown)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `package_itineraries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_id` INT UNSIGNED NOT NULL,
  `day_number` TINYINT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `meals_included` VARCHAR(100) DEFAULT NULL,
  `stay_location` VARCHAR(150) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_itinerary_pkg` (`package_id`, `day_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. Table: package_images (Supplementary image gallery for package)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `package_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(200) DEFAULT NULL,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. Table: enquiries (Trip planning and package enquiries)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reference_no` VARCHAR(40) NOT NULL UNIQUE,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `whatsapp` VARCHAR(30) DEFAULT NULL,
  `destination_id` INT UNSIGNED DEFAULT NULL,
  `destination_name` VARCHAR(150) DEFAULT NULL,
  `package_id` INT UNSIGNED DEFAULT NULL,
  `package_title` VARCHAR(200) DEFAULT NULL,
  `travel_category` VARCHAR(60) DEFAULT NULL,
  `departure_date` DATE DEFAULT NULL,
  `return_date` DATE DEFAULT NULL,
  `adults_count` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `children_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `budget_range` VARCHAR(60) DEFAULT NULL,
  `accommodation_preference` VARCHAR(60) DEFAULT NULL,
  `travel_requirements` TEXT DEFAULT NULL,
  `preferred_contact_method` ENUM('whatsapp', 'phone', 'email') NOT NULL DEFAULT 'whatsapp',
  `status` ENUM('new', 'in_progress', 'contacted', 'converted', 'closed') NOT NULL DEFAULT 'new',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_enq_ref` (`reference_no`),
  INDEX `idx_enq_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. Table: enquiry_notes (Internal agency follow-up notes)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enquiry_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `enquiry_id` INT UNSIGNED NOT NULL,
  `admin_id` INT UNSIGNED NOT NULL,
  `note` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. Table: bookings (Confirmed trip bookings & deposit workflows)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_reference` VARCHAR(40) NOT NULL UNIQUE,
  `enquiry_id` INT UNSIGNED DEFAULT NULL,
  `package_id` INT UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(120) NOT NULL,
  `customer_email` VARCHAR(120) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `travel_date` DATE NOT NULL,
  `travellers_count` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `booking_status` ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid', 'partial', 'paid', 'refunded') NOT NULL DEFAULT 'unpaid',
  `special_requests` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_bk_ref` (`booking_reference`),
  INDEX `idx_bk_status` (`booking_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. Table: payments (Payment gateway transactions & audit log)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT UNSIGNED NOT NULL,
  `booking_reference` VARCHAR(40) NOT NULL,
  `payment_gateway` VARCHAR(50) NOT NULL DEFAULT 'razorpay',
  `gateway_order_id` VARCHAR(100) NOT NULL,
  `gateway_payment_id` VARCHAR(100) DEFAULT NULL,
  `gateway_signature` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
  `status` ENUM('pending', 'authorized', 'captured', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `raw_response` TEXT DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_pay_order` (`gateway_order_id`),
  INDEX `idx_pay_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. Table: gallery_images (Curated visual travel catalog)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `gallery_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'destinations',
  `image_url` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(200) NOT NULL,
  `location_name` VARCHAR(120) NOT NULL,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. Table: testimonials (Verified customer reviews)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(120) NOT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `tour_taken` VARCHAR(150) DEFAULT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `feedback` TEXT NOT NULL,
  `travel_date` VARCHAR(50) DEFAULT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 1,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. Table: faqs (Frequently asked questions)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(50) NOT NULL DEFAULT 'General',
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `display_order` SMALLINT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. Table: activity_logs (Administrative audit trail)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `action_type` VARCHAR(60) NOT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- SEED DATA: Site Settings
-- =====================================================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'MIDHILA VOYAGES', 'general'),
('site_tagline', 'The World Is Waiting. Let\'s Make It Yours.', 'general'),
('site_description', 'Bespoke domestic, international, and sacred pilgrimage journeys crafted with care, integrity, and personalised luxury.', 'general'),
('primary_phone', '+91 12345 67890', 'contact'),
('secondary_phone', '+91 12345 67890', 'contact'),
('whatsapp_number', '+911234567890', 'contact'),
('primary_email', 'journeys@midhilavoyages.com', 'contact'),
('office_address', 'Midhila Voyages, Travel & Tourism Hub, MG Road, Kochi, Kerala - 682016, India', 'contact'),
('working_hours', 'Monday – Saturday: 9:00 AM – 7:30 PM (IST)', 'contact'),
('facebook_url', 'https://facebook.com/midhilavoyages', 'social'),
('instagram_url', 'https://instagram.com/midhilavoyages', 'social'),
('youtube_url', 'https://youtube.com/@midhilavoyages', 'social'),
('currency_symbol', '₹', 'finance'),
('currency_code', 'INR', 'finance'),
('razorpay_key_id', 'rzp_test_midhila_demo', 'payment'),
('razorpay_key_secret', 'rzp_secret_demo_123456789', 'payment'),
('payment_mode', 'test', 'payment'),
('hero_headline', 'The World Is Waiting. Let\'s Make It Yours.', 'hero'),
('hero_subheading', 'Discover handpicked escapes, immersive cultural expeditions, and sacred pilgrimage trails tailored to your rhythm.', 'hero'),
('hero_primary_cta_text', 'Plan Your Journey', 'hero'),
('hero_secondary_cta_text', 'Explore Destinations', 'hero');

-- =====================================================================
-- SEED DATA: Default Admin Account
-- Username: admin
-- Password: admin123 / travel123 / payment123 (BCRYPT hashed)
-- =====================================================================
INSERT INTO `admins` (`username`, `password_hash`, `full_name`, `email`, `role`, `is_active`) VALUES
('admin', '$2y$10$MqWc6VOvHC7FGPeefV7ygei9LLuc2PGiIDNqQ9ANRJy74Y1tS.HLK', 'Midhila Voyages Director', 'admin@midhilavoyages.com', 'super_admin', 1),
('travel', '$2y$10$MqWc6VOvHC7FGPeefV7ygei9LLuc2PGiIDNqQ9ANRJy74Y1tS.HLK', 'Travel Operations Manager', 'travel@midhilavoyages.com', 'travel_manage', 1),
('payment', '$2y$10$MqWc6VOvHC7FGPeefV7ygei9LLuc2PGiIDNqQ9ANRJy74Y1tS.HLK', 'Finance & Payment Handler', 'payment@midhilavoyages.com', 'payment_handle', 1)
ON DUPLICATE KEY UPDATE `username`=`username`;

-- =====================================================================
-- SEED DATA: Package Categories
-- =====================================================================
INSERT INTO `package_categories` (`id`, `name`, `slug`, `description`, `icon_svg`, `display_order`) VALUES
(1, 'Domestic Tour Packages', 'domestic-packages', 'Immerse in the breathtaking landscapes, hill stations, and coastal serenity of India.', 'compass', 1),
(2, 'International Tour Packages', 'international-packages', 'Seamless journeys across global iconic skylines, tropical islands, and cultural capitals.', 'globe', 2),
(3, 'Pilgrimage Tours', 'pilgrimage-tours', 'Sacred and devotional journeys thoughtfully planned with comfortable transport and revered darshans.', 'temple', 3),
(4, 'Family Holidays', 'family-holidays', 'Relaxed, multi-generational friendly itineraries with curated comfort and engaging activities.', 'users', 4),
(5, 'Honeymoon Packages', 'honeymoon-packages', 'Intimate, romantic escapes with scenic stays, private dinners, and unforgettable memories.', 'heart', 5),
(6, 'Group Tours', 'group-tours', 'Energetic group expeditions, institutional groups, and corporate retreats with coordinated logistics.', 'briefcase', 6),
(7, 'Customised Holidays', 'customised-holidays', 'Completely bespoke travel plans designed around your dates, pace, and specific desires.', 'sparkles', 7);

-- =====================================================================
-- SEED DATA: Destinations
-- =====================================================================
INSERT INTO `destinations` (`id`, `name`, `slug`, `category`, `region_state`, `country`, `tagline`, `description`, `highlights`, `best_time_to_visit`, `featured_image`, `alt_text`, `latitude`, `longitude`, `is_featured`, `is_pilgrimage`, `display_order`) VALUES
(1, 'Kerala Backwaters & Hills', 'kerala', 'domestic', 'Kerala', 'India', 'God\'s Own Country of Mist, Spice & Tranquil Waters', 'From the emerald tea estates of Munnar to tranquil overnight houseboat cruises on Vembanad Lake, Kerala offers an unparalleled blend of rejuvenation, Ayurveda, and lush nature.', 'Munnar Tea Plantations, Alleppey Houseboat Cruise, Thekkady Spice Trails, Kovalam Beaches, Kochi Colonial Heritage', 'September to March', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80', 'Kerala traditional houseboat on calm backwaters surrounded by palm trees', 9.4981, 76.3388, 1, 0, 1),
(2, 'Tamil Nadu Temple Trails', 'tamil-nadu', 'domestic', 'Tamil Nadu', 'India', 'Dravidian Architectural Wonders & Sacred Horizons', 'A cultural journey into soaring Gopurams, living heritage temples, classical arts, and the dramatic coastal shores of Rameswaram and Kanyakumari.', 'Madurai Meenakshi Amman Temple, Rameswaram Ramanathaswamy Temple, Brihadisvara Temple Thanjavur, Kanyakumari Sunset Point', 'October to March', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80', 'Magnificent towering gopuram of a historic South Indian temple in Tamil Nadu', 9.9195, 78.1193, 1, 1, 2),
(3, 'Varanasi', 'varanasi', 'domestic', 'Uttar Pradesh', 'India', 'The Eternal City of Light, Devotion & Ganga Aarti', 'Among the oldest living cities in human civilization, Varanasi stirs the soul with spiritual sunrise boat rides on the Holy Ganges, evening Maha Aarti at Dashashwamedh Ghat, and darshan at Kashi Vishwanath.', 'Kashi Vishwanath Corridor, Evening Ganga Aarti, Sunrise boat ride, Sarnath Buddhist Stupa, Banarasi Silk Trails', 'October to March', 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1200&q=80', 'Varanasi ghats at twilight with holy lamps and evening prayer ceremonies', 25.3176, 82.9739, 1, 1, 3),
(4, 'Ayodhya', 'ayodhya', 'domestic', 'Uttar Pradesh', 'India', 'Sacred Abode of Lord Rama on the Banks of Saryu', 'A city of deep spiritual resonance, sacred hymns, renovated historic ghats, and the grand Shri Ram Janmabhoomi Temple.', 'Shri Ram Janmabhoomi Mandir, Hanuman Garhi, Kanak Bhavan, Saryu River Evening Aarti', 'October to March', 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80', 'Spiritual riverbank and serene temples reflecting golden sunrise in Ayodhya', 26.7922, 82.1998, 1, 1, 4),
(5, 'Andaman Islands', 'andaman-islands', 'domestic', 'Andaman and Nicobar', 'India', 'Turquoise Waters, White Coral Sands & Coral Reefs', 'An exotic paradise of pristine shores, crystal waters, coral diving, and intriguing history at Cellular Jail.', 'Havelock Island Radhanagar Beach, Neil Island, Scuba Diving & Snorkeling, Cellular Jail Light & Sound', 'October to May', 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80', 'Pristine turquoise tropical waters and soft white sands in Andaman Islands', 11.7401, 92.6586, 1, 0, 5),
(6, 'Lakshadweep Islands', 'lakshadweep', 'domestic', 'Lakshadweep', 'India', 'Unspoiled Coral Atolls in the Heart of the Arabian Sea', 'Exclusive coral atolls, shimmering turquoise lagoons, pristine water sports, and tranquil isolation away from the rush of everyday life.', 'Agatti Island Lagoon, Bangaram Atoll, Kayaking & Snorkeling, Untouched Marine Biodiversity', 'October to April', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'Aerial view of crystal clear lagoon and coral atoll in Lakshadweep', 10.5667, 72.6417, 1, 0, 6),
(7, 'Kullu & Manali', 'kullu-manali', 'domestic', 'Himachal Pradesh', 'India', 'Alpine Meadows, Pine Forests & Snow-Capped Peaks', 'Nestled in the Beas River Valley, Kullu-Manali combines alpine beauty, adventure thrills at Solang Valley, and apple orchard serenity.', 'Solang Valley Adventure, Atal Tunnel to Sissu, Old Manali Cafes, Hadimba Devi Temple, Rohtang Pass', 'All Year (Snow: Dec-Feb, Pleasant: Mar-Jun)', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80', 'Snowcapped Himalayan mountain range and pine trees in Kullu Manali', 32.2432, 77.1892, 1, 0, 7),
(8, 'Hyderabad', 'hyderabad', 'domestic', 'Telangana', 'India', 'City of Pearls, Nizam Grandeur & Historic Splendour', 'A fascinating fusion of regal Qutb Shahi and Asaf Jahi monuments, shimmering bazaars, Ramoji Film City, and mouthwatering culinary traditions.', 'Charminar, Golconda Fort Light & Sound, Chowmahalla Palace, Ramoji Film City, Salar Jung Museum', 'October to March', 'https://images.unsplash.com/photo-1605281317010-fe5ffe798166?auto=format&fit=crop&w=1200&q=80', 'Historic Charminar monument illuminated at dusk in Hyderabad', 17.3616, 78.4747, 0, 0, 8),
(9, 'Dubai & Emirates', 'dubai', 'international', 'Emirate of Dubai', 'United Arab Emirates', 'Futuristic Marvels, Desert Glamour & Iconic Luxury', 'A dazzling world of ultra-modern architectural feats, luxurious desert safaris, gold and spice souks, and family entertainment wonders.', 'Burj Khalifa Observation Deck, Desert Dune Bashing & BBQ, Dubai Mall & Fountain Show, Dubai Marina Yacht Cruise, Museum of the Future', 'November to April', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80', 'Dramatic skyline of Dubai with Burj Khalifa against evening sunset sky', 25.2048, 55.2708, 1, 0, 9),
(10, 'Bali', 'bali', 'international', 'Bali Province', 'Indonesia', 'Island of the Gods, Terraced Valleys & Spiritual Sunsets', 'Famed for its emerald rice terraces, cliffside Uluwatu ocean temples, vibrant artisan communities in Ubud, and sun-kissed beaches.', 'Ubud Monkey Forest & Rice Terraces, Tanah Lot Sunset, Uluwatu Kecak Dance, Nusa Penida Day Excursion, Mount Batur Sunrise', 'April to October', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80', 'Scenic Balinese temple gate overlooking lush tropical greenery', -8.4095, 115.1889, 1, 0, 10),
(11, 'Singapore', 'singapore', 'international', 'Central Region', 'Singapore', 'Garden City Wonder, Skyline Splendour & World-Class Attractions', 'A gleaming island metropolis where futuristic biodomes meet rich Peranakan culture, luxury shopping, and Sentosa Island adventures.', 'Gardens by the Bay Supertree Grove, Marina Bay Sands SkyPark, Universal Studios Singapore, Sentosa Island, Night Safari', 'All Year Round', 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80', 'Futuristic Gardens by the Bay and Marina Bay skyline in Singapore', 1.3521, 103.8198, 1, 0, 11),
(12, 'Thailand (Bangkok & Phuket)', 'thailand', 'international', 'Phuket / Bangkok', 'Thailand', 'Golden Temples, Floating Markets & Tropical Archipelago', 'Experience the kinetic energy of Bangkok\'s gilded palaces followed by the serene turquoise waters and limestone karsts of Phuket and Phi Phi.', 'Grand Palace Bangkok, Phi Phi Island Speedboat Tour, James Bond Island, Wat Arun, Thai Street Food Tours', 'November to April', 'https://images.unsplash.com/photo-1506665531195-3566af2b4dfa?auto=format&fit=crop&w=1200&q=80', 'Iconic limestone karst islands rising from turquoise sea in Thailand', 7.8804, 98.3923, 1, 0, 12),
(13, 'Malaysia', 'malaysia', 'international', 'Federal Territory', 'Malaysia', 'Twin Tower Marvels, Rainforests & Island Sanctuaries', 'From the iconic Petronas Towers in Kuala Lumpur to the pristine beaches of Langkawi and the heritage lanes of Penang.', 'Petronas Twin Towers, Batu Caves, Genting Highlands Cable Car, Langkawi Island Hopping, Penang Heritage Walks', 'All Year Round', 'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=1200&q=80', 'Illuminated Petronas Twin Towers soaring into the night sky in Kuala Lumpur', 3.1390, 101.6869, 0, 0, 13),
(14, 'Maldives Atolls', 'maldives', 'international', 'North Malé Atoll', 'Maldives', 'Overwater Luxury & Endless Blue Horizons', 'The ultimate idyllic retreat featuring private overwater villas, coral house reefs, romantic sunset cruises, and tranquil island seclusion.', 'Private Overwater Villa Stay, House Reef Snorkeling with Manta Rays, Sandbank Sunset Dinner, Dolphin Cruise', 'November to April', 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1200&q=80', 'Private luxury water villas over crystalline shallow turquoise lagoon in Maldives', 3.2028, 73.2207, 1, 0, 14);

-- =====================================================================
-- SEED DATA: Packages
-- =====================================================================
INSERT INTO `packages` (`id`, `destination_id`, `category_id`, `title`, `slug`, `subtitle`, `duration_days`, `duration_nights`, `short_description`, `overview`, `starting_price`, `is_price_on_request`, `currency`, `inclusions`, `exclusions`, `seasonal_info`, `featured_image`, `alt_text`, `is_featured`, `is_pilgrimage`, `display_order`) VALUES
(1, 1, 1, 'Kerala Serenade: Munnar Hills & Alleppey Waters', 'kerala-serenade-munnar-alleppey', 'Mist-covered tea gardens and a private luxury backwater cruise', 5, 4, 'Escape into Kerala\'s lush paradise: 2 nights in romantic Munnar tea hills, 1 night in spice-scented Thekkady, and 1 unforgettable night aboard a private Alleppey houseboat.', 'Experience the soul of God\'s Own Country. Traverse rolling emerald hills, breathe fresh mountain mist among lush tea gardens in Munnar, take an elephant safari and spice plantation tour in Thekkady, and culminate with a serene private cruise across the tranquil waters of Alleppey backwaters on an authentic Kerala kettuvallam.', 24500.00, 0, 'INR', 'Accommodation in 4-star handpicked resorts, Daily buffet breakfast & dinner, Exclusive 1-night Alleppey luxury houseboat with all meals, Private AC sedan transport with experienced chauffeur, Guided spice plantation tour, All toll taxes and driver allowances', 'Airfare/train tickets to Kochi, Personal expenses and telephone charges, Optional Ayurvedic spa therapies, Entry tickets to monuments not in itinerary', 'Best experienced from September to April', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80', 'Traditional houseboat gliding through palm-fringed Kerala backwaters', 1, 0, 1),

(2, 2, 3, 'Tamil Nadu Divine Yatra: Madurai, Rameswaram & Kanyakumari', 'tamil-nadu-divine-yatra', 'A soul-enriching South Indian pilgrimage through sacred corridors', 6, 5, 'Walk through sacred gopurams and sacred ocean ghats. Visit Madurai Meenakshi, Rameswaram Ramanathaswamy 22 sacred theerthams, and Kanyakumari confluence.', 'A deeply revered spiritual circuit covering the architectural marvels of Dravidian temple culture. Experience the divine vibration of the Madurai Meenakshi Amman Temple, proceed across the Pamban Bridge to the holy island of Rameswaram for holy bath in the 22 Kundas, and conclude at Kanyakumari where three oceans meet with sunset and sunrise views.', 22000.00, 0, 'INR', 'Accommodation in curated comfortable hotels near temple precincts, Daily breakfast and traditional vegetarian dinners, Dedicated AC transport with courteous driver, Assistance for special darshan queues where permitted, Pamban bridge crossing and sightseeing', 'Flight or train fare to Madurai, Special VIP Puja and archana receipts, Personal laundry and tips, Any religious offerings', 'Comfortable climate from October to March', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80', 'Soaring Dravidian gopuram with intricate sculptures in Madurai temple', 1, 1, 2),

(3, 3, 3, 'Kashi & Ayodhya Sacred Circuit', 'kashi-ayodhya-sacred-circuit', 'The eternal spiritual journey of northern India', 5, 4, 'Darshan at Kashi Vishwanath, evening Ganga Aarti at Dashashwamedh Ghat, Sarnath Stupa, and the holy city of Ayodhya including Shri Ram Mandir.', 'Immerse in timeless devotion across two of India\'s most holy cities. Experience the divine dawn boat cruise on the sacred Ganges, witness the mesmerizing evening Maha Aarti, seek blessings at the restored Kashi Vishwanath Dham, and travel in comfort to Ayodhya for darshan of Ram Lalla at the newly consecrated Ram Janmabhoomi Mandir.', 26800.00, 0, 'INR', '4 nights hotel stay with pure vegetarian meals, Private AC vehicle for transfers and Varanasi-Ayodhya transit, Exclusive private sunrise boat ride on the holy Ganges, Dedicated local pilgrimage coordinator in Kashi, All applicable parking and vehicle taxes', 'Airfare/train fare to Varanasi, Individual temple archana and offerings, Sarnath museum entry tickets, Personal expenses', 'Ideal between October and March', 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1200&q=80', 'Evening Ganga Aarti ceremony illuminated by brass lamps in Varanasi', 1, 1, 3),

(4, 5, 1, 'Andaman Island Haven: Port Blair & Havelock', 'andaman-island-haven', 'Turquoise waters, coral reef adventures, and tropical ease', 6, 5, 'Unwind in pure island bliss. Explore historical Cellular Jail, cruise on luxury catamarans to Havelock Island, and sink your toes into Radhanagar Beach.', 'Discover India\'s most breathtaking island getaway. Relax on the world-renowned Radhanagar Beach, snorkel among vibrant coral reefs at Elephant Beach, explore the historic Cellular Jail in Port Blair, and enjoy smooth luxury catamaran cruises between pristine tropical islands.', 38500.00, 0, 'INR', '5 nights accommodation in beachfront & premium resorts, Daily buffet breakfast, Makruzz or Green Ocean luxury catamaran tickets between islands, Private AC vehicles for all transfers and sightseeing, Snorkeling session at Elephant Beach with guide, Cellular Jail light and sound show entry', 'Airfare to Port Blair, Scuba diving or sea karting optional upgrades, Lunch and beverages not mentioned, Camera and video permits', 'Best time to visit is October to May', 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80', 'Radhanagar beach turquoise ocean and white sands in Havelock Island', 1, 0, 4),

(5, 7, 4, 'Himalayan Wonder: Kullu, Manali & Solang Valley', 'himalayan-wonder-manali', 'Pine-scented mountain air, rushing rivers, and snow peaks', 5, 4, 'A rejuvenating alpine retreat featuring Solang Valley adventure, Hadimba Devi temple, Vashisht hot springs, and scenic drive through the Atal Tunnel to Sissu.', 'Head to the high Himalayas of Himachal Pradesh. Soak in panoramic vistas of snow-draped mountain peaks, stroll through fragrant pine forests, thrill in adventure activities in Solang Valley, and travel through the engineering marvel of Atal Tunnel to witness the raw beauty of Lahaul Valley.', 19800.00, 0, 'INR', '4 nights stay in hillside view resorts, Daily breakfast and hearty dinner, Exclusive AC/Heater private vehicle throughout the trip, Excursion to Solang Valley and Atal Tunnel (subject to weather), Sightseeing of Manali town and temples', 'Flight/train tickets to Chandigarh or Delhi, Adventure sports charges (paragliding, skiing, ATV), Rohtang Pass NGT permit fee if opted, Personal warm clothing rentals', 'Snow season: Dec-Feb; pleasant summer: Mar-Jun', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80', 'Manali valley covered in snow with majestic pine trees', 1, 0, 5),

(6, 9, 2, 'Gleaming Dubai & Desert Splendour', 'gleaming-dubai-desert-splendour', 'The ultimate blend of futuristic icons and Arabian desert magic', 5, 4, 'Explore Burj Khalifa, evening desert safari with dune bashing & barbecue, Dubai Marina dhow cruise with dinner, and luxury city highlights.', 'Step into a world of architectural wonder and desert allure. Ascend to the 124th floor of the iconic Burj Khalifa, ride through golden desert dunes in a 4x4 with a starlit Arabian barbecue dinner and tanoura dance, cruise Dubai Marina on a traditional glass-enclosed dhow, and enjoy duty-free shopping at the world\'s grandest malls.', 46500.00, 0, 'INR', '4 nights accommodation in 4-star Dubai city hotel, Daily buffet breakfast, Desert Safari with dune bashing, BBQ dinner, and live shows, Burj Khalifa 124th floor non-peak entry ticket, Marina Dhow Cruise with international buffet dinner, Half-day Dubai panoramic city tour, Airport transfers in private AC vehicle', 'International airfare and UAE tourist visa, Tourism Dirham fee paid directly to hotel, Lunch and personal shopping expenses, Travel insurance', 'Best visited between November and April', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80', 'Burj Khalifa and Dubai downtown skyline gleaming in twilight', 1, 0, 6),

(7, 10, 5, 'Romantic Bali: Ubud Rainforest & Cliffside Ocean', 'romantic-bali-ubud-ocean', 'Intimate tropical sanctuary, private pool villa, and sunset temples', 6, 5, 'The quintessential honeymoon getaway. Private pool villa in Ubud, rice terrace swing, Tanah Lot ocean temple sunset, and Uluwatu Kecak performance.', 'Designed for couples and seekers of tropical tranquility. Wake up to lush tropical greenery in an Ubud private pool villa, swing over emerald Tegallalang rice terraces, explore volcanic Mount Batur vistas, and witness unforgettable golden sunsets over the Indian Ocean from dramatic cliffside temples.', 52000.00, 0, 'INR', '3 nights in Ubud private pool villa + 2 nights in Seminyak luxury beach resort, Daily breakfast, 1 romantic candlelight dinner with wine, Tegallalang rice terrace & giant swing tickets, Tanah Lot and Uluwatu temple guided excursions, Private AC car with English-speaking chauffeur-guide, Return airport transfers', 'International flight tickets, Indonesia visa on arrival, Lunches and extra beverage expenses, Water sports fees', 'Ideal from April to October', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80', 'Serene Bali infinity pool looking out into lush jungle canopy', 1, 0, 7),

(8, 14, 5, 'Maldives Azure Dream: Overwater Luxury Stay', 'maldives-azure-dream', 'Infinite shades of blue, private overwater villa, and coral reef magic', 4, 3, 'Escape to a private luxury island resort. Stay suspended over turquoise lagoons, wake up to panoramic ocean vistas, and enjoy all-inclusive dining.', 'Experience the pinnacle of tropical indulgence. Live directly above the crystal-clear turquoise waters of the Indian Ocean in a thatched overwater bungalow. Snorkel directly from your private sun deck into coral gardens alive with colorful marine life and savor gourmet meals with your toes in soft white sand.', 98000.00, 0, 'INR', '3 nights in premium Overwater Lagoon Villa, Full board meal plan (Breakfast, Lunch, Dinner at resort), Return speedboat transfers from Velana International Airport, Complimentary snorkeling gear and non-motorized water sports, Welcome bottle of sparkling wine and fruit basket, All Maldives green tax and service charges', 'International flight tickets to Malé, Spa treatments and scuba diving excursions, Alcoholic beverages unless opted for all-inclusive upgrade, Gratuities', 'Spectacular weather from November to April', 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1200&q=80', 'Scenic overwater bungalows leading into azure blue lagoon in Maldives', 1, 0, 8),

(9, 6, 7, 'Lakshadweep Coral Lagoon Escape', 'lakshadweep-coral-lagoon-escape', 'Untouched coral atolls, crystal waters, and tranquil seclusion', 5, 4, 'Discover India\'s secret island sanctuary. Unwind on Agatti and Bangaram islands with guided lagoon kayaking, coral reef tours, and island permit coordination.', 'Breathe the purest ocean air in Lakshadweep. Our curated package handles all mandatory island entry permits, comfortable beachfront cottage stays on Agatti island, boat excursions to the uninhabited paradise of Bangaram atoll, and personalized water activities.', 32000.00, 0, 'INR', '4 nights stay in beachfront cottages in Agatti, All meals (Breakfast, Lunch, Evening Tea, Dinner), Lakshadweep entry permit processing and paper clearances, Airport pickup and drop by boat/jeep, Glass-bottom boat ride and guided snorkeling', 'Airfare from Kochi to Agatti and return, Scuba diving certification charges, Personal shopping or tipping', 'Peak season October to April', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 'Pristine white sand shoreline and turquoise lagoon in Lakshadweep', 0, 0, 9),

(10, 11, 4, 'Vibrant Singapore City & Sentosa Discovery', 'vibrant-singapore-sentosa', 'World-class modern wonder, futuristic gardens, and theme park thrills', 5, 4, 'Marvel at Gardens by the Bay, take the cable car into Sentosa Island, enjoy Universal Studios Singapore, and take in Marina Bay panoramic views.', 'A seamless, high-comfort itinerary tailored for families and modern explorers. Experience the world\'s best airport at Jewel Changi, stand beneath the iconic Supertrees of Gardens by the Bay, experience world-class rides at Universal Studios, and cruise Singapore River by twilight.', 54000.00, 0, 'INR', '4 nights stay in central Singapore 4-star hotel, Daily breakfast, Gardens by the Bay (Flower Dome & Cloud Forest) entry, Universal Studios Singapore one-day pass, Sentosa cable car & Island discovery, Return airport transfers and city tour in private AC coach', 'International flights, Singapore tourist visa, Meals other than breakfast, Personal expenses', 'Pleasant all year round', 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80', 'Gardens by the bay illuminated Supertrees in Singapore', 0, 0, 10);

-- =====================================================================
-- SEED DATA: Itineraries for Packages
-- =====================================================================
-- Package 1: Kerala Serenade
INSERT INTO `package_itineraries` (`package_id`, `day_number`, `title`, `description`, `meals_included`, `stay_location`) VALUES
(1, 1, 'Arrival in Kochi & Scenic Drive to Munnar Hills', 'Upon arrival at Kochi International Airport or Railway Station, our chauffeur greets you. Embark on a picturesque 4-hour drive up into the Western Ghats passing through spice-scented valleys, Cheeyappara Waterfalls, and Valara Waterfalls. Check into your hillside resort in Munnar and relax amidst cool mountain air.', 'Dinner', 'Munnar Resort'),
(1, 2, 'Munnar Tea Estates & Eravikulam National Park', 'Morning visit to Eravikulam National Park, home to the endangered Nilgiri Tahr. Tour the Tata Tea Museum to observe the art of tea crafting, followed by visits to Mattupetty Dam, Echo Point, and the lush Rose Garden. Evening free to stroll in Munnar town market.', 'Breakfast, Dinner', 'Munnar Resort'),
(1, 3, 'Munnar to Thekkady: Spice Trails & Lake Periyar', 'Scenic drive to Thekkady through rolling pepper and cardamom plantations. Afternoon guided walk through a lush organic spice plantation learning about vanilla, cinnamon, and cloves. Optional evening Kathakali classical dance and Kalaripayattu martial arts show.', 'Breakfast, Dinner', 'Thekkady Hotel'),
(1, 4, 'Thekkady to Alleppey: Boarding the Private Houseboat', 'Drive down to Alleppey backwaters. Board your private traditionally crafted luxury houseboat at 12:00 PM. Glide silently past swaying palms, Chinese fishing nets, and village life along Vembanad Lake. Savor authentic freshly prepared Kerala cuisine on board with overnight cruise stay.', 'Breakfast, Lunch, Dinner', 'Alleppey Houseboat'),
(1, 5, 'Alleppey to Kochi Departure', 'Enjoy sunrise over calm backwaters with a traditional breakfast. Disembark at 9:00 AM. Travel back to Kochi with a stop at Fort Kochi to see colonial heritage and Chinese fishing nets before dropping you at Kochi Airport/Railway Station for your flight home.', 'Breakfast', 'Departure');

-- Package 2: Tamil Nadu Divine Yatra
INSERT INTO `package_itineraries` (`package_id`, `day_number`, `title`, `description`, `meals_included`, `stay_location`) VALUES
(2, 1, 'Arrival in Madurai & Meenakshi Amman Darshan', 'Arrive at Madurai, the soul of Tamil Nadu. Check into your hotel. In the evening, visit the world-renowned Meenakshi Sundareswarar Temple to witness the awe-inspiring sculpted gopurams and the sacred night ceremony (Palliarai Puja).', 'Dinner', 'Madurai Hotel'),
(2, 2, 'Madurai Heritage & Transit to Holy Rameswaram', 'Morning visit to Thirumalai Nayakar Mahal and Gandhi Memorial Museum. Drive to the holy island of Rameswaram via the engineering marvel Pamban Bridge. Evening darshan at Ramanathaswamy Temple and walk along the calm Agnitheertham seashore.', 'Breakfast, Dinner', 'Rameswaram Hotel'),
(2, 3, 'Sacred 22 Theertham Bath & Dhanushkodi Excursion', 'Early morning holy bath across the 22 sacred wells inside the temple complex followed by special darshan of the revered Spatika Lingam. Afternoon trip to ghost town Dhanushkodi and Ram Setu viewpoint. Evening spiritual satsang and rest.', 'Breakfast, Dinner', 'Rameswaram Hotel'),
(2, 4, 'Rameswaram to Kanyakumari: Land\'s End', 'Drive from Rameswaram to Kanyakumari, the southernmost tip of peninsular India. Check into hotel. Visit the iconic Vivekananda Rock Memorial and Thiruvalluvar Statue via ferry. Watch the breathtaking sunset over the confluence of three oceans.', 'Breakfast, Dinner', 'Kanyakumari Hotel'),
(2, 5, 'Kanyakumari Sunrise & Suchindram Temple', 'Witness the majestic sunrise over the Indian Ocean. Visit the sacred Bhagavathy Amman Temple and the historic Suchindram Thanumalayan Temple with its musical stone pillars. Afternoon visit to Padmanabhapuram Wooden Palace.', 'Breakfast, Dinner', 'Kanyakumari Hotel'),
(2, 6, 'Kanyakumari to Trivandrum Departure', 'After breakfast, drive to Thiruvananthapuram (Trivandrum). Visit the sacred Sree Padmanabhaswamy Temple for blessings. Transfer to Trivandrum Airport or Railway Station with divine memories.', 'Breakfast', 'Departure');

-- Package 3: Kashi & Ayodhya
INSERT INTO `package_itineraries` (`package_id`, `day_number`, `title`, `description`, `meals_included`, `stay_location`) VALUES
(3, 1, 'Arrival in Varanasi & Evening Maha Ganga Aarti', 'Arrive at Lal Bahadur Shastri Airport or Varanasi Junction. Warm welcome by our representative. Check into hotel. At dusk, head to Dashashwamedh Ghat for an exclusive boat view of the world-famous evening Maha Ganga Aarti.', 'Dinner', 'Varanasi Hotel'),
(3, 2, 'Sacred Kashi Vishwanath Darshan & Sarnath', 'Early morning dawn boat ride along the historic ghats. Visit Kashi Vishwanath Jyotirlinga Temple through the grand Vishwanath Corridor, Annapurna Mandir, and Kal Bhairav Temple. Afternoon excursion to Sarnath, where Lord Buddha gave his first sermon.', 'Breakfast, Dinner', 'Varanasi Hotel'),
(3, 3, 'Varanasi to Ayodhya: City of Lord Ram', 'After breakfast, take a comfortable 4-hour private drive to the holy city of Ayodhya. Check in to your hotel. Visit the tranquil banks of Saryu River and participate in the melodious evening Saryu Aarti followed by a visit to Ram Ki Paidi.', 'Breakfast, Dinner', 'Ayodhya Hotel'),
(3, 4, 'Shri Ram Janmabhoomi Darshan & Historic Temples', 'Special morning darshan at the magnificent Shri Ram Janmabhoomi Temple to view Ram Lalla. Visit the revered Hanuman Garhi temple and Kanak Bhavan. Spend the afternoon exploring Ayodhya\'s historic streets and heritage.', 'Breakfast, Dinner', 'Ayodhya Hotel'),
(3, 5, 'Ayodhya Departure or Transit to Varanasi/Lucknow', 'Morning prayer and breakfast. Departure transfer to Ayodhya Airport/Railway Station or drive to Lucknow/Varanasi airport for your onward journey home.', 'Breakfast', 'Departure');

-- =====================================================================
-- SEED DATA: Package Supplementary Gallery Images
-- =====================================================================
INSERT INTO `package_images` (`package_id`, `image_url`, `caption`, `display_order`) VALUES
(1, 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=900&q=80', 'Munnar mist-covered tea estate morning', 1),
(1, 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=900&q=80', 'Traditional Kerala kettuvallam luxury interior', 2),
(1, 'https://images.unsplash.com/photo-1590050752117-238cb0fb12b1?auto=format&fit=crop&w=900&q=80', 'Thekkady Periyar wildlife sanctuary lake', 3),
(2, 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=900&q=80', 'Madurai Meenakshi temple golden corridor', 1),
(2, 'https://images.unsplash.com/photo-1609766857041-ed402ea8069a?auto=format&fit=crop&w=900&q=80', 'Rameswaram Pamban sea bridge', 2),
(2, 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=900&q=80', 'Kanyakumari Vivekananda rock memorial at sea', 3),
(3, 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=900&q=80', 'Varanasi holy evening Ganga Aarti', 1),
(3, 'https://images.unsplash.com/photo-1571536802807-30451e3955d8?auto=format&fit=crop&w=900&q=80', 'Ganges sunrise wooden boat cruise', 2),
(3, 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=80', 'Ayodhya Saryu riverfront and temples', 3),
(6, 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80', 'Dubai Burj Khalifa fountain show at night', 1),
(6, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=900&q=80', 'Golden Arabian desert dune safari at sunset', 2),
(7, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=900&q=80', 'Bali Ubud private pool jungle villa', 1),
(7, 'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=900&q=80', 'Tegallalang green stepped rice terraces', 2),
(8, 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=900&q=80', 'Maldives luxury overwater walkway and turquoise lagoon', 1),
(8, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80', 'Maldives soft white sand beach and coral waters', 2);

-- =====================================================================
-- SEED DATA: Gallery Images
-- =====================================================================
INSERT INTO `gallery_images` (`title`, `category`, `image_url`, `alt_text`, `location_name`, `display_order`) VALUES
('Tea Plantations at Dawn', 'destinations', 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1000&q=80', 'Rolling green tea terraces in Munnar covered in morning mist', 'Munnar, Kerala', 1),
('Backwater Horizon', 'destinations', 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1000&q=80', 'Traditional kettuvallam houseboat on Kerala backwaters', 'Alleppey, Kerala', 2),
('Grandeur of Dravidian Gopuram', 'pilgrimages', 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1000&q=80', 'Intricate carvings on ancient Madurai temple gopuram', 'Madurai, Tamil Nadu', 3),
('Spiritual Twilight at Ganga Ghats', 'pilgrimages', 'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1000&q=80', 'Lamps and prayer ceremonies by the holy Ganges in Varanasi', 'Varanasi, Uttar Pradesh', 4),
('Turquoise Lagoon Paradise', 'nature', 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1000&q=80', 'Crystal clear waters at Radhanagar Beach', 'Havelock, Andaman', 5),
('Himalayan Winter Vista', 'nature', 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1000&q=80', 'Snow-blanketed peaks and pine trees in Solang Valley', 'Manali, Himachal Pradesh', 6),
('Burj Khalifa Starlit Skyline', 'luxury', 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=80', 'Iconic Burj Khalifa and Dubai Fountain at night', 'Dubai, UAE', 7),
('Balinese Serenity & Sacred Rice Terraces', 'destinations', 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=80', 'Traditional Balinese temple gateway and tropical palm trees', 'Ubud, Bali', 8),
('Overwater Lagoon Retreat', 'luxury', 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1000&q=80', 'Luxury thatched water villas over turquoise lagoon in Maldives', 'Malé Atolls, Maldives', 9);

-- =====================================================================
-- SEED DATA: FAQs
-- =====================================================================
INSERT INTO `faqs` (`category`, `question`, `answer`, `display_order`) VALUES
('General', 'How do I enquire about or book a tour package with Midhila Voyages?', 'You can simply use our "Plan My Trip" enquiry form on the website, select a specific tour package and click "Enquire on WhatsApp", or call our travel specialists directly. We will discuss your preferred travel dates, group size, and requirements to prepare a detailed, customized itinerary proposal for you.', 1),
('General', 'Can itineraries be customized to our specific preferences?', 'Yes, absolutely. Every itinerary on our website serves as a curated foundation. Our travel designers can personalize the duration, add specific destinations, upgrade accommodation categories, incorporate private guides, or include special dietary needs to match your rhythm.', 2),
('Payments', 'What payment methods do you support for package deposits and bookings?', 'We accept secure digital payments including UPI, Net Banking, Credit/Debit cards via our payment gateway (Razorpay), as well as direct NEFT/RTGS bank transfers to our verified current account. Receipts and booking vouchers are generated promptly upon confirmation.', 3),
('Payments', 'What is your cancellation and refund policy?', 'Cancellations made 30 days or more prior to the departure date are eligible for a refund after deducting nominal administrative and supplier non-refundable fees. For detailed terms and timeline breakdowns, please consult our dedicated Cancellation and Refund Policy page.', 4),
('Pilgrimage', 'How are pilgrimage tours organized, and what special assistance is provided?', 'Our sacred pilgrimage circuits (such as Varanasi, Ayodhya, and Tamil Nadu Temple trails) are curated with senior-citizen and family comfort in mind. We arrange clean AC transfers, comfortable hotel stays near temple precincts, guidance on darshan timings, assistance with special queue bookings where permitted, and pure vegetarian dining options.', 5),
('Documents', 'What travel documents do I need for domestic and international tours?', 'For all domestic travel within India, a valid government-issued photo ID (Aadhaar card, Passport, or Voter ID) is mandatory for hotel check-ins and flight/train boarding. For international journeys (Dubai, Bali, Singapore, etc.), you must hold an Indian Passport with at least 6 months validity from the travel date, along with the appropriate tourist visa or visa-on-arrival approvals.', 6),
('General', 'Are flights and train tickets included in the package prices?', 'Unless explicitly indicated in the package inclusions, standard packages cover ground transport, hotel accommodation, breakfasts, and guided sightseeing. Our ticketing desk can seamlessly arrange your domestic or international airfare or train tickets upon request at current airline/railway tariffs.', 7);

-- =====================================================================
-- SEED DATA: Testimonials (Verified Customer Experiences)
-- =====================================================================
INSERT INTO `testimonials` (`client_name`, `location`, `tour_taken`, `rating`, `feedback`, `travel_date`, `is_verified`, `is_featured`, `display_order`) VALUES
('Narayanan K. & Family', 'Kottayam, Kerala', 'Tamil Nadu Divine Yatra (Madurai - Rameswaram)', 5, 'Midhila Voyages arranged our parents\' pilgrimage with utmost care and devotion. The vehicle was immaculately clean, the driver was courteous and knowledgeable, and all hotel accommodations were conveniently close to the temples. Truly a peaceful experience.', 'February 2026', 1, 1, 1),
('Dr. Anand & Meera', 'Bengaluru, Karnataka', 'Kerala Serenade (Munnar & Alleppey)', 5, 'The private houseboat experience in Alleppey arranged by Midhila Voyages exceeded our expectations. The chef served delicious authentic Kerala meals, and the tea estate resort in Munnar had breathtaking misty valley views. Highly recommended for couples!', 'January 2026', 1, 1, 2),
('Rajesh Varma & Group', 'Chennai, Tamil Nadu', 'Kashi & Ayodhya Sacred Circuit', 5, 'Coordinating a 12-member group tour to Varanasi and Ayodhya could have been challenging, but Midhila Voyages handled every detail seamlessly — from airport pickups to sunrise boats on the Ganges and smooth darshan at Ram Janmabhoomi.', 'March 2026', 1, 1, 3);
