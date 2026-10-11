-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 04, 2026 at 01:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `habelles_rent`
--

-- --------------------------------------------------------

--
-- Table structure for table `agreement_signatures`
--

DROP TABLE IF EXISTS `agreement_signatures`;
CREATE TABLE `agreement_signatures` (
  `id` int(10) UNSIGNED NOT NULL,
  `agreement_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `signature_type` enum('accept','reject') NOT NULL DEFAULT 'accept',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `signed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `checksum` varchar(64) DEFAULT NULL COMMENT 'SHA256 of agreement content'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

DROP TABLE IF EXISTS `amenities`;
CREATE TABLE `amenities` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL COMMENT 'indoor, outdoor, utility, security',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `slug`, `icon`, `category`, `sort_order`, `active`) VALUES
(1, 'Electricity', 'electricity', 'fa-bolt', 'utility', 1, 1),
(2, 'Water Supply', 'water-supply', 'fa-tint', 'utility', 2, 1),
(3, 'Internet / WiFi', 'internet-wifi', 'fa-wifi', 'utility', 3, 1),
(4, 'TV Aerial', 'tv-aerial', 'fa-tv', 'utility', 4, 1),
(5, 'Generator', 'generator', 'fa-plug', 'utility', 5, 1),
(6, 'Solar Power', 'solar-power', 'fa-sun', 'utility', 6, 1),
(7, 'Air Conditioning', 'air-conditioning', 'fa-snowflake', 'indoor', 10, 1),
(8, 'Ceiling Fan', 'ceiling-fan', 'fa-fan', 'indoor', 11, 1),
(9, 'Wardrobe', 'wardrobe', 'fa-archive', 'indoor', 12, 1),
(10, 'Built-in Shelves', 'builtin-shelves', 'fa-th-large', 'indoor', 13, 1),
(11, 'Kitchen Cabinets', 'kitchen-cabinets', 'fa-box', 'indoor', 14, 1),
(12, 'Refrigerator', 'refrigerator', 'fa-cube', 'indoor', 15, 1),
(13, 'Washing Machine', 'washing-machine', 'fa-sync', 'indoor', 16, 1),
(14, 'Water Heater', 'water-heater', 'fa-thermometer-half', 'indoor', 17, 1),
(15, 'Compound', 'compound', 'fa-tree', 'outdoor', 20, 1),
(16, 'Garden', 'garden', 'fa-seedling', 'outdoor', 21, 1),
(17, 'Swimming Pool', 'swimming-pool', 'fa-swimming-pool', 'outdoor', 22, 1),
(18, 'Parking Space', 'parking-space', 'fa-car', 'outdoor', 23, 1),
(19, 'Barbeque Area', 'barbeque', 'fa-fire', 'outdoor', 24, 1),
(20, 'Rooftop', 'rooftop', 'fa-building', 'outdoor', 25, 1),
(21, '24hr Security', '24hr-security', 'fa-shield-alt', 'security', 30, 1),
(22, 'CCTV', 'cctv', 'fa-video', 'security', 31, 1),
(23, 'Electric Fence', 'electric-fence', 'fa-bolt', 'security', 32, 1),
(24, 'Gated Community', 'gated-community', 'fa-lock', 'security', 33, 1),
(25, 'Intercom', 'intercom', 'fa-phone', 'security', 34, 1),
(26, 'Elevator / Lift', 'elevator', 'fa-elevator', 'access', 40, 1),
(27, 'Wheelchair Access', 'wheelchair-access', 'fa-wheelchair', 'access', 41, 1),
(28, 'Tarred Road Access', 'tarred-road', 'fa-road', 'access', 42, 1);

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
CREATE TABLE `areas` (
  `id` int(10) UNSIGNED NOT NULL,
  `city_id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `city_id`, `name`, `slug`, `municipality`, `active`) VALUES
(1, 1, 'East Legon', 'east-legon', 'La Nkwantanang-Madina', 1),
(2, 1, 'Adabraka', 'adabraka', 'Accra Metropolitan', 1),
(3, 1, 'Cantonments', 'cantonments', 'Accra Metropolitan', 1),
(4, 1, 'Airport Res.', 'airport-res', 'Accra Metropolitan', 1),
(5, 1, 'Osu', 'osu', 'Accra Metropolitan', 1),
(6, 1, 'Labadi', 'labadi', 'La Dade-Kotopon', 1),
(7, 1, 'Dansoman', 'dansoman', 'Ablekuma West', 1),
(8, 1, 'Achimota', 'achimota', 'Okaikwei North', 1),
(9, 1, 'Dzorwulu', 'dzorwulu', 'Accra Metropolitan', 1),
(10, 1, 'Labone', 'labone', 'Accra Metropolitan', 1),
(11, 1, 'Spintex', 'spintex', 'Tema West', 1),
(12, 1, 'Haatso', 'haatso', 'Ga East', 1),
(13, 1, 'Dome', 'dome', 'Ga East', 1),
(14, 1, 'Madina', 'madina-area', 'La Nkwantanang-Madina', 1),
(15, 6, 'Adum', 'adum', 'Kumasi Metropolitan', 1),
(16, 6, 'Nhyiaeso', 'nhyiaeso', 'Kumasi Metropolitan', 1),
(17, 6, 'Bantama', 'bantama', 'Kumasi Metropolitan', 1),
(18, 6, 'Asokwa', 'asokwa', 'Asokwa', 1),
(19, 6, 'Suame', 'suame', 'Suame', 1),
(20, 8, 'Effia', 'effia', 'Effia-Kwesimintsim', 1),
(21, 8, 'Market Circle', 'market-circle', 'Sekondi-Takoradi', 1),
(22, 8, 'Anaji', 'anaji', 'Effia-Kwesimintsim', 1),
(23, 8, 'Fijai', 'fijai', 'Sekondi-Takoradi', 1),
(24, 8, 'Agona Nkwanta', 'agona-nkwanta', 'Ahanta West', 1);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `related_id` int(10) UNSIGNED DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `author_id` int(10) UNSIGNED NOT NULL,
  `category` varchar(80) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `author_id`, `category`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `tags`, `status`, `published_at`, `view_count`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Renter Tips', '5 Things to Check Before Visiting a Property', '5-things-to-check-before-visiting-a-property', 'Save time and money by reviewing these key details online before travelling to inspect a property.', '<p>With a detailed online property profile, you can verify many things before leaving your home...</p>', NULL, NULL, 'published', '2026-10-01 20:29:07', 1, NULL, NULL, '2026-10-01 20:29:07', '2026-10-04 10:24:43'),
(2, 1, 'Landlord Advice', 'Why Your Property Description Must Include Dimensions', 'why-property-description-must-include-dimensions', 'Renters increasingly want to know exact room dimensions before visiting. Here is why measurements matter.', '<p>One of the most common complaints from renters is arriving at a property and finding it smaller than expected...</p>', NULL, NULL, 'published', '2026-10-01 20:29:07', 0, NULL, NULL, '2026-10-01 20:29:07', '2026-10-01 20:29:07'),
(3, 1, 'Platform News', 'Understanding Road Accessibility: The Hidden Factor in Property Search', 'understanding-road-accessibility-property-search', 'A property 5 minutes from the main road may take 30 minutes to reach. Learn how we present accessibility information.', '<p>Road accessibility is one of the most overlooked factors in accommodation search...</p>', NULL, NULL, 'published', '2026-10-01 20:29:07', 1, NULL, NULL, '2026-10-01 20:29:07', '2026-10-04 10:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `ceiling_types`
--

DROP TABLE IF EXISTS `ceiling_types`;
CREATE TABLE `ceiling_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ceiling_types`
--

INSERT INTO `ceiling_types` (`id`, `name`, `active`) VALUES
(1, 'Plywood', 1),
(2, 'Plastic', 1),
(3, 'Wooden T&G', 1),
(4, 'POP (Plaster of Paris)', 1),
(5, 'Concrete', 1),
(6, 'None / Open', 1),
(7, 'Other', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `region_id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `region_id`, `name`, `slug`, `active`) VALUES
(1, 1, 'Accra', 'accra', 1),
(2, 1, 'Tema', 'tema', 1),
(3, 1, 'Kasoa', 'kasoa', 1),
(4, 1, 'Ashaiman', 'ashaiman', 1),
(5, 1, 'Madina', 'madina', 1),
(6, 2, 'Kumasi', 'kumasi', 1),
(7, 2, 'Obuasi', 'obuasi', 1),
(8, 3, 'Takoradi', 'takoradi', 1),
(9, 3, 'Sekondi', 'sekondi', 1),
(10, 3, 'Tarkwa', 'tarkwa', 1),
(11, 4, 'Cape Coast', 'cape-coast', 1),
(12, 4, 'Winneba', 'winneba', 1),
(13, 5, 'Koforidua', 'koforidua', 1),
(14, 5, 'Nkawkaw', 'nkawkaw', 1),
(15, 7, 'Tamale', 'tamale', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cms_pages`
--

DROP TABLE IF EXISTS `cms_pages`;
CREATE TABLE `cms_pages` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `status` enum('published','draft') NOT NULL DEFAULT 'draft',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cms_pages`
--

INSERT INTO `cms_pages` (`id`, `slug`, `title`, `content`, `meta_title`, `meta_description`, `status`, `sort_order`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'home', 'Homepage', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(2, 'about', 'About Us', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(3, 'how-it-works', 'How It Works', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(4, 'for-landlords', 'For Landlords', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(5, 'for-renters', 'For Renters', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(6, 'terms', 'Terms and Conditions', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(7, 'privacy', 'Privacy Policy', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(8, 'viewing-policy', 'Viewing Policy', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(9, 'agency-agreement', 'Agency Agreement Template', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(10, 'fees', 'Fees & Pricing', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(11, 'help', 'Help Centre', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06'),
(12, 'contact', 'Contact Us', NULL, NULL, NULL, 'published', 0, NULL, '2026-10-01 20:29:06', '2026-10-01 20:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
CREATE TABLE `commissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `landlord_id` int(10) UNSIGNED NOT NULL,
  `renter_id` int(10) UNSIGNED DEFAULT NULL,
  `total_rent` decimal(12,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL COMMENT 'percentage',
  `commission_amount` decimal(12,2) NOT NULL,
  `landlord_portion_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `landlord_portion` decimal(12,2) NOT NULL DEFAULT 0.00,
  `renter_portion_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `renter_portion` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','waived','disputed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('general','landlord_inquiry','renter_inquiry','complaint','support') NOT NULL DEFAULT 'general',
  `status` enum('new','read','replied','closed') NOT NULL DEFAULT 'new',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
CREATE TABLE `email_verifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(100) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `category` enum('general','landlord','renter','payment','verification','viewing') NOT NULL DEFAULT 'general',
  `question` text NOT NULL,
  `answer` longtext NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `category`, `question`, `answer`, `sort_order`, `active`, `created_at`) VALUES
(1, 'general', 'What is RentalWebsite?', 'RentalWebsite is a property information and viewing management platform designed to help renters find accommodation they can learn about before physically visiting. We provide detailed property information, verified measurements, accessibility details, and professional photos so you can make an informed decision before spending time and money on travel.', 1, 1, '2026-10-01 20:29:07'),
(2, 'general', 'Is it free to use?', 'Registration is free for renters. Browsing and searching properties is completely free. A small viewing fee is charged only when a renter requests a physical inspection of a property.', 2, 1, '2026-10-01 20:29:07'),
(3, 'renter', 'How do I search for properties?', 'Use the search bar on the homepage. You can filter by location, property type, price range, number of bedrooms, amenities, road accessibility, and many more criteria. The advanced search lets you combine multiple filters at once.', 1, 1, '2026-10-01 20:29:07'),
(4, 'renter', 'What is a viewing fee?', 'A viewing fee is a small charge that covers the cost of coordinating and supervising a physical property inspection. It is paid before the viewing is confirmed. This ensures that only seriously interested renters visit properties, reducing disturbance to landlords and current occupants.', 2, 1, '2026-10-01 20:29:07'),
(5, 'renter', 'Can I compare properties?', 'Yes. Add any properties you are interested in to your comparison list and view them side by side, comparing rent, dimensions, amenities, accessibility, and other details.', 3, 1, '2026-10-01 20:29:07'),
(6, 'landlord', 'How do I list my property?', 'Register as a landlord, complete your profile, then click \"Add Property\" from your dashboard. Fill in all required information about your property. Once submitted, our team will review and, if required, physically verify the property before it is published.', 1, 1, '2026-10-01 20:29:07'),
(7, 'landlord', 'How long does verification take?', 'Verification typically takes 3–7 working days after submission. Properties requiring physical inspection may take slightly longer depending on your location.', 2, 1, '2026-10-01 20:29:07'),
(8, 'landlord', 'What is the agency commission?', 'The agency commission is a percentage of the agreed rent. This is configurable and will be clearly stated in your agency agreement before you sign. Commission rates can be viewed in our Fees & Pricing page.', 3, 1, '2026-10-01 20:29:07'),
(9, 'verification', 'What does property verification mean?', 'Property verification means our team has reviewed and, in most cases, physically inspected the property to confirm that all information — including dimensions, photos, amenities, accessibility, and utilities — is accurate. Verified properties receive a verification badge.', 1, 1, '2026-10-01 20:29:07'),
(10, 'payment', 'What payment methods are accepted?', 'We accept Mobile Money (MTN, Vodafone, AirtelTigo), bank card, and bank transfer. Payment infrastructure for additional providers can be added. All transactions are recorded and receipts are sent by email.', 1, 1, '2026-10-01 20:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `flooring_types`
--

DROP TABLE IF EXISTS `flooring_types`;
CREATE TABLE `flooring_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `flooring_types`
--

INSERT INTO `flooring_types` (`id`, `name`, `active`) VALUES
(1, 'Tiles', 1),
(2, 'Cement / Screed', 1),
(3, 'Wooden Floor', 1),
(4, 'Vinyl / Laminate', 1),
(5, 'Terrazzo', 1),
(6, 'Carpet', 1),
(7, 'Other', 1);

-- --------------------------------------------------------

--
-- Table structure for table `landlords`
--

DROP TABLE IF EXISTS `landlords`;
CREATE TABLE `landlords` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `business_name` varchar(191) DEFAULT NULL,
  `business_reg_number` varchar(100) DEFAULT NULL,
  `tin_number` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_complete` tinyint(1) NOT NULL DEFAULT 0,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_name` varchar(191) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `mobile_money_name` varchar(191) DEFAULT NULL,
  `mobile_money_number` varchar(30) DEFAULT NULL,
  `mobile_money_network` varchar(30) DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `total_properties` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL COMMENT 'internal admin notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landlords`
--


-- --------------------------------------------------------

--
-- Table structure for table `landlord_agreements`
--

DROP TABLE IF EXISTS `landlord_agreements`;
CREATE TABLE `landlord_agreements` (
  `id` int(10) UNSIGNED NOT NULL,
  `agreement_ref` varchar(30) NOT NULL,
  `landlord_id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED DEFAULT NULL,
  `agreement_type` enum('general','property_specific') NOT NULL DEFAULT 'general',
  `version` varchar(10) NOT NULL DEFAULT '1.0',
  `terms_text` longtext DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `registration_fee` decimal(10,2) DEFAULT NULL,
  `agency_fee_rate` decimal(5,2) DEFAULT NULL,
  `status` enum('draft','sent','signed','expired','cancelled') NOT NULL DEFAULT 'draft',
  `signed_at` timestamp NULL DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `pdf_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `from_user_id` int(10) UNSIGNED DEFAULT NULL,
  `to_user_id` int(10) UNSIGNED DEFAULT NULL,
  `property_id` int(10) UNSIGNED DEFAULT NULL,
  `sender_name` varchar(191) DEFAULT NULL,
  `sender_email` varchar(191) DEFAULT NULL,
  `sender_phone` varchar(30) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `type` enum('enquiry','contact','complaint','general') NOT NULL DEFAULT 'enquiry',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'thread reply',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(80) NOT NULL COMMENT 'property_approved, viewing_confirmed, etc.',
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'related IDs and extra context' CHECK (json_valid(`data`)),
  `channel` enum('in_app','email','sms','whatsapp','push') NOT NULL DEFAULT 'in_app',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `failed` tinyint(1) NOT NULL DEFAULT 0,
  `fail_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `token` varchar(100) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--


-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `transaction_ref` varchar(50) NOT NULL COMMENT 'unique transaction reference',
  `user_id` int(10) UNSIGNED NOT NULL,
  `payer_name` varchar(191) DEFAULT NULL,
  `payer_email` varchar(191) DEFAULT NULL,
  `payer_phone` varchar(30) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'GHS',
  `purpose` enum('viewing_fee','registration_fee','verification_fee','agency_commission','featured_listing','renewal_fee','other') NOT NULL,
  `related_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'property_id, viewing_request_id etc.',
  `related_type` varchar(50) DEFAULT NULL COMMENT 'property, viewing_request, etc.',
  `gateway` varchar(50) DEFAULT NULL COMMENT 'paystack, flutterwave, manual',
  `gateway_ref` varchar(255) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `status` enum('pending','processing','successful','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'card, mobile_money, bank_transfer',
  `paid_at` timestamp NULL DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refund_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--


-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL COMMENT 'e.g. properties, users, payments',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`) VALUES
(1, 'View Users', 'users.view', 'users', NULL),
(2, 'Create Users', 'users.create', 'users', NULL),
(3, 'Edit Users', 'users.edit', 'users', NULL),
(4, 'Delete Users', 'users.delete', 'users', NULL),
(5, 'Suspend Users', 'users.suspend', 'users', NULL),
(6, 'View Properties', 'properties.view', 'properties', NULL),
(7, 'Create Properties', 'properties.create', 'properties', NULL),
(8, 'Edit Properties', 'properties.edit', 'properties', NULL),
(9, 'Delete Properties', 'properties.delete', 'properties', NULL),
(10, 'Approve Properties', 'properties.approve', 'properties', NULL),
(11, 'Reject Properties', 'properties.reject', 'properties', NULL),
(12, 'Verify Properties', 'properties.verify', 'properties', NULL),
(13, 'Feature Properties', 'properties.feature', 'properties', NULL),
(14, 'View Viewings', 'viewings.view', 'viewings', NULL),
(15, 'Manage Viewings', 'viewings.manage', 'viewings', NULL),
(16, 'View Payments', 'payments.view', 'payments', NULL),
(17, 'Manage Payments', 'payments.manage', 'payments', NULL),
(18, 'Refund Payments', 'payments.refund', 'payments', NULL),
(19, 'View Settings', 'settings.view', 'settings', NULL),
(20, 'Edit Settings', 'settings.edit', 'settings', NULL),
(21, 'Manage CMS', 'cms.manage', 'cms', NULL),
(22, 'View Reports', 'reports.view', 'reports', NULL),
(23, 'Export Reports', 'reports.export', 'reports', NULL),
(24, 'View Audit Logs', 'audit.view', 'audit', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
CREATE TABLE `properties` (
  `id` int(10) UNSIGNED NOT NULL,
  `landlord_id` int(10) UNSIGNED NOT NULL,
  `property_type_id` tinyint(3) UNSIGNED NOT NULL,
  `reference` varchar(20) NOT NULL COMMENT 'RW-XXXXX unique public ref',
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `description` text NOT NULL,
  `rent_monthly` decimal(12,2) NOT NULL DEFAULT 0.00,
  `rent_annual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `security_deposit` decimal(12,2) NOT NULL DEFAULT 0.00,
  `agency_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'GHS',
  `negotiable` tinyint(1) NOT NULL DEFAULT 0,
  `available_from` date DEFAULT NULL,
  `min_lease_months` tinyint(3) UNSIGNED DEFAULT 1,
  `furnished` enum('furnished','semi_furnished','unfurnished') NOT NULL DEFAULT 'unfurnished',
  `num_bedrooms` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `num_bathrooms` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `num_toilets` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `num_kitchens` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `max_occupants` tinyint(3) UNSIGNED DEFAULT NULL,
  `is_self_contained` tinyint(1) NOT NULL DEFAULT 0,
  `building_level` enum('ground','first','second','third','higher') NOT NULL DEFAULT 'ground',
  `elevator_available` tinyint(1) NOT NULL DEFAULT 0,
  `total_floors` tinyint(3) UNSIGNED DEFAULT 1,
  `has_balcony` tinyint(1) NOT NULL DEFAULT 0,
  `has_staircase` tinyint(1) NOT NULL DEFAULT 1,
  `ceiling_type_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `flooring_type_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `ceiling_notes` varchar(255) DEFAULT NULL,
  `flooring_notes` varchar(255) DEFAULT NULL,
  `kitchen_size` enum('mini','average','spacious') DEFAULT NULL,
  `kitchen_type` enum('private','shared') DEFAULT 'private',
  `kitchen_location` enum('indoor','outdoor') DEFAULT 'indoor',
  `kitchen_has_sink` tinyint(1) NOT NULL DEFAULT 0,
  `kitchen_has_cabinets` tinyint(1) NOT NULL DEFAULT 0,
  `kitchen_ventilation` tinyint(1) NOT NULL DEFAULT 0,
  `toilet_location` enum('inside','outside','shared','private') DEFAULT 'inside',
  `bathroom_location` enum('inside','outside','shared','private') DEFAULT 'inside',
  `combined_toilet_bath` tinyint(1) NOT NULL DEFAULT 0,
  `has_porch` tinyint(1) NOT NULL DEFAULT 0,
  `porch_type` enum('private','shared') DEFAULT NULL,
  `porch_covered` tinyint(1) DEFAULT 0,
  `porch_description` varchar(255) DEFAULT NULL,
  `has_wardrobe` tinyint(1) NOT NULL DEFAULT 0,
  `wardrobe_builtin` tinyint(1) NOT NULL DEFAULT 0,
  `wardrobe_indentation` tinyint(1) NOT NULL DEFAULT 0,
  `wardrobe_count` tinyint(3) UNSIGNED DEFAULT 0,
  `wardrobe_notes` varchar(255) DEFAULT NULL,
  `electricity_type` set('self_meter','shared_meter','prepaid','postpaid','ecg','other') DEFAULT NULL,
  `electricity_notes` varchar(255) DEFAULT NULL,
  `water_available` tinyint(1) NOT NULL DEFAULT 1,
  `water_source` set('ghana_water','borehole','tank','well','other') DEFAULT NULL,
  `water_type` enum('shared','private') DEFAULT 'shared',
  `water_frequency` varchar(100) DEFAULT NULL COMMENT 'e.g. 24/7, morning only',
  `water_storage` tinyint(1) NOT NULL DEFAULT 0,
  `water_notes` varchar(255) DEFAULT NULL,
  `pet_policy` enum('allowed','not_allowed','certain_allowed','approval_required') NOT NULL DEFAULT 'not_allowed',
  `pet_notes` varchar(255) DEFAULT NULL,
  `has_parking` tinyint(1) NOT NULL DEFAULT 0,
  `parking_spaces` tinyint(3) UNSIGNED DEFAULT 0,
  `parking_type` enum('private','shared','street') DEFAULT NULL,
  `parking_covered` tinyint(1) NOT NULL DEFAULT 0,
  `visitor_parking` tinyint(1) NOT NULL DEFAULT 0,
  `parking_fee` decimal(10,2) DEFAULT 0.00,
  `parking_notes` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','under_review','inspection_required','verified','rejected','suspended','rented','expired') NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_until` date DEFAULT NULL,
  `location_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=exact, 0=approximate',
  `show_on_map` tinyint(1) NOT NULL DEFAULT 1,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verification_ref` varchar(30) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `view_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `save_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `enquiry_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `rented_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_accessibility`
--

DROP TABLE IF EXISTS `property_accessibility`;
CREATE TABLE `property_accessibility` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `distance_main_road_km` decimal(6,2) DEFAULT NULL,
  `distance_vehicle_road_km` decimal(6,2) DEFAULT NULL,
  `walking_distance_m` int(10) UNSIGNED DEFAULT NULL COMMENT 'metres',
  `walking_time_minutes` tinyint(3) UNSIGNED DEFAULT NULL,
  `road_condition` enum('tarred','gravel','dirt','mixed') DEFAULT NULL,
  `vehicle_accessible` tinyint(1) NOT NULL DEFAULT 0,
  `car_accessible` tinyint(1) NOT NULL DEFAULT 0,
  `taxi_accessible` tinyint(1) NOT NULL DEFAULT 0,
  `motorbike_accessible` tinyint(1) NOT NULL DEFAULT 1,
  `public_transport_nearby` tinyint(1) NOT NULL DEFAULT 0,
  `public_transport_distance_m` int(10) UNSIGNED DEFAULT NULL,
  `public_transport_notes` varchar(255) DEFAULT NULL,
  `accessibility_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_accessibility`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_amenities`
--

DROP TABLE IF EXISTS `property_amenities`;
CREATE TABLE `property_amenities` (
  `property_id` int(10) UNSIGNED NOT NULL,
  `amenity_id` smallint(5) UNSIGNED NOT NULL,
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_amenities`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_comparisons`
--

DROP TABLE IF EXISTS `property_comparisons`;
CREATE TABLE `property_comparisons` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `session_key` varchar(100) DEFAULT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_comparisons`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_documents`
--

DROP TABLE IF EXISTS `property_documents`;
CREATE TABLE `property_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `type` enum('floor_plan','measurement_diagram','survey','other') NOT NULL DEFAULT 'other',
  `filename` varchar(255) NOT NULL,
  `path` varchar(500) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

DROP TABLE IF EXISTS `property_images`;
CREATE TABLE `property_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `path` varchar(500) NOT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `category` enum('exterior','living_room','bedroom','kitchen','bathroom','toilet','porch','compound','parking','road','other') NOT NULL DEFAULT 'other',
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `file_size` int(10) UNSIGNED DEFAULT NULL COMMENT 'bytes',
  `width` smallint(5) UNSIGNED DEFAULT NULL,
  `height` smallint(5) UNSIGNED DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_images`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_locations`
--

DROP TABLE IF EXISTS `property_locations`;
CREATE TABLE `property_locations` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `region_id` smallint(5) UNSIGNED DEFAULT NULL,
  `city_id` smallint(5) UNSIGNED DEFAULT NULL,
  `area_id` int(10) UNSIGNED DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `community` varchar(100) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `house_number` varchar(50) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `digital_address` varchar(50) DEFAULT NULL COMMENT 'GhanaPostGPS',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `location_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_locations`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_reports`
--

DROP TABLE IF EXISTS `property_reports`;
CREATE TABLE `property_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `reporter_name` varchar(191) DEFAULT NULL COMMENT 'for anonymous reports',
  `reporter_email` varchar(191) DEFAULT NULL,
  `report_type` enum('wrong_info','fake_property','wrong_dimensions','incorrect_rent','already_rented','misleading_photos','unsafe','fraud','other') NOT NULL,
  `description` text NOT NULL,
  `evidence_path` varchar(500) DEFAULT NULL,
  `status` enum('open','investigating','resolved','dismissed') NOT NULL DEFAULT 'open',
  `resolved_by` int(10) UNSIGNED DEFAULT NULL,
  `resolution` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_security`
--

DROP TABLE IF EXISTS `property_security`;
CREATE TABLE `property_security` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `gated` tinyint(1) NOT NULL DEFAULT 0,
  `security_guard` tinyint(1) NOT NULL DEFAULT 0,
  `cctv` tinyint(1) NOT NULL DEFAULT 0,
  `street_lighting` tinyint(1) NOT NULL DEFAULT 0,
  `security_gate` tinyint(1) NOT NULL DEFAULT 0,
  `fenced` tinyint(1) NOT NULL DEFAULT 0,
  `intercom` tinyint(1) NOT NULL DEFAULT 0,
  `alarm_system` tinyint(1) NOT NULL DEFAULT 0,
  `security_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_security`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_spaces`
--

DROP TABLE IF EXISTS `property_spaces`;
CREATE TABLE `property_spaces` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `space_name` varchar(80) NOT NULL COMMENT 'e.g. Bedroom, Living Room',
  `space_type` enum('bedroom','living_room','kitchen','bathroom','toilet','dining_room','porch','store','garage','other') NOT NULL DEFAULT 'other',
  `length` decimal(7,2) DEFAULT NULL,
  `width` decimal(7,2) DEFAULT NULL,
  `height` decimal(7,2) DEFAULT NULL,
  `unit` enum('m','ft','cm') NOT NULL DEFAULT 'm',
  `area_sqm` decimal(10,2) GENERATED ALWAYS AS (case when `unit` = 'm' then `length` * `width` when `unit` = 'ft' then `length` * `width` * 0.0929 when `unit` = 'cm' then `length` * `width` * 0.0001 else NULL end) STORED,
  `notes` varchar(255) DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_spaces`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_status_history`
--

DROP TABLE IF EXISTS `property_status_history`;
CREATE TABLE `property_status_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_status_history`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_types`
--

DROP TABLE IF EXISTS `property_types`;
CREATE TABLE `property_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL COMMENT 'Font Awesome class',
  `description` text DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_types`
--

INSERT INTO `property_types` (`id`, `name`, `slug`, `icon`, `description`, `sort_order`, `active`, `created_at`) VALUES
(1, 'Single Room', 'single-room', 'fa-door-closed', NULL, 1, 1, '2026-10-01 20:29:06'),
(2, 'Single Room Self-Contained', 'single-room-self-contained', 'fa-home', NULL, 2, 1, '2026-10-01 20:29:06'),
(3, 'Chamber and Hall', 'chamber-and-hall', 'fa-building', NULL, 3, 1, '2026-10-01 20:29:06'),
(4, 'Chamber and Hall Self-Contained', 'chamber-hall-self-contained', 'fa-building', NULL, 4, 1, '2026-10-01 20:29:06'),
(5, '2 Bedroom', '2-bedroom', 'fa-bed', NULL, 5, 1, '2026-10-01 20:29:06'),
(6, '2 Bedroom Self-Contained', '2-bedroom-self-contained', 'fa-bed', NULL, 6, 1, '2026-10-01 20:29:06'),
(7, '3 Bedroom', '3-bedroom', 'fa-bed', NULL, 7, 1, '2026-10-01 20:29:06'),
(8, '3 Bedroom Self-Contained', '3-bedroom-self-contained', 'fa-bed', NULL, 8, 1, '2026-10-01 20:29:06'),
(9, '4+ Bedroom', '4-plus-bedroom', 'fa-bed', NULL, 9, 1, '2026-10-01 20:29:06'),
(10, 'Apartment', 'apartment', 'fa-city', NULL, 10, 1, '2026-10-01 20:29:06'),
(11, 'Studio', 'studio', 'fa-couch', NULL, 11, 1, '2026-10-01 20:29:06'),
(12, 'Hostel', 'hostel', 'fa-hotel', NULL, 12, 1, '2026-10-01 20:29:06'),
(13, 'Shop / Commercial Space', 'shop-commercial', 'fa-store', NULL, 13, 1, '2026-10-01 20:29:06'),
(14, 'Office Space', 'office-space', 'fa-briefcase', NULL, 14, 1, '2026-10-01 20:29:06'),
(15, 'Other', 'other', 'fa-question-circle', NULL, 15, 1, '2026-10-01 20:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `property_verifications`
--

DROP TABLE IF EXISTS `property_verifications`;
CREATE TABLE `property_verifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending','in_progress','passed','failed','re_verification') NOT NULL DEFAULT 'pending',
  `verification_ref` varchar(30) NOT NULL,
  `inspection_date` date DEFAULT NULL,
  `inspection_time` time DEFAULT NULL,
  `inspector_name` varchar(191) DEFAULT NULL,
  `dimensions_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `images_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `facilities_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `accessibility_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `location_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `rent_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `landlord_id_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `utilities_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `availability_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `overall_score` tinyint(3) UNSIGNED DEFAULT NULL COMMENT '0-100',
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `checklist_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_verifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `property_videos`
--

DROP TABLE IF EXISTS `property_videos`;
CREATE TABLE `property_videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `type` enum('upload','youtube','vimeo') NOT NULL DEFAULT 'upload',
  `filename` varchar(255) DEFAULT NULL,
  `path` varchar(500) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `embed_code` text DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `duration` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'seconds',
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `thumbnail` varchar(500) DEFAULT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property_views`
--

DROP TABLE IF EXISTS `property_views`;
CREATE TABLE `property_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL COMMENT 'search, direct, featured, etc.',
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `property_views`
--


-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(191) NOT NULL COMMENT 'ip:action or user_id:action',
  `action` varchar(80) NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `blocked_until` timestamp NULL DEFAULT NULL,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--


-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
CREATE TABLE `regions` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `country` varchar(80) NOT NULL DEFAULT 'Ghana',
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `slug`, `country`, `sort_order`, `active`) VALUES
(1, 'Greater Accra', 'greater-accra', 'Ghana', 0, 1),
(2, 'Ashanti', 'ashanti', 'Ghana', 0, 1),
(3, 'Western', 'western', 'Ghana', 0, 1),
(4, 'Central', 'central', 'Ghana', 0, 1),
(5, 'Eastern', 'eastern', 'Ghana', 0, 1),
(6, 'Volta', 'volta', 'Ghana', 0, 1),
(7, 'Northern', 'northern', 'Ghana', 0, 1),
(8, 'Upper East', 'upper-east', 'Ghana', 0, 1),
(9, 'Upper West', 'upper-west', 'Ghana', 0, 1),
(10, 'Brong-Ahafo', 'brong-ahafo', 'Ghana', 0, 1),
(11, 'Western North', 'western-north', 'Ghana', 0, 1),
(12, 'Ahafo', 'ahafo', 'Ghana', 0, 1),
(13, 'Bono East', 'bono-east', 'Ghana', 0, 1),
(14, 'Oti', 'oti', 'Ghana', 0, 1),
(15, 'Savannah', 'savannah', 'Ghana', 0, 1),
(16, 'North East', 'north-east', 'Ghana', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `renters`
--

DROP TABLE IF EXISTS `renters`;
CREATE TABLE `renters` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `employer` varchar(191) DEFAULT NULL,
  `monthly_income_range` varchar(50) DEFAULT NULL,
  `num_occupants` tinyint(3) UNSIGNED DEFAULT 1,
  `has_pets` tinyint(1) DEFAULT 0,
  `pet_description` varchar(255) DEFAULT NULL,
  `preferred_move_in` date DEFAULT NULL,
  `preferred_regions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_regions`)),
  `budget_min` decimal(10,2) DEFAULT NULL,
  `budget_max` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `renters`
--


-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `viewing_request_id` int(10) UNSIGNED NOT NULL,
  `rating_overall` tinyint(3) UNSIGNED NOT NULL COMMENT '1-5',
  `rating_accuracy` tinyint(3) UNSIGNED DEFAULT NULL,
  `rating_condition` tinyint(3) UNSIGNED DEFAULT NULL,
  `rating_location` tinyint(3) UNSIGNED DEFAULT NULL,
  `rating_agent` tinyint(3) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','flagged') NOT NULL DEFAULT 'pending',
  `moderated_by` int(10) UNSIGNED DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `helpful_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Super Administrator', 'super_admin', 'Full platform access', '2026-10-01 20:29:05'),
(2, 'Administrator', 'admin', 'Platform administration', '2026-10-01 20:29:05'),
(3, 'Landlord', 'landlord', 'Property owner/manager', '2026-10-01 20:29:05'),
(4, 'Renter', 'renter', 'Property seeker/tenant', '2026-10-01 20:29:05');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `permission_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(2, 1),
(2, 2),
(2, 3),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24);

-- --------------------------------------------------------

--
-- Table structure for table `saved_properties`
--

DROP TABLE IF EXISTS `saved_properties`;
CREATE TABLE `saved_properties` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_properties`
--


-- --------------------------------------------------------

--
-- Table structure for table `search_logs`
--

DROP TABLE IF EXISTS `search_logs`;
CREATE TABLE `search_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `query` varchar(500) DEFAULT NULL,
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `results_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `search_logs`
--


-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `type` enum('string','integer','float','boolean','json','text') NOT NULL DEFAULT 'string',
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `label` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `type`, `group`, `label`, `description`, `is_public`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'RentalWebsite', 'string', 'general', 'Site Name', 'Platform display name', 1, NULL, '2026-10-01 20:29:06'),
(2, 'site_tagline', 'See it. Understand it. Then decide.', 'string', 'general', 'Site Tagline', 'Hero tagline', 1, NULL, '2026-10-01 20:29:06'),
(3, 'site_email', 'noreply@rent.habellesfoundation.org', 'string', 'general', 'Site Email', 'Main contact email', 1, NULL, '2026-10-01 20:29:06'),
(4, 'site_phone', '+233200000000', 'string', 'general', 'Site Phone', 'Main contact phone', 1, NULL, '2026-10-01 20:29:06'),
(5, 'site_address', 'Takoradi, Ghana', 'string', 'general', 'Site Address', 'Physical office address', 1, NULL, '2026-10-01 20:29:06'),
(6, 'currency_code', 'GHS', 'string', 'general', 'Currency Code', 'ISO currency code', 1, NULL, '2026-10-01 20:29:06'),
(7, 'currency_symbol', 'GH₵', 'string', 'general', 'Currency Symbol', 'Currency display symbol', 1, NULL, '2026-10-01 20:29:06'),
(8, 'default_country', 'Ghana', 'string', 'general', 'Default Country', NULL, 1, NULL, '2026-10-01 20:29:06'),
(9, 'commission_rate', '5.00', 'float', 'fees', 'Commission Rate (%)', 'Agency commission percentage of annual rent', 0, NULL, '2026-10-01 20:29:06'),
(10, 'commission_landlord_rate', '2.50', 'float', 'fees', 'Landlord Commission (%)', 'Portion of commission paid by landlord', 0, NULL, '2026-10-01 20:29:06'),
(11, 'commission_renter_rate', '2.50', 'float', 'fees', 'Renter Commission (%)', 'Portion of commission paid by renter', 0, NULL, '2026-10-01 20:29:06'),
(12, 'viewing_fee_default', '50.00', 'float', 'fees', 'Default Viewing Fee', 'Default physical viewing fee (GHS)', 0, NULL, '2026-10-01 20:29:06'),
(13, 'registration_fee', '100.00', 'float', 'fees', 'Property Registration Fee', 'Fee for landlord to list a property', 0, NULL, '2026-10-01 20:29:06'),
(14, 'verification_fee', '150.00', 'float', 'fees', 'Property Verification Fee', 'Fee for physical property inspection', 0, NULL, '2026-10-01 20:29:06'),
(15, 'featured_listing_fee', '200.00', 'float', 'fees', 'Featured Listing Fee', 'Fee to feature a property on homepage', 0, NULL, '2026-10-01 20:29:06'),
(16, 'max_images_per_property', '20', 'integer', 'properties', 'Max Images Per Property', 'Maximum images per listing', 0, NULL, '2026-10-01 20:29:06'),
(17, 'max_video_size_mb', '100', 'integer', 'properties', 'Max Video Size (MB)', NULL, 0, NULL, '2026-10-01 20:29:06'),
(18, 'max_image_size_mb', '5', 'integer', 'properties', 'Max Image Size (MB)', NULL, 0, NULL, '2026-10-01 20:29:06'),
(19, 'property_require_reg_fee', '0', 'boolean', 'properties', 'Require Registration Fee', '1 = landlord must pay to submit', 0, NULL, '2026-10-01 20:29:06'),
(20, 'auto_approve_properties', '0', 'boolean', 'properties', 'Auto-approve Properties', 'Skip admin review (not recommended)', 0, NULL, '2026-10-01 20:29:06'),
(21, 'mail_driver', 'log', 'string', 'email', 'Mail Driver', 'smtp, sendmail, etc.', 0, NULL, '2026-10-01 20:29:06'),
(22, 'mail_host', 'smtp.mailtrap.io', 'string', 'email', 'SMTP Host', NULL, 0, NULL, '2026-10-01 20:29:06'),
(23, 'mail_port', '587', 'integer', 'email', 'SMTP Port', NULL, 0, NULL, '2026-10-01 20:29:06'),
(24, 'mail_username', '', 'string', 'email', 'SMTP Username', NULL, 0, NULL, '2026-10-01 20:29:06'),
(25, 'mail_password', '', 'string', 'email', 'SMTP Password', NULL, 0, NULL, '2026-10-01 20:29:06'),
(26, 'mail_encryption', 'tls', 'string', 'email', 'Mail Encryption', 'tls or ssl', 0, NULL, '2026-10-01 20:29:06'),
(27, 'mail_from_address', 'noreply@rent.habellesfoundation.org', 'string', 'email', 'Mail From Address', NULL, 0, NULL, '2026-10-01 20:29:06'),
(28, 'mail_from_name', 'RentalWebsite', 'string', 'email', 'Mail From Name', NULL, 0, NULL, '2026-10-01 20:29:06'),
(29, 'login_max_attempts', '5', 'integer', 'security', 'Max Login Attempts', 'Before temporary lockout', 0, NULL, '2026-10-01 20:29:06'),
(30, 'login_lockout_minutes', '30', 'integer', 'security', 'Lockout Duration (minutes)', NULL, 0, NULL, '2026-10-01 20:29:06'),
(31, 'session_lifetime_hours', '24', 'integer', 'security', 'Session Lifetime (hours)', NULL, 0, NULL, '2026-10-01 20:29:06'),
(32, 'require_email_verify', '1', 'boolean', 'security', 'Require Email Verification', NULL, 0, NULL, '2026-10-01 20:29:06'),
(33, 'password_min_length', '8', 'integer', 'security', 'Min Password Length', NULL, 0, NULL, '2026-10-01 20:29:06'),
(34, 'viewing_advance_days', '7', 'integer', 'viewing', 'Min Advance Booking (days)', 'Minimum days ahead to book a viewing', 0, NULL, '2026-10-04 10:45:26'),
(35, 'viewing_max_advance_days', '30', 'integer', 'viewing', 'Max Advance Booking (days)', 'Maximum days ahead to book a viewing', 0, NULL, '2026-10-01 20:29:06'),
(36, 'viewing_cancellation_hrs', '24', 'integer', 'viewing', 'Cancellation Notice (hours)', 'Hours before viewing to allow cancellation', 0, NULL, '2026-10-01 20:29:06'),
(37, 'properties_per_page', '12', 'integer', 'search', 'Properties Per Page', NULL, 1, NULL, '2026-10-01 20:29:06'),
(38, 'enable_map_search', '1', 'boolean', 'search', 'Enable Map Search', NULL, 1, NULL, '2026-10-01 20:29:06'),
(39, 'show_exact_location', '0', 'boolean', 'search', 'Show Exact Location Default', 'Global default; can be overridden per property', 0, NULL, '2026-10-01 20:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL COMMENT 'Ghana Card, Passport, etc.',
  `id_number` varchar(100) DEFAULT NULL,
  `id_document` varchar(255) DEFAULT NULL COMMENT 'path to uploaded ID',
  `address` text DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','suspended','pending_verification') NOT NULL DEFAULT 'pending_verification',
  `email_verify_token` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `auth_provider` enum('local','google') NOT NULL DEFAULT 'local',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `notification_prefs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'email, sms, push preferences' CHECK (json_valid(`notification_prefs`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `phone_verified`, `email_verified`, `email_verified_at`, `password`, `avatar`, `gender`, `date_of_birth`, `id_type`, `id_number`, `id_document`, `address`, `region`, `city`, `status`, `email_verify_token`, `password_reset_token`, `password_reset_at`, `last_login_at`, `last_login_ip`, `login_attempts`, `locked_until`, `remember_token`, `two_factor_enabled`, `notification_prefs`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Super', 'Admin', 'admin@rentalwebsite.com', '+233200000000', 0, 1, '2026-10-01 20:36:11', '$2y$12$cvFd.CDcZA7Lwg/taRSVne/qYYOjSArh3Gprn1gbPAlPh1tfeRW6C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2026-10-04 10:38:02', '::1', 0, NULL, NULL, 0, NULL, '2026-10-01 20:29:05', '2026-10-04 10:38:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `viewing_requests`
--

DROP TABLE IF EXISTS `viewing_requests`;
CREATE TABLE `viewing_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(20) NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `renter_id` int(10) UNSIGNED NOT NULL,
  `viewing_slot_id` int(10) UNSIGNED DEFAULT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `alt_date` date DEFAULT NULL,
  `alt_time` time DEFAULT NULL,
  `num_attendees` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `special_request` text DEFAULT NULL,
  `viewing_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fee_paid` tinyint(1) NOT NULL DEFAULT 0,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending_payment','paid','requested','confirmed','rescheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'pending_payment',
  `confirmed_date` date DEFAULT NULL,
  `confirmed_time` time DEFAULT NULL,
  `confirmed_by` int(10) UNSIGNED DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_by` int(10) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `renter_notes` text DEFAULT NULL,
  `landlord_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `viewing_requests`
--


-- --------------------------------------------------------

--
-- Table structure for table `viewing_slots`
--

DROP TABLE IF EXISTS `viewing_slots`;
CREATE TABLE `viewing_slots` (
  `id` int(10) UNSIGNED NOT NULL,
  `property_id` int(10) UNSIGNED NOT NULL,
  `slot_date` date NOT NULL,
  `slot_time` time NOT NULL,
  `max_viewers` tinyint(3) UNSIGNED NOT NULL DEFAULT 2,
  `booked_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `viewing_slots`
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `agreement_signatures`
--
ALTER TABLE `agreement_signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_as_agreement` (`agreement_id`),
  ADD KEY `fk_as_user` (`user_id`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_amenities_slug` (`slug`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_areas_city` (`city_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_module` (`module`),
  ADD KEY `idx_audit_date` (`created_at`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_blog_slug` (`slug`),
  ADD KEY `idx_blog_status` (`status`),
  ADD KEY `fk_blog_author` (`author_id`);

--
-- Indexes for table `ceiling_types`
--
ALTER TABLE `ceiling_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cities_slug_region` (`slug`,`region_id`),
  ADD KEY `idx_cities_region` (`region_id`);

--
-- Indexes for table `cms_pages`
--
ALTER TABLE `cms_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cms_slug` (`slug`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comm_property` (`property_id`),
  ADD KEY `idx_comm_landlord` (`landlord_id`),
  ADD KEY `idx_comm_status` (`status`),
  ADD KEY `fk_comm_payment` (`payment_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cm_status` (`status`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ev_user` (`user_id`),
  ADD KEY `idx_ev_token` (`token`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flooring_types`
--
ALTER TABLE `flooring_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landlords`
--
ALTER TABLE `landlords`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_landlords_user` (`user_id`),
  ADD KEY `idx_landlords_verified` (`verified`),
  ADD KEY `fk_landlords_verified_by` (`verified_by`);

--
-- Indexes for table `landlord_agreements`
--
ALTER TABLE `landlord_agreements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_la_ref` (`agreement_ref`),
  ADD KEY `idx_la_landlord` (`landlord_id`),
  ADD KEY `fk_la_property` (`property_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_msg_from` (`from_user_id`),
  ADD KEY `idx_msg_to` (`to_user_id`),
  ADD KEY `idx_msg_property` (`property_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`),
  ADD KEY `idx_notif_read` (`user_id`,`is_read`),
  ADD KEY `idx_notif_type` (`type`),
  ADD KEY `idx_notif_created` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pr_email` (`email`),
  ADD KEY `idx_pr_token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payments_ref` (`transaction_ref`),
  ADD KEY `idx_payments_user` (`user_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_purpose` (`purpose`),
  ADD KEY `idx_payments_date` (`paid_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_permissions_slug` (`slug`),
  ADD KEY `idx_permissions_module` (`module`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_properties_reference` (`reference`),
  ADD UNIQUE KEY `uq_properties_slug` (`slug`),
  ADD KEY `idx_properties_landlord` (`landlord_id`),
  ADD KEY `idx_properties_type` (`property_type_id`),
  ADD KEY `idx_properties_status` (`status`),
  ADD KEY `idx_properties_verified` (`verified`),
  ADD KEY `idx_properties_rent_monthly` (`rent_monthly`),
  ADD KEY `idx_properties_featured` (`is_featured`,`featured_until`),
  ADD KEY `idx_properties_available_from` (`available_from`),
  ADD KEY `fk_properties_ceiling` (`ceiling_type_id`),
  ADD KEY `fk_properties_flooring` (`flooring_type_id`),
  ADD KEY `fk_properties_verified_by` (`verified_by`);

--
-- Indexes for table `property_accessibility`
--
ALTER TABLE `property_accessibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pa_property` (`property_id`);

--
-- Indexes for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD PRIMARY KEY (`property_id`,`amenity_id`),
  ADD KEY `fk_pam_amenity` (`amenity_id`);

--
-- Indexes for table `property_comparisons`
--
ALTER TABLE `property_comparisons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pc_user_property` (`user_id`,`property_id`),
  ADD KEY `idx_pc_property` (`property_id`);

--
-- Indexes for table `property_documents`
--
ALTER TABLE `property_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pd_property` (`property_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pi_property` (`property_id`),
  ADD KEY `idx_pi_primary` (`property_id`,`is_primary`),
  ADD KEY `fk_pi_uploader` (`uploaded_by`);

--
-- Indexes for table `property_locations`
--
ALTER TABLE `property_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_property_locations_property` (`property_id`),
  ADD KEY `idx_pl_region` (`region_id`),
  ADD KEY `idx_pl_city` (`city_id`),
  ADD KEY `idx_pl_area` (`area_id`),
  ADD KEY `idx_pl_lat_lng` (`latitude`,`longitude`);

--
-- Indexes for table `property_reports`
--
ALTER TABLE `property_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pr_property` (`property_id`),
  ADD KEY `idx_pr_status` (`status`),
  ADD KEY `fk_pr_user` (`user_id`),
  ADD KEY `fk_pr_resolver` (`resolved_by`);

--
-- Indexes for table `property_security`
--
ALTER TABLE `property_security`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_psec_property` (`property_id`);

--
-- Indexes for table `property_spaces`
--
ALTER TABLE `property_spaces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ps_property` (`property_id`);

--
-- Indexes for table `property_status_history`
--
ALTER TABLE `property_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_psh_property` (`property_id`),
  ADD KEY `fk_psh_user` (`changed_by`);

--
-- Indexes for table `property_types`
--
ALTER TABLE `property_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_property_types_slug` (`slug`);

--
-- Indexes for table `property_verifications`
--
ALTER TABLE `property_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pver_property` (`property_id`),
  ADD KEY `idx_pver_status` (`status`),
  ADD KEY `fk_pver_verifier` (`verified_by`);

--
-- Indexes for table `property_videos`
--
ALTER TABLE `property_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pv_property` (`property_id`);

--
-- Indexes for table `property_views`
--
ALTER TABLE `property_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pvw_property` (`property_id`),
  ADD KEY `idx_pvw_user` (`user_id`),
  ADD KEY `idx_pvw_date` (`viewed_at`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rl_key_action` (`key`,`action`),
  ADD KEY `idx_rl_key` (`key`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_regions_slug` (`slug`);

--
-- Indexes for table `renters`
--
ALTER TABLE `renters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_renters_user` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reviews_viewing` (`viewing_request_id`),
  ADD KEY `idx_rev_property` (`property_id`),
  ADD KEY `idx_rev_user` (`user_id`),
  ADD KEY `idx_rev_status` (`status`),
  ADD KEY `fk_rev_moderator` (`moderated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_slug` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `saved_properties`
--
ALTER TABLE `saved_properties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sp_user_property` (`user_id`,`property_id`),
  ADD KEY `idx_sp_property` (`property_id`);

--
-- Indexes for table `search_logs`
--
ALTER TABLE `search_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sl_user` (`user_id`),
  ADD KEY `idx_sl_date` (`created_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settings_key` (`key`),
  ADD KEY `idx_settings_group` (`group`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_phone` (`phone`),
  ADD UNIQUE KEY `uq_users_google_id` (`google_id`);

--
-- Indexes for table `viewing_requests`
--
ALTER TABLE `viewing_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_vr_reference` (`reference`),
  ADD KEY `idx_vr_property` (`property_id`),
  ADD KEY `idx_vr_renter` (`renter_id`),
  ADD KEY `idx_vr_status` (`status`),
  ADD KEY `idx_vr_date` (`preferred_date`),
  ADD KEY `fk_vr_slot` (`viewing_slot_id`),
  ADD KEY `fk_vr_confirmed_by` (`confirmed_by`);

--
-- Indexes for table `viewing_slots`
--
ALTER TABLE `viewing_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vs_property` (`property_id`),
  ADD KEY `idx_vs_date` (`slot_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agreement_signatures`
--
ALTER TABLE `agreement_signatures`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ceiling_types`
--
ALTER TABLE `ceiling_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cms_pages`
--
ALTER TABLE `cms_pages`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `flooring_types`
--
ALTER TABLE `flooring_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `landlords`
--
ALTER TABLE `landlords`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `landlord_agreements`
--
ALTER TABLE `landlord_agreements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_accessibility`
--
ALTER TABLE `property_accessibility`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_comparisons`
--
ALTER TABLE `property_comparisons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `property_documents`
--
ALTER TABLE `property_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_locations`
--
ALTER TABLE `property_locations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_reports`
--
ALTER TABLE `property_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_security`
--
ALTER TABLE `property_security`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_spaces`
--
ALTER TABLE `property_spaces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_status_history`
--
ALTER TABLE `property_status_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_types`
--
ALTER TABLE `property_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `property_verifications`
--
ALTER TABLE `property_verifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `property_videos`
--
ALTER TABLE `property_videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `property_views`
--
ALTER TABLE `property_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `renters`
--
ALTER TABLE `renters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `saved_properties`
--
ALTER TABLE `saved_properties`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `search_logs`
--
ALTER TABLE `search_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `viewing_requests`
--
ALTER TABLE `viewing_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT for table `viewing_slots`
--
ALTER TABLE `viewing_slots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agreement_signatures`
--
ALTER TABLE `agreement_signatures`
  ADD CONSTRAINT `fk_as_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `landlord_agreements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_as_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `fk_areas_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `fk_cities_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `fk_comm_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`id`),
  ADD CONSTRAINT `fk_comm_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_comm_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `landlords`
--
ALTER TABLE `landlords`
  ADD CONSTRAINT `fk_landlords_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_landlords_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `landlord_agreements`
--
ALTER TABLE `landlord_agreements`
  ADD CONSTRAINT `fk_la_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_la_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_msg_from` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_msg_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_msg_to` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `fk_properties_ceiling` FOREIGN KEY (`ceiling_type_id`) REFERENCES `ceiling_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_properties_flooring` FOREIGN KEY (`flooring_type_id`) REFERENCES `flooring_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_properties_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`id`),
  ADD CONSTRAINT `fk_properties_type` FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`),
  ADD CONSTRAINT `fk_properties_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_accessibility`
--
ALTER TABLE `property_accessibility`
  ADD CONSTRAINT `fk_pa_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_amenities`
--
ALTER TABLE `property_amenities`
  ADD CONSTRAINT `fk_pam_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pam_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_comparisons`
--
ALTER TABLE `property_comparisons`
  ADD CONSTRAINT `fk_pc_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_documents`
--
ALTER TABLE `property_documents`
  ADD CONSTRAINT `fk_pd_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `fk_pi_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pi_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_locations`
--
ALTER TABLE `property_locations`
  ADD CONSTRAINT `fk_pl_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pl_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pl_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pl_region` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_reports`
--
ALTER TABLE `property_reports`
  ADD CONSTRAINT `fk_pr_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pr_resolver` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_security`
--
ALTER TABLE `property_security`
  ADD CONSTRAINT `fk_psec_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_spaces`
--
ALTER TABLE `property_spaces`
  ADD CONSTRAINT `fk_ps_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_status_history`
--
ALTER TABLE `property_status_history`
  ADD CONSTRAINT `fk_psh_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_psh_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_verifications`
--
ALTER TABLE `property_verifications`
  ADD CONSTRAINT `fk_pver_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pver_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `property_videos`
--
ALTER TABLE `property_videos`
  ADD CONSTRAINT `fk_pv_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_views`
--
ALTER TABLE `property_views`
  ADD CONSTRAINT `fk_pvw_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `renters`
--
ALTER TABLE `renters`
  ADD CONSTRAINT `fk_renters_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_moderator` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rev_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_rev_viewing` FOREIGN KEY (`viewing_request_id`) REFERENCES `viewing_requests` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_properties`
--
ALTER TABLE `saved_properties`
  ADD CONSTRAINT `fk_sp_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `viewing_requests`
--
ALTER TABLE `viewing_requests`
  ADD CONSTRAINT `fk_vr_confirmed_by` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vr_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `fk_vr_renter` FOREIGN KEY (`renter_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_vr_slot` FOREIGN KEY (`viewing_slot_id`) REFERENCES `viewing_slots` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `viewing_slots`
--
ALTER TABLE `viewing_slots`
  ADD CONSTRAINT `fk_vs_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
