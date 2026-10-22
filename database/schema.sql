-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: midhila_voyages
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT NULL,
  `action_type` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'admin_login','Administrator \'admin\' logged in successfully.','127.0.0.1','2026-10-18 20:21:22');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `role` enum('super_admin','manager','editor') NOT NULL DEFAULT 'super_admin',
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$MqWc6VOvHC7FGPeefV7ygei9LLuc2PGiIDNqQ9ANRJy74Y1tS.HLK','Midhila Voyages Director','admin@midhilavoyages.com','super_admin','2026-10-18 21:53:11',1,'2026-10-18 19:50:10','2026-10-18 23:11:41');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(40) NOT NULL,
  `enquiry_id` int(10) unsigned DEFAULT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_email` varchar(120) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `travel_date` date NOT NULL,
  `travellers_count` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `booking_status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','partial','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `special_requests` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `enquiry_id` (`enquiry_id`),
  KEY `package_id` (`package_id`),
  KEY `idx_bk_ref` (`booking_reference`),
  KEY `idx_bk_status` (`booking_status`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinations`
--

DROP TABLE IF EXISTS `destinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destinations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `category` enum('domestic','international') NOT NULL DEFAULT 'domestic',
  `region_state` varchar(100) NOT NULL,
  `country` varchar(80) NOT NULL DEFAULT 'India',
  `tagline` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `highlights` text DEFAULT NULL,
  `best_time_to_visit` varchar(150) DEFAULT NULL,
  `featured_image` varchar(255) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_pilgrimage` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `meta_title` varchar(180) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_dest_cat` (`category`),
  KEY `idx_dest_pub` (`is_published`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinations`
--

LOCK TABLES `destinations` WRITE;
/*!40000 ALTER TABLE `destinations` DISABLE KEYS */;
INSERT INTO `destinations` VALUES (1,'Kerala Backwaters & Hills','kerala','domestic','Kerala','India','God\'s Own Country of Mist, Spice & Tranquil Waters','From the emerald tea estates of Munnar to tranquil overnight houseboat cruises on Vembanad Lake, Kerala offers an unparalleled blend of rejuvenation, Ayurveda, and lush nature.','Munnar Tea Plantations, Alleppey Houseboat Cruise, Thekkady Spice Trails, Kovalam Beaches, Kochi Colonial Heritage','September to March','https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80','Kerala Backwaters & Hills',NULL,NULL,1,0,1,1,'','','2026-10-18 19:50:10','2026-10-18 22:28:50'),(2,'Tamil Nadu Temple Trails','tamil-nadu','domestic','Tamil Nadu','India','Dravidian Architectural Wonders & Sacred Horizons','A cultural journey into soaring Gopurams, living heritage temples, classical arts, and the dramatic coastal shores of Rameswaram and Kanyakumari.','Madurai Meenakshi Amman Temple, Rameswaram Ramanathaswamy Temple, Brihadisvara Temple Thanjavur, Kanyakumari Sunset Point','October to March','https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80','Magnificent towering gopuram of a historic South Indian temple in Tamil Nadu',9.9195000,78.1193000,1,1,1,2,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(3,'Varanasi','varanasi','domestic','Uttar Pradesh','India','The Eternal City of Light, Devotion & Ganga Aarti','Among the oldest living cities in human civilization, Varanasi stirs the soul with spiritual sunrise boat rides on the Holy Ganges, evening Maha Aarti at Dashashwamedh Ghat, and darshan at Kashi Vishwanath.','Kashi Vishwanath Corridor, Evening Ganga Aarti, Sunrise boat ride, Sarnath Buddhist Stupa, Banarasi Silk Trails','October to March','https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1200&q=80','Varanasi ghats at twilight with holy lamps and evening prayer ceremonies',25.3176000,82.9739000,1,1,1,3,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(4,'Ayodhya','ayodhya','domestic','Uttar Pradesh','India','Sacred Abode of Lord Rama on the Banks of Saryu','A city of deep spiritual resonance, sacred hymns, renovated historic ghats, and the grand Shri Ram Janmabhoomi Temple.','Shri Ram Janmabhoomi Mandir, Hanuman Garhi, Kanak Bhavan, Saryu River Evening Aarti','October to March','https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80','Spiritual riverbank and serene temples reflecting golden sunrise in Ayodhya',26.7922000,82.1998000,1,1,1,4,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(5,'Andaman Islands','andaman-islands','domestic','Andaman and Nicobar','India','Turquoise Waters, White Coral Sands & Coral Reefs','An exotic paradise of pristine shores, crystal waters, coral diving, and intriguing history at Cellular Jail.','Havelock Island Radhanagar Beach, Neil Island, Scuba Diving & Snorkeling, Cellular Jail Light & Sound','October to May','https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80','Pristine turquoise tropical waters and soft white sands in Andaman Islands',11.7401000,92.6586000,1,0,1,5,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(6,'Lakshadweep Islands','lakshadweep','domestic','Lakshadweep','India','Unspoiled Coral Atolls in the Heart of the Arabian Sea','Exclusive coral atolls, shimmering turquoise lagoons, pristine water sports, and tranquil isolation away from the rush of everyday life.','Agatti Island Lagoon, Bangaram Atoll, Kayaking & Snorkeling, Untouched Marine Biodiversity','October to April','https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80','Aerial view of crystal clear lagoon and coral atoll in Lakshadweep',10.5667000,72.6417000,1,0,1,6,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(7,'Kullu & Manali','kullu-manali','domestic','Himachal Pradesh','India','Alpine Meadows, Pine Forests & Snow-Capped Peaks','Nestled in the Beas River Valley, Kullu-Manali combines alpine beauty, adventure thrills at Solang Valley, and apple orchard serenity.','Solang Valley Adventure, Atal Tunnel to Sissu, Old Manali Cafes, Hadimba Devi Temple, Rohtang Pass','All Year (Snow: Dec-Feb, Pleasant: Mar-Jun)','https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80','Snowcapped Himalayan mountain range and pine trees in Kullu Manali',32.2432000,77.1892000,1,0,1,7,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(8,'Hyderabad','hyderabad','domestic','Telangana','India','City of Pearls, Nizam Grandeur & Historic Splendour','A fascinating fusion of regal Qutb Shahi and Asaf Jahi monuments, shimmering bazaars, Ramoji Film City, and mouthwatering culinary traditions.','Charminar, Golconda Fort Light & Sound, Chowmahalla Palace, Ramoji Film City, Salar Jung Museum','October to March','https://images.unsplash.com/photo-1605281317010-fe5ffe798166?auto=format&fit=crop&w=1200&q=80','Historic Charminar monument illuminated at dusk in Hyderabad',17.3616000,78.4747000,0,0,1,8,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(9,'Dubai & Emirates','dubai','international','Emirate of Dubai','United Arab Emirates','Futuristic Marvels, Desert Glamour & Iconic Luxury','A dazzling world of ultra-modern architectural feats, luxurious desert safaris, gold and spice souks, and family entertainment wonders.','Burj Khalifa Observation Deck, Desert Dune Bashing & BBQ, Dubai Mall & Fountain Show, Dubai Marina Yacht Cruise, Museum of the Future','November to April','https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80','Dramatic skyline of Dubai with Burj Khalifa against evening sunset sky',25.2048000,55.2708000,1,0,1,9,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(10,'Bali','bali','international','Bali Province','Indonesia','Island of the Gods, Terraced Valleys & Spiritual Sunsets','Famed for its emerald rice terraces, cliffside Uluwatu ocean temples, vibrant artisan communities in Ubud, and sun-kissed beaches.','Ubud Monkey Forest & Rice Terraces, Tanah Lot Sunset, Uluwatu Kecak Dance, Nusa Penida Day Excursion, Mount Batur Sunrise','April to October','https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80','Scenic Balinese temple gate overlooking lush tropical greenery',-8.4095000,115.1889000,1,0,1,10,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(11,'Singapore','singapore','international','Central Region','Singapore','Garden City Wonder, Skyline Splendour & World-Class Attractions','A gleaming island metropolis where futuristic biodomes meet rich Peranakan culture, luxury shopping, and Sentosa Island adventures.','Gardens by the Bay Supertree Grove, Marina Bay Sands SkyPark, Universal Studios Singapore, Sentosa Island, Night Safari','All Year Round','https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80','Futuristic Gardens by the Bay and Marina Bay skyline in Singapore',1.3521000,103.8198000,1,0,1,11,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(12,'Thailand (Bangkok & Phuket)','thailand','international','Phuket / Bangkok','Thailand','Golden Temples, Floating Markets & Tropical Archipelago','Experience the kinetic energy of Bangkok\'s gilded palaces followed by the serene turquoise waters and limestone karsts of Phuket and Phi Phi.','Grand Palace Bangkok, Phi Phi Island Speedboat Tour, James Bond Island, Wat Arun, Thai Street Food Tours','November to April','https://images.unsplash.com/photo-1506665531195-3566af2b4dfa?auto=format&fit=crop&w=1200&q=80','Iconic limestone karst islands rising from turquoise sea in Thailand',7.8804000,98.3923000,1,0,1,12,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(13,'Malaysia','malaysia','international','Federal Territory','Malaysia','Twin Tower Marvels, Rainforests & Island Sanctuaries','From the iconic Petronas Towers in Kuala Lumpur to the pristine beaches of Langkawi and the heritage lanes of Penang.','Petronas Twin Towers, Batu Caves, Genting Highlands Cable Car, Langkawi Island Hopping, Penang Heritage Walks','All Year Round','https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=1200&q=80','Illuminated Petronas Twin Towers soaring into the night sky in Kuala Lumpur',3.1390000,101.6869000,0,0,1,13,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(14,'Maldives Atolls','maldives','international','North Mal├® Atoll','Maldives','Overwater Luxury & Endless Blue Horizons','The ultimate idyllic retreat featuring private overwater villas, coral house reefs, romantic sunset cruises, and tranquil island seclusion.','Private Overwater Villa Stay, House Reef Snorkeling with Manta Rays, Sandbank Sunset Dinner, Dolphin Cruise','November to April','https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1200&q=80','Private luxury water villas over crystalline shallow turquoise lagoon in Maldives',3.2028000,73.2207000,1,0,1,14,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10');
/*!40000 ALTER TABLE `destinations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiries`
--

DROP TABLE IF EXISTS `enquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(40) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `whatsapp` varchar(30) DEFAULT NULL,
  `destination_id` int(10) unsigned DEFAULT NULL,
  `destination_name` varchar(150) DEFAULT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `package_title` varchar(200) DEFAULT NULL,
  `travel_category` varchar(60) DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `adults_count` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `children_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `budget_range` varchar(60) DEFAULT NULL,
  `accommodation_preference` varchar(60) DEFAULT NULL,
  `travel_requirements` text DEFAULT NULL,
  `preferred_contact_method` enum('whatsapp','phone','email') NOT NULL DEFAULT 'whatsapp',
  `status` enum('new','in_progress','contacted','converted','closed') NOT NULL DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_no` (`reference_no`),
  KEY `destination_id` (`destination_id`),
  KEY `package_id` (`package_id`),
  KEY `idx_enq_ref` (`reference_no`),
  KEY `idx_enq_status` (`status`),
  CONSTRAINT `enquiries_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `enquiries_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiries`
--

LOCK TABLES `enquiries` WRITE;
/*!40000 ALTER TABLE `enquiries` DISABLE KEYS */;
/*!40000 ALTER TABLE `enquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiry_notes`
--

DROP TABLE IF EXISTS `enquiry_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enquiry_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `enquiry_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `note` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `enquiry_id` (`enquiry_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `enquiry_notes_ibfk_1` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `enquiry_notes_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiry_notes`
--

LOCK TABLES `enquiry_notes` WRITE;
/*!40000 ALTER TABLE `enquiry_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `enquiry_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL DEFAULT 'General',
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'General','How do I enquire about or book a tour package with Midhila Voyages?','You can simply use our \"Plan My Trip\" enquiry form on the website, select a specific tour package and click \"Enquire on WhatsApp\", or call our travel specialists directly. We will discuss your preferred travel dates, group size, and requirements to prepare a detailed, customized itinerary proposal for you.',1,1,'2026-10-18 19:50:10'),(2,'General','Can itineraries be customized to our specific preferences?','Yes, absolutely. Every itinerary on our website serves as a curated foundation. Our travel designers can personalize the duration, add specific destinations, upgrade accommodation categories, incorporate private guides, or include special dietary needs to match your rhythm.',2,1,'2026-10-18 19:50:10'),(3,'Payments','What payment methods do you support for package deposits and bookings?','We accept secure digital payments including UPI, Net Banking, Credit/Debit cards via our payment gateway (Razorpay), as well as direct NEFT/RTGS bank transfers to our verified current account. Receipts and booking vouchers are generated promptly upon confirmation.',3,1,'2026-10-18 19:50:10'),(4,'Payments','What is your cancellation and refund policy?','Cancellations made 30 days or more prior to the departure date are eligible for a refund after deducting nominal administrative and supplier non-refundable fees. For detailed terms and timeline breakdowns, please consult our dedicated Cancellation and Refund Policy page.',4,1,'2026-10-18 19:50:10'),(5,'Pilgrimage','How are pilgrimage tours organized, and what special assistance is provided?','Our sacred pilgrimage circuits (such as Varanasi, Ayodhya, and Tamil Nadu Temple trails) are curated with senior-citizen and family comfort in mind. We arrange clean AC transfers, comfortable hotel stays near temple precincts, guidance on darshan timings, assistance with special queue bookings where permitted, and pure vegetarian dining options.',5,1,'2026-10-18 19:50:10'),(6,'Documents','What travel documents do I need for domestic and international tours?','For all domestic travel within India, a valid government-issued photo ID (Aadhaar card, Passport, or Voter ID) is mandatory for hotel check-ins and flight/train boarding. For international journeys (Dubai, Bali, Singapore, etc.), you must hold an Indian Passport with at least 6 months validity from the travel date, along with the appropriate tourist visa or visa-on-arrival approvals.',6,1,'2026-10-18 19:50:10'),(7,'General','Are flights and train tickets included in the package prices?','Unless explicitly indicated in the package inclusions, standard packages cover ground transport, hotel accommodation, breakfasts, and guided sightseeing. Our ticketing desk can seamlessly arrange your domestic or international airfare or train tickets upon request at current airline/railway tariffs.',7,1,'2026-10-18 19:50:10');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_images`
--

DROP TABLE IF EXISTS `gallery_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'destinations',
  `image_url` varchar(255) NOT NULL,
  `alt_text` varchar(200) NOT NULL,
  `location_name` varchar(120) NOT NULL,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_images`
--

LOCK TABLES `gallery_images` WRITE;
/*!40000 ALTER TABLE `gallery_images` DISABLE KEYS */;
INSERT INTO `gallery_images` VALUES (1,'Tea Plantations at Dawn','destinations','https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1000&q=80','Rolling green tea terraces in Munnar covered in morning mist','Munnar, Kerala',1,1,'2026-10-18 19:50:10'),(2,'Backwater Horizon','destinations','https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1000&q=80','Traditional kettuvallam houseboat on Kerala backwaters','Alleppey, Kerala',2,1,'2026-10-18 19:50:10'),(3,'Grandeur of Dravidian Gopuram','pilgrimages','https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1000&q=80','Intricate carvings on ancient Madurai temple gopuram','Madurai, Tamil Nadu',3,1,'2026-10-18 19:50:10'),(4,'Spiritual Twilight at Ganga Ghats','pilgrimages','https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1000&q=80','Lamps and prayer ceremonies by the holy Ganges in Varanasi','Varanasi, Uttar Pradesh',4,1,'2026-10-18 19:50:10'),(5,'Turquoise Lagoon Paradise','nature','https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1000&q=80','Crystal clear waters at Radhanagar Beach','Havelock, Andaman',5,1,'2026-10-18 19:50:10'),(6,'Himalayan Winter Vista','nature','https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1000&q=80','Snow-blanketed peaks and pine trees in Solang Valley','Manali, Himachal Pradesh',6,1,'2026-10-18 19:50:10'),(7,'Burj Khalifa Starlit Skyline','luxury','https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=80','Iconic Burj Khalifa and Dubai Fountain at night','Dubai, UAE',7,1,'2026-10-18 19:50:10'),(8,'Balinese Serenity & Sacred Rice Terraces','destinations','https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=80','Traditional Balinese temple gateway and tropical palm trees','Ubud, Bali',8,1,'2026-10-18 19:50:10'),(9,'Overwater Lagoon Retreat','luxury','https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1000&q=80','Luxury thatched water villas over turquoise lagoon in Maldives','Mal├® Atolls, Maldives',9,1,'2026-10-18 19:50:10');
/*!40000 ALTER TABLE `gallery_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_categories`
--

DROP TABLE IF EXISTS `package_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_svg` text DEFAULT NULL,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_categories`
--

LOCK TABLES `package_categories` WRITE;
/*!40000 ALTER TABLE `package_categories` DISABLE KEYS */;
INSERT INTO `package_categories` VALUES (1,'Domestic Tour Packages','domestic-packages','Immerse in the breathtaking landscapes, hill stations, and coastal serenity of India.','compass',1,1,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(2,'International Tour Packages','international-packages','Seamless journeys across global iconic skylines, tropical islands, and cultural capitals.','globe',2,1,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(3,'Pilgrimage Tours','pilgrimage-tours','Sacred and devotional journeys thoughtfully planned with comfortable transport and revered darshans.','temple',3,1,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(4,'Family Holidays','family-holidays','Relaxed, multi-generational friendly itineraries with curated comfort and engaging activities.','users',4,1,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(5,'Honeymoon Packages','honeymoon-packages','Intimate, romantic escapes with scenic stays, private dinners, and unforgettable memories.','heart',5,1,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(6,'Group Tours','group-tours','Energetic group expeditions, institutional groups, and corporate retreats with coordinated logistics.','briefcase',6,1,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(7,'Customised Holidays','customised-holidays','Completely bespoke travel plans designed around your dates, pace, and specific desires.','sparkles',7,1,'2026-10-18 19:50:10','2026-10-18 19:50:10');
/*!40000 ALTER TABLE `package_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_images`
--

DROP TABLE IF EXISTS `package_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `package_images_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_images`
--

LOCK TABLES `package_images` WRITE;
/*!40000 ALTER TABLE `package_images` DISABLE KEYS */;
INSERT INTO `package_images` VALUES (1,1,'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=900&q=80','Munnar mist-covered tea estate morning',1,'2026-10-18 20:53:32'),(2,1,'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=900&q=80','Traditional Kerala kettuvallam luxury interior',2,'2026-10-18 20:53:32'),(3,1,'https://images.unsplash.com/photo-1590050752117-238cb0fb12b1?auto=format&fit=crop&w=900&q=80','Thekkady Periyar wildlife sanctuary lake',3,'2026-10-18 20:53:32'),(4,2,'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=900&q=80','Madurai Meenakshi temple golden corridor',1,'2026-10-18 20:53:32'),(5,2,'https://images.unsplash.com/photo-1609766857041-ed402ea8069a?auto=format&fit=crop&w=900&q=80','Rameswaram Pamban sea bridge',2,'2026-10-18 20:53:32'),(6,2,'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=900&q=80','Kanyakumari Vivekananda rock memorial at sea',3,'2026-10-18 20:53:32'),(7,3,'https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=900&q=80','Varanasi holy evening Ganga Aarti',1,'2026-10-18 20:53:32'),(8,3,'https://images.unsplash.com/photo-1571536802807-30451e3955d8?auto=format&fit=crop&w=900&q=80','Ganges sunrise wooden boat cruise',2,'2026-10-18 20:53:32'),(9,3,'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=80','Ayodhya Saryu riverfront and temples',3,'2026-10-18 20:53:32'),(10,6,'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=900&q=80','Dubai Burj Khalifa fountain show at night',1,'2026-10-18 20:53:32'),(11,6,'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=900&q=80','Golden Arabian desert dune safari at sunset',2,'2026-10-18 20:53:32'),(12,7,'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=900&q=80','Bali Ubud private pool jungle villa',1,'2026-10-18 20:53:32'),(13,7,'https://images.unsplash.com/photo-1518548419970-58e3b4079ab2?auto=format&fit=crop&w=900&q=80','Tegallalang green stepped rice terraces',2,'2026-10-18 20:53:32'),(14,8,'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=900&q=80','Maldives luxury overwater walkway and turquoise lagoon',1,'2026-10-18 20:53:32'),(15,8,'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80','Maldives soft white sand beach and coral waters',2,'2026-10-18 20:53:32');
/*!40000 ALTER TABLE `package_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `package_itineraries`
--

DROP TABLE IF EXISTS `package_itineraries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_itineraries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `day_number` tinyint(3) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `meals_included` varchar(100) DEFAULT NULL,
  `stay_location` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_itinerary_pkg` (`package_id`,`day_number`),
  CONSTRAINT `package_itineraries_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_itineraries`
--

LOCK TABLES `package_itineraries` WRITE;
/*!40000 ALTER TABLE `package_itineraries` DISABLE KEYS */;
INSERT INTO `package_itineraries` VALUES (1,1,1,'Arrival in Kochi & Scenic Drive to Munnar Hills','Upon arrival at Kochi International Airport or Railway Station, our chauffeur greets you. Embark on a picturesque 4-hour drive up into the Western Ghats passing through spice-scented valleys, Cheeyappara Waterfalls, and Valara Waterfalls. Check into your hillside resort in Munnar and relax amidst cool mountain air.','Dinner','Munnar Resort','2026-10-18 19:50:10'),(2,1,2,'Munnar Tea Estates & Eravikulam National Park','Morning visit to Eravikulam National Park, home to the endangered Nilgiri Tahr. Tour the Tata Tea Museum to observe the art of tea crafting, followed by visits to Mattupetty Dam, Echo Point, and the lush Rose Garden. Evening free to stroll in Munnar town market.','Breakfast, Dinner','Munnar Resort','2026-10-18 19:50:10'),(3,1,3,'Munnar to Thekkady: Spice Trails & Lake Periyar','Scenic drive to Thekkady through rolling pepper and cardamom plantations. Afternoon guided walk through a lush organic spice plantation learning about vanilla, cinnamon, and cloves. Optional evening Kathakali classical dance and Kalaripayattu martial arts show.','Breakfast, Dinner','Thekkady Hotel','2026-10-18 19:50:10'),(4,1,4,'Thekkady to Alleppey: Boarding the Private Houseboat','Drive down to Alleppey backwaters. Board your private traditionally crafted luxury houseboat at 12:00 PM. Glide silently past swaying palms, Chinese fishing nets, and village life along Vembanad Lake. Savor authentic freshly prepared Kerala cuisine on board with overnight cruise stay.','Breakfast, Lunch, Dinner','Alleppey Houseboat','2026-10-18 19:50:10'),(5,1,5,'Alleppey to Kochi Departure','Enjoy sunrise over calm backwaters with a traditional breakfast. Disembark at 9:00 AM. Travel back to Kochi with a stop at Fort Kochi to see colonial heritage and Chinese fishing nets before dropping you at Kochi Airport/Railway Station for your flight home.','Breakfast','Departure','2026-10-18 19:50:10'),(6,2,1,'Arrival in Madurai & Meenakshi Amman Darshan','Arrive at Madurai, the soul of Tamil Nadu. Check into your hotel. In the evening, visit the world-renowned Meenakshi Sundareswarar Temple to witness the awe-inspiring sculpted gopurams and the sacred night ceremony (Palliarai Puja).','Dinner','Madurai Hotel','2026-10-18 19:50:10'),(7,2,2,'Madurai Heritage & Transit to Holy Rameswaram','Morning visit to Thirumalai Nayakar Mahal and Gandhi Memorial Museum. Drive to the holy island of Rameswaram via the engineering marvel Pamban Bridge. Evening darshan at Ramanathaswamy Temple and walk along the calm Agnitheertham seashore.','Breakfast, Dinner','Rameswaram Hotel','2026-10-18 19:50:10'),(8,2,3,'Sacred 22 Theertham Bath & Dhanushkodi Excursion','Early morning holy bath across the 22 sacred wells inside the temple complex followed by special darshan of the revered Spatika Lingam. Afternoon trip to ghost town Dhanushkodi and Ram Setu viewpoint. Evening spiritual satsang and rest.','Breakfast, Dinner','Rameswaram Hotel','2026-10-18 19:50:10'),(9,2,4,'Rameswaram to Kanyakumari: Land\'s End','Drive from Rameswaram to Kanyakumari, the southernmost tip of peninsular India. Check into hotel. Visit the iconic Vivekananda Rock Memorial and Thiruvalluvar Statue via ferry. Watch the breathtaking sunset over the confluence of three oceans.','Breakfast, Dinner','Kanyakumari Hotel','2026-10-18 19:50:10'),(10,2,5,'Kanyakumari Sunrise & Suchindram Temple','Witness the majestic sunrise over the Indian Ocean. Visit the sacred Bhagavathy Amman Temple and the historic Suchindram Thanumalayan Temple with its musical stone pillars. Afternoon visit to Padmanabhapuram Wooden Palace.','Breakfast, Dinner','Kanyakumari Hotel','2026-10-18 19:50:10'),(11,2,6,'Kanyakumari to Trivandrum Departure','After breakfast, drive to Thiruvananthapuram (Trivandrum). Visit the sacred Sree Padmanabhaswamy Temple for blessings. Transfer to Trivandrum Airport or Railway Station with divine memories.','Breakfast','Departure','2026-10-18 19:50:10'),(12,3,1,'Arrival in Varanasi & Evening Maha Ganga Aarti','Arrive at Lal Bahadur Shastri Airport or Varanasi Junction. Warm welcome by our representative. Check into hotel. At dusk, head to Dashashwamedh Ghat for an exclusive boat view of the world-famous evening Maha Ganga Aarti.','Dinner','Varanasi Hotel','2026-10-18 19:50:10'),(13,3,2,'Sacred Kashi Vishwanath Darshan & Sarnath','Early morning dawn boat ride along the historic ghats. Visit Kashi Vishwanath Jyotirlinga Temple through the grand Vishwanath Corridor, Annapurna Mandir, and Kal Bhairav Temple. Afternoon excursion to Sarnath, where Lord Buddha gave his first sermon.','Breakfast, Dinner','Varanasi Hotel','2026-10-18 19:50:10'),(14,3,3,'Varanasi to Ayodhya: City of Lord Ram','After breakfast, take a comfortable 4-hour private drive to the holy city of Ayodhya. Check in to your hotel. Visit the tranquil banks of Saryu River and participate in the melodious evening Saryu Aarti followed by a visit to Ram Ki Paidi.','Breakfast, Dinner','Ayodhya Hotel','2026-10-18 19:50:10'),(15,3,4,'Shri Ram Janmabhoomi Darshan & Historic Temples','Special morning darshan at the magnificent Shri Ram Janmabhoomi Temple to view Ram Lalla. Visit the revered Hanuman Garhi temple and Kanak Bhavan. Spend the afternoon exploring Ayodhya\'s historic streets and heritage.','Breakfast, Dinner','Ayodhya Hotel','2026-10-18 19:50:10'),(16,3,5,'Ayodhya Departure or Transit to Varanasi/Lucknow','Morning prayer and breakfast. Departure transfer to Ayodhya Airport/Railway Station or drive to Lucknow/Varanasi airport for your onward journey home.','Breakfast','Departure','2026-10-18 19:50:10');
/*!40000 ALTER TABLE `package_itineraries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `destination_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `duration_days` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `duration_nights` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `short_description` varchar(500) NOT NULL,
  `overview` text NOT NULL,
  `starting_price` decimal(10,2) DEFAULT NULL,
  `is_price_on_request` tinyint(1) NOT NULL DEFAULT 0,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `seasonal_info` varchar(200) DEFAULT NULL,
  `featured_image` varchar(255) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_pilgrimage` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `meta_title` varchar(180) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `destination_id` (`destination_id`),
  KEY `category_id` (`category_id`),
  KEY `idx_pkg_pub` (`is_published`),
  KEY `idx_pkg_featured` (`is_featured`),
  KEY `idx_pkg_pilgrim` (`is_pilgrimage`),
  CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `packages_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `package_categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packages`
--

LOCK TABLES `packages` WRITE;
/*!40000 ALTER TABLE `packages` DISABLE KEYS */;
INSERT INTO `packages` VALUES (1,1,1,'Kerala Serenade: Munnar Hills & Alleppey Waters','kerala-serenade-munnar-alleppey','Mist-covered tea gardens and a private luxury backwater cruise',5,4,'Escape into Kerala\'s lush paradise: 2 nights in romantic Munnar tea hills, 1 night in spice-scented Thekkady, and 1 unforgettable night aboard a private Alleppey houseboat.','Experience the soul of God\'s Own Country. Traverse rolling emerald hills, breathe fresh mountain mist among lush tea gardens in Munnar, take an elephant safari and spice plantation tour in Thekkady, and culminate with a serene private cruise across the tranquil waters of Alleppey backwaters on an authentic Kerala kettuvallam.',24500.00,0,'INR','Accommodation in 4-star handpicked resorts, Daily buffet breakfast & dinner, Exclusive 1-night Alleppey luxury houseboat with all meals, Private AC sedan transport with experienced chauffeur, Guided spice plantation tour, All toll taxes and driver allowances','Airfare/train tickets to Kochi, Personal expenses and telephone charges, Optional Ayurvedic spa therapies, Entry tickets to monuments not in itinerary','Best experienced from September to April','https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80','Traditional houseboat gliding through palm-fringed Kerala backwaters',1,0,1,1,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(2,2,3,'Tamil Nadu Divine Yatra: Madurai, Rameswaram & Kanyakumari','tamil-nadu-divine-yatra','A soul-enriching South Indian pilgrimage through sacred corridors',6,5,'Walk through sacred gopurams and sacred ocean ghats. Visit Madurai Meenakshi, Rameswaram Ramanathaswamy 22 sacred theerthams, and Kanyakumari confluence.','A deeply revered spiritual circuit covering the architectural marvels of Dravidian temple culture. Experience the divine vibration of the Madurai Meenakshi Amman Temple, proceed across the Pamban Bridge to the holy island of Rameswaram for holy bath in the 22 Kundas, and conclude at Kanyakumari where three oceans meet with sunset and sunrise views.',22000.00,0,'INR','Accommodation in curated comfortable hotels near temple precincts, Daily breakfast and traditional vegetarian dinners, Dedicated AC transport with courteous driver, Assistance for special darshan queues where permitted, Pamban bridge crossing and sightseeing','Flight or train fare to Madurai, Special VIP Puja and archana receipts, Personal laundry and tips, Any religious offerings','Comfortable climate from October to March','https://images.unsplash.com/photo-1582510003544-4d00b7f74220?auto=format&fit=crop&w=1200&q=80','Soaring Dravidian gopuram with intricate sculptures in Madurai temple',1,1,1,2,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(3,3,3,'Kashi & Ayodhya Sacred Circuit','kashi-ayodhya-sacred-circuit','The eternal spiritual journey of northern India',5,4,'Darshan at Kashi Vishwanath, evening Ganga Aarti at Dashashwamedh Ghat, Sarnath Stupa, and the holy city of Ayodhya including Shri Ram Mandir.','Immerse in timeless devotion across two of India\'s most holy cities. Experience the divine dawn boat cruise on the sacred Ganges, witness the mesmerizing evening Maha Aarti, seek blessings at the restored Kashi Vishwanath Dham, and travel in comfort to Ayodhya for darshan of Ram Lalla at the newly consecrated Ram Janmabhoomi Mandir.',26800.00,0,'INR','4 nights hotel stay with pure vegetarian meals, Private AC vehicle for transfers and Varanasi-Ayodhya transit, Exclusive private sunrise boat ride on the holy Ganges, Dedicated local pilgrimage coordinator in Kashi, All applicable parking and vehicle taxes','Airfare/train fare to Varanasi, Individual temple archana and offerings, Sarnath museum entry tickets, Personal expenses','Ideal between October and March','https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=1200&q=80','Evening Ganga Aarti ceremony illuminated by brass lamps in Varanasi',1,1,1,3,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(4,5,1,'Andaman Island Haven: Port Blair & Havelock','andaman-island-haven','Turquoise waters, coral reef adventures, and tropical ease',6,5,'Unwind in pure island bliss. Explore historical Cellular Jail, cruise on luxury catamarans to Havelock Island, and sink your toes into Radhanagar Beach.','Discover India\'s most breathtaking island getaway. Relax on the world-renowned Radhanagar Beach, snorkel among vibrant coral reefs at Elephant Beach, explore the historic Cellular Jail in Port Blair, and enjoy smooth luxury catamaran cruises between pristine tropical islands.',38500.00,0,'INR','5 nights accommodation in beachfront & premium resorts, Daily buffet breakfast, Makruzz or Green Ocean luxury catamaran tickets between islands, Private AC vehicles for all transfers and sightseeing, Snorkeling session at Elephant Beach with guide, Cellular Jail light and sound show entry','Airfare to Port Blair, Scuba diving or sea karting optional upgrades, Lunch and beverages not mentioned, Camera and video permits','Best time to visit is October to May','https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=1200&q=80','Radhanagar beach turquoise ocean and white sands in Havelock Island',1,0,1,4,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(5,7,4,'Himalayan Wonder: Kullu, Manali & Solang Valley','himalayan-wonder-manali','Pine-scented mountain air, rushing rivers, and snow peaks',5,4,'A rejuvenating alpine retreat featuring Solang Valley adventure, Hadimba Devi temple, Vashisht hot springs, and scenic drive through the Atal Tunnel to Sissu.','Head to the high Himalayas of Himachal Pradesh. Soak in panoramic vistas of snow-draped mountain peaks, stroll through fragrant pine forests, thrill in adventure activities in Solang Valley, and travel through the engineering marvel of Atal Tunnel to witness the raw beauty of Lahaul Valley.',19800.00,0,'INR','4 nights stay in hillside view resorts, Daily breakfast and hearty dinner, Exclusive AC/Heater private vehicle throughout the trip, Excursion to Solang Valley and Atal Tunnel (subject to weather), Sightseeing of Manali town and temples','Flight/train tickets to Chandigarh or Delhi, Adventure sports charges (paragliding, skiing, ATV), Rohtang Pass NGT permit fee if opted, Personal warm clothing rentals','Snow season: Dec-Feb; pleasant summer: Mar-Jun','https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1200&q=80','Manali valley covered in snow with majestic pine trees',1,0,1,5,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(6,9,2,'Gleaming Dubai & Desert Splendour','gleaming-dubai-desert-splendour','The ultimate blend of futuristic icons and Arabian desert magic',5,4,'Explore Burj Khalifa, evening desert safari with dune bashing & barbecue, Dubai Marina dhow cruise with dinner, and luxury city highlights.','Step into a world of architectural wonder and desert allure. Ascend to the 124th floor of the iconic Burj Khalifa, ride through golden desert dunes in a 4x4 with a starlit Arabian barbecue dinner and tanoura dance, cruise Dubai Marina on a traditional glass-enclosed dhow, and enjoy duty-free shopping at the world\'s grandest malls.',46500.00,0,'INR','4 nights accommodation in 4-star Dubai city hotel, Daily buffet breakfast, Desert Safari with dune bashing, BBQ dinner, and live shows, Burj Khalifa 124th floor non-peak entry ticket, Marina Dhow Cruise with international buffet dinner, Half-day Dubai panoramic city tour, Airport transfers in private AC vehicle','International airfare and UAE tourist visa, Tourism Dirham fee paid directly to hotel, Lunch and personal shopping expenses, Travel insurance','Best visited between November and April','https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1200&q=80','Burj Khalifa and Dubai downtown skyline gleaming in twilight',1,0,1,6,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(7,10,5,'Romantic Bali: Ubud Rainforest & Cliffside Ocean','romantic-bali-ubud-ocean','Intimate tropical sanctuary, private pool villa, and sunset temples',6,5,'The quintessential honeymoon getaway. Private pool villa in Ubud, rice terrace swing, Tanah Lot ocean temple sunset, and Uluwatu Kecak performance.','Designed for couples and seekers of tropical tranquility. Wake up to lush tropical greenery in an Ubud private pool villa, swing over emerald Tegallalang rice terraces, explore volcanic Mount Batur vistas, and witness unforgettable golden sunsets over the Indian Ocean from dramatic cliffside temples.',52000.00,0,'INR','3 nights in Ubud private pool villa + 2 nights in Seminyak luxury beach resort, Daily breakfast, 1 romantic candlelight dinner with wine, Tegallalang rice terrace & giant swing tickets, Tanah Lot and Uluwatu temple guided excursions, Private AC car with English-speaking chauffeur-guide, Return airport transfers','International flight tickets, Indonesia visa on arrival, Lunches and extra beverage expenses, Water sports fees','Ideal from April to October','https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=80','Serene Bali infinity pool looking out into lush jungle canopy',1,0,1,7,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(8,14,5,'Maldives Azure Dream: Overwater Luxury Stay','maldives-azure-dream','Infinite shades of blue, private overwater villa, and coral reef magic',4,3,'Escape to a private luxury island resort. Stay suspended over turquoise lagoons, wake up to panoramic ocean vistas, and enjoy all-inclusive dining.','Experience the pinnacle of tropical indulgence. Live directly above the crystal-clear turquoise waters of the Indian Ocean in a thatched overwater bungalow. Snorkel directly from your private sun deck into coral gardens alive with colorful marine life and savor gourmet meals with your toes in soft white sand.',98000.00,0,'INR','3 nights in premium Overwater Lagoon Villa, Full board meal plan (Breakfast, Lunch, Dinner at resort), Return speedboat transfers from Velana International Airport, Complimentary snorkeling gear and non-motorized water sports, Welcome bottle of sparkling wine and fruit basket, All Maldives green tax and service charges','International flight tickets to Mal├®, Spa treatments and scuba diving excursions, Alcoholic beverages unless opted for all-inclusive upgrade, Gratuities','Spectacular weather from November to April','https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1200&q=80','Scenic overwater bungalows leading into azure blue lagoon in Maldives',1,0,1,8,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(9,6,7,'Lakshadweep Coral Lagoon Escape','lakshadweep-coral-lagoon-escape','Untouched coral atolls, crystal waters, and tranquil seclusion',5,4,'Discover India\'s secret island sanctuary. Unwind on Agatti and Bangaram islands with guided lagoon kayaking, coral reef tours, and island permit coordination.','Breathe the purest ocean air in Lakshadweep. Our curated package handles all mandatory island entry permits, comfortable beachfront cottage stays on Agatti island, boat excursions to the uninhabited paradise of Bangaram atoll, and personalized water activities.',32000.00,0,'INR','4 nights stay in beachfront cottages in Agatti, All meals (Breakfast, Lunch, Evening Tea, Dinner), Lakshadweep entry permit processing and paper clearances, Airport pickup and drop by boat/jeep, Glass-bottom boat ride and guided snorkeling','Airfare from Kochi to Agatti and return, Scuba diving certification charges, Personal shopping or tipping','Peak season October to April','https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80','Pristine white sand shoreline and turquoise lagoon in Lakshadweep',0,0,1,9,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10'),(10,11,4,'Vibrant Singapore City & Sentosa Discovery','vibrant-singapore-sentosa','World-class modern wonder, futuristic gardens, and theme park thrills',5,4,'Marvel at Gardens by the Bay, take the cable car into Sentosa Island, enjoy Universal Studios Singapore, and take in Marina Bay panoramic views.','A seamless, high-comfort itinerary tailored for families and modern explorers. Experience the world\'s best airport at Jewel Changi, stand beneath the iconic Supertrees of Gardens by the Bay, experience world-class rides at Universal Studios, and cruise Singapore River by twilight.',54000.00,0,'INR','4 nights stay in central Singapore 4-star hotel, Daily breakfast, Gardens by the Bay (Flower Dome & Cloud Forest) entry, Universal Studios Singapore one-day pass, Sentosa cable car & Island discovery, Return airport transfers and city tour in private AC coach','International flights, Singapore tourist visa, Meals other than breakfast, Personal expenses','Pleasant all year round','https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80','Gardens by the bay illuminated Supertrees in Singapore',0,0,1,10,NULL,NULL,'2026-10-18 19:50:10','2026-10-18 19:50:10');
/*!40000 ALTER TABLE `packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` int(10) unsigned NOT NULL,
  `booking_reference` varchar(40) NOT NULL,
  `payment_gateway` varchar(50) NOT NULL DEFAULT 'razorpay',
  `gateway_order_id` varchar(100) NOT NULL,
  `gateway_payment_id` varchar(100) DEFAULT NULL,
  `gateway_signature` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `status` enum('pending','authorized','captured','failed','refunded') NOT NULL DEFAULT 'pending',
  `raw_response` text DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `idx_pay_order` (`gateway_order_id`),
  KEY `idx_pay_status` (`status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'site_name','MIDHILA VOYAGES','general','2026-10-18 19:50:10','2026-10-18 22:28:30'),(2,'site_tagline','The World Is Waiting. Let\'s Make It Yours.','general','2026-10-18 19:50:10','2026-10-18 22:28:30'),(3,'site_description','Bespoke domestic, international, and sacred pilgrimage journeys crafted with care, integrity, and personalised luxury.','general','2026-10-18 19:50:10','2026-10-18 19:50:10'),(4,'primary_phone','+91 12345 67890','contact','2026-10-18 19:50:10','2026-10-18 22:28:30'),(5,'secondary_phone','+91 12345 67890','contact','2026-10-18 19:50:10','2026-10-18 22:28:30'),(6,'whatsapp_number','+911234567890','contact','2026-10-18 19:50:10','2026-10-18 22:28:30'),(7,'primary_email','journeys@midhilavoyages.com','contact','2026-10-18 19:50:10','2026-10-18 22:28:30'),(8,'office_address','Midhila Voyages, Travel & Tourism Hub, MG Road, Kochi, Kerala - 682016, India','contact','2026-10-18 19:50:10','2026-10-18 22:28:30'),(9,'working_hours','Monday ÔÇô Saturday: 9:00 AM ÔÇô 7:30 PM (IST)','contact','2026-10-18 19:50:10','2026-10-18 22:28:30'),(10,'facebook_url','https://facebook.com/midhilavoyages','social','2026-10-18 19:50:10','2026-10-18 22:28:30'),(11,'instagram_url','https://instagram.com/midhilavoyages','social','2026-10-18 19:50:10','2026-10-18 22:28:30'),(12,'youtube_url','https://youtube.com/@midhilavoyages','social','2026-10-18 19:50:10','2026-10-18 22:28:30'),(13,'currency_symbol','Ôé╣','finance','2026-10-18 19:50:10','2026-10-18 19:50:10'),(14,'currency_code','INR','finance','2026-10-18 19:50:10','2026-10-18 19:50:10'),(15,'razorpay_key_id','rzp_test_midhila_demo','payment','2026-10-18 19:50:10','2026-10-18 22:28:30'),(16,'razorpay_key_secret','rzp_secret_demo_123456789','payment','2026-10-18 19:50:10','2026-10-18 22:28:30'),(17,'payment_mode','test','payment','2026-10-18 19:50:10','2026-10-18 22:28:30'),(18,'hero_headline','The World Is Waiting. Let\'s Make It Yours.','hero','2026-10-18 19:50:10','2026-10-18 22:28:30'),(19,'hero_subheading','Discover handpicked escapes, immersive cultural expeditions, and sacred pilgrimage trails tailored to your rhythm.','hero','2026-10-18 19:50:10','2026-10-18 22:28:30'),(20,'hero_primary_cta_text','Plan Your Journey','hero','2026-10-18 19:50:10','2026-10-18 19:50:10'),(21,'hero_secondary_cta_text','Explore Destinations','hero','2026-10-18 19:50:10','2026-10-18 19:50:10');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_name` varchar(120) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `tour_taken` varchar(150) DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `feedback` text NOT NULL,
  `travel_date` varchar(50) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Narayanan K. & Family','Kottayam, Kerala','Tamil Nadu Divine Yatra (Madurai - Rameswaram)',5,'Midhila Voyages arranged our parents\' pilgrimage with utmost care and devotion. The vehicle was immaculately clean, the driver was courteous and knowledgeable, and all hotel accommodations were conveniently close to the temples. Truly a peaceful experience.','February 2026',1,1,1,'2026-10-18 19:50:10'),(2,'Dr. Anand & Meera','Bengaluru, Karnataka','Kerala Serenade (Munnar & Alleppey)',5,'The private houseboat experience in Alleppey arranged by Midhila Voyages exceeded our expectations. The chef served delicious authentic Kerala meals, and the tea estate resort in Munnar had breathtaking misty valley views. Highly recommended for couples!','January 2026',1,1,2,'2026-10-18 19:50:10'),(3,'Rajesh Varma & Group','Chennai, Tamil Nadu','Kashi & Ayodhya Sacred Circuit',5,'Coordinating a 12-member group tour to Varanasi and Ayodhya could have been challenging, but Midhila Voyages handled every detail seamlessly ÔÇö from airport pickups to sunrise boats on the Ganges and smooth darshan at Ram Janmabhoomi.','March 2026',1,1,3,'2026-10-18 19:50:10');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-18 23:17:08
