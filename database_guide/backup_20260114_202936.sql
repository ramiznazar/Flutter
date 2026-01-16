-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: my_gamez
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_username_unique` (`username`),
  UNIQUE KEY `admin_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ads_setting`
--

DROP TABLE IF EXISTS `ads_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ads_setting` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `applovin_sdk_key` text COLLATE utf8mb4_unicode_ci,
  `applovin_inter_id` text COLLATE utf8mb4_unicode_ci,
  `applovin_reward_id` text COLLATE utf8mb4_unicode_ci,
  `applovin_native_id` text COLLATE utf8mb4_unicode_ci,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ads_setting`
--

LOCK TABLES `ads_setting` WRITE;
/*!40000 ALTER TABLE `ads_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `ads_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `badge_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mining_sessions_required` int DEFAULT NULL,
  `spin_wheel_required` int DEFAULT NULL,
  `invite_friends_required` int DEFAULT NULL,
  `crutox_in_wallet_required` int DEFAULT NULL,
  `social_media_task_completed` tinyint(1) DEFAULT NULL,
  `badges_icon` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coin_settings`
--

DROP TABLE IF EXISTS `coin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coin_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seconds_per_coin` text COLLATE utf8mb4_unicode_ci,
  `max_seconds_allow` text COLLATE utf8mb4_unicode_ci,
  `claim_time_in_sec` text COLLATE utf8mb4_unicode_ci,
  `max_coin_claim_allow` text COLLATE utf8mb4_unicode_ci,
  `token` text COLLATE utf8mb4_unicode_ci,
  `token_price` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coin_settings`
--

LOCK TABLES `coin_settings` WRITE;
/*!40000 ALTER TABLE `coin_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `coin_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currency` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency` text COLLATE utf8mb4_unicode_ci,
  `value` text COLLATE utf8mb4_unicode_ci,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `status` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency`
--

LOCK TABLES `currency` WRITE;
/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `giveaway`
--

DROP TABLE IF EXISTS `giveaway`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `giveaway` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `title` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `giveaway`
--

LOCK TABLES `giveaway` WRITE;
/*!40000 ALTER TABLE `giveaway` DISABLE KEYS */;
/*!40000 ALTER TABLE `giveaway` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kyc_submissions`
--

DROP TABLE IF EXISTS `kyc_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kyc_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `front_image` text COLLATE utf8mb4_unicode_ci,
  `back_image` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kyc_submissions_user_id_index` (`user_id`),
  KEY `kyc_submissions_status_index` (`status`),
  CONSTRAINT `kyc_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kyc_submissions`
--

LOCK TABLES `kyc_submissions` WRITE;
/*!40000 ALTER TABLE `kyc_submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `kyc_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `level`
--

DROP TABLE IF EXISTS `level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `level` (
  `id` int NOT NULL,
  `lvl_name` text COLLATE utf8mb4_unicode_ci,
  `mining_sessions` int DEFAULT NULL,
  `spin_wheel` int DEFAULT NULL,
  `total_invite` int DEFAULT NULL,
  `user_account_old` int DEFAULT NULL,
  `perk_crutox_per_time` text COLLATE utf8mb4_unicode_ci,
  `perk_mining_time` int DEFAULT NULL,
  `perk_crutox_reward` text COLLATE utf8mb4_unicode_ci,
  `perk_other_access` text COLLATE utf8mb4_unicode_ci,
  `is_ads_block` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `level`
--

LOCK TABLES `level` WRITE;
/*!40000 ALTER TABLE `level` DISABLE KEYS */;
/*!40000 ALTER TABLE `level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_08_19_000000_create_failed_jobs_table',1),(2,'2019_12_14_000001_create_personal_access_tokens_table',1),(3,'2025_12_24_234939_create_ads_setting_table',1),(4,'2025_12_24_235118_create_badges_table',1),(5,'2025_12_24_235156_create_coin_settings_table',1),(6,'2025_12_24_235226_create_currency_table',1),(7,'2025_12_24_235233_create_giveaway_table',1),(8,'2025_12_24_235247_create_level_table',1),(9,'2025_12_24_235304_create_news_table',1),(10,'2025_12_24_235325_create_news_like_table',1),(11,'2025_12_24_235358_create_settings_table',1),(12,'2025_12_24_235422_create_shop_table',1),(13,'2025_12_24_235435_create_shop_views_table',1),(14,'2025_12_24_235450_create_social_media_setting_table',1),(15,'2025_12_24_235549_create_social_media_tokens_table',1),(16,'2025_12_24_235614_create_spin_table',1),(17,'2025_12_24_235633_create_spin_cailmed_table',1),(18,'2025_12_24_235652_create_spin_setting_table',1),(19,'2025_12_24_235659_create_users_table',1),(20,'2025_12_24_235706_create_user_guide_table',1),(21,'2025_12_24_235713_create_user_levels_table',1),(22,'2025_12_24_235724_create_kyc_submissions_table',1),(23,'2025_12_24_235745_create_admin_table',1),(24,'2025_12_24_235937_create_task_completions_table',1),(25,'2025_12_24_235949_create_user_boosters_table',1),(26,'2025_12_25_000021_create_mystery_box_claims_table',1),(27,'2025_12_25_000038_add_columns_to_settings_table',1),(28,'2025_12_25_000111_add_columns_to_news_table',1),(29,'2025_12_25_000128_add_columns_to_social_media_setting_table',1),(30,'2025_12_25_000649_add_columns_to_giveaway_table',1),(31,'2025_12_25_000708_add_columns_to_shop_table',1),(32,'2025_12_25_000718_add_columns_to_kyc_submissions_table',1),(33,'2025_12_25_000730_add_columns_to_mystery_box_claims_table',1),(34,'2025_12_25_093221_add_auth_token_to_users_table',1),(35,'2025_12_25_093222_add_custom_coin_speed_to_users_table',1),(36,'2025_12_25_093223_add_timestamps_to_all_tables',1),(37,'2025_12_25_093224_fix_news_table_for_sql_import',1),(38,'2025_12_25_093225_fix_settings_table_for_sql_import',1),(39,'2025_12_25_093226_fix_all_tables_for_sql_import',1),(40,'2025_12_26_142335_fix_users_id_auto_increment',1),(41,'2026_01_01_114601_add_remember_token_to_admin_table',1),(42,'2026_01_03_095201_add_custom_coin_speed_to_users_table',1),(43,'2026_01_14_193641_create_jobs_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mystery_box_claims`
--

DROP TABLE IF EXISTS `mystery_box_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mystery_box_claims` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `box_type` enum('common','rare','epic','legendary') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ads_watched` int DEFAULT '0',
  `ads_required` int DEFAULT NULL,
  `last_ad_watched_at` datetime DEFAULT NULL,
  `cooldown_until` datetime DEFAULT NULL,
  `box_opened` tinyint(1) DEFAULT '0',
  `reward_coins` decimal(10,2) DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mystery_box_claims_user_id_index` (`user_id`),
  KEY `mystery_box_claims_box_type_index` (`box_type`),
  KEY `mystery_box_claims_cooldown_until_index` (`cooldown_until`),
  CONSTRAINT `mystery_box_claims_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mystery_box_claims`
--

LOCK TABLES `mystery_box_claims` WRITE;
/*!40000 ALTER TABLE `mystery_box_claims` DISABLE KEYS */;
/*!40000 ALTER TABLE `mystery_box_claims` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Image` text COLLATE utf8mb4_unicode_ci,
  `Title` text COLLATE utf8mb4_unicode_ci,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  `AdShow` tinyint(1) DEFAULT NULL,
  `RAdShow` tinyint(1) DEFAULT NULL,
  `Likes` text COLLATE utf8mb4_unicode_ci,
  `isliked` tinyint(1) DEFAULT NULL,
  `Status` int DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_like`
--

DROP TABLE IF EXISTS `news_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_like` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `News_ID` int DEFAULT NULL,
  `User_ID` int DEFAULT NULL,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`ID`),
  KEY `news_like_news_id_foreign` (`News_ID`),
  CONSTRAINT `news_like_news_id_foreign` FOREIGN KEY (`News_ID`) REFERENCES `news` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_like`
--

LOCK TABLES `news_like` WRITE;
/*!40000 ALTER TABLE `news_like` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_like` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `update_version` text COLLATE utf8mb4_unicode_ci,
  `maintenance` text COLLATE utf8mb4_unicode_ci,
  `force_update` text COLLATE utf8mb4_unicode_ci,
  `update_message` text COLLATE utf8mb4_unicode_ci,
  `maintenance_message` text COLLATE utf8mb4_unicode_ci,
  `update_link` text COLLATE utf8mb4_unicode_ci,
  `pirvacy_policy_link` text COLLATE utf8mb4_unicode_ci,
  `term_n_condition_link` text COLLATE utf8mb4_unicode_ci,
  `support_email` text COLLATE utf8mb4_unicode_ci,
  `faq_link` text COLLATE utf8mb4_unicode_ci,
  `white_paper_link` text COLLATE utf8mb4_unicode_ci,
  `road_map_link` text COLLATE utf8mb4_unicode_ci,
  `about_us_link` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shop`
--

DROP TABLE IF EXISTS `shop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shop` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Image` text COLLATE utf8mb4_unicode_ci,
  `Title` text COLLATE utf8mb4_unicode_ci,
  `Link` text COLLATE utf8mb4_unicode_ci,
  `Likes` text COLLATE utf8mb4_unicode_ci,
  `isliked` tinyint DEFAULT NULL,
  `Status` tinyint DEFAULT NULL,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop`
--

LOCK TABLES `shop` WRITE;
/*!40000 ALTER TABLE `shop` DISABLE KEYS */;
/*!40000 ALTER TABLE `shop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shop_views`
--

DROP TABLE IF EXISTS `shop_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shop_views` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Shop_ID` int DEFAULT NULL,
  `User_ID` int DEFAULT NULL,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shop_views`
--

LOCK TABLES `shop_views` WRITE;
/*!40000 ALTER TABLE `shop_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `shop_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_media_setting`
--

DROP TABLE IF EXISTS `social_media_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_media_setting` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE utf8mb4_unicode_ci,
  `Icon` text COLLATE utf8mb4_unicode_ci,
  `Link` text COLLATE utf8mb4_unicode_ci,
  `Token` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_media_setting`
--

LOCK TABLES `social_media_setting` WRITE;
/*!40000 ALTER TABLE `social_media_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_media_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_media_tokens`
--

DROP TABLE IF EXISTS `social_media_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_media_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `social_media_id` int DEFAULT NULL,
  `claim_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `social_media_tokens_user_id_index` (`user_id`),
  KEY `social_media_tokens_social_media_id_index` (`social_media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_media_tokens`
--

LOCK TABLES `social_media_tokens` WRITE;
/*!40000 ALTER TABLE `social_media_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_media_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spin`
--

DROP TABLE IF EXISTS `spin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spin` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Prize` text COLLATE utf8mb4_unicode_ci,
  `Type` text COLLATE utf8mb4_unicode_ci,
  `Color` text COLLATE utf8mb4_unicode_ci,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  `Status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spin`
--

LOCK TABLES `spin` WRITE;
/*!40000 ALTER TABLE `spin` DISABLE KEYS */;
/*!40000 ALTER TABLE `spin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spin_cailmed`
--

DROP TABLE IF EXISTS `spin_cailmed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spin_cailmed` (
  `UserID` int DEFAULT NULL,
  `Total` int DEFAULT NULL,
  `EndAt` text COLLATE utf8mb4_unicode_ci,
  `StartedAt` text COLLATE utf8mb4_unicode_ci,
  UNIQUE KEY `spin_cailmed_userid_unique` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spin_cailmed`
--

LOCK TABLES `spin_cailmed` WRITE;
/*!40000 ALTER TABLE `spin_cailmed` DISABLE KEYS */;
/*!40000 ALTER TABLE `spin_cailmed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spin_setting`
--

DROP TABLE IF EXISTS `spin_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spin_setting` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ShowAd` tinyint(1) DEFAULT NULL,
  `AdType` text COLLATE utf8mb4_unicode_ci,
  `MaxLimit` int DEFAULT NULL,
  `Time` text COLLATE utf8mb4_unicode_ci,
  `SpinShow` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spin_setting`
--

LOCK TABLES `spin_setting` WRITE;
/*!40000 ALTER TABLE `spin_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `spin_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_completions`
--

DROP TABLE IF EXISTS `task_completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_completions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `task_id` int DEFAULT NULL,
  `task_type` enum('daily','onetime') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `reward_available_at` datetime DEFAULT NULL,
  `reward_claimed` tinyint(1) DEFAULT '0',
  `reward_claimed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_completions_user_id_index` (`user_id`),
  KEY `task_completions_task_id_index` (`task_id`),
  KEY `task_completions_task_type_index` (`task_type`),
  KEY `task_completions_reward_available_at_index` (`reward_available_at`),
  CONSTRAINT `task_completions_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `social_media_setting` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `task_completions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_completions`
--

LOCK TABLES `task_completions` WRITE;
/*!40000 ALTER TABLE `task_completions` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_boosters`
--

DROP TABLE IF EXISTS `user_boosters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_boosters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `booster_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2x',
  `started_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_boosters_user_id_index` (`user_id`),
  KEY `user_boosters_expires_at_index` (`expires_at`),
  KEY `user_boosters_is_active_index` (`is_active`),
  CONSTRAINT `user_boosters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_boosters`
--

LOCK TABLES `user_boosters` WRITE;
/*!40000 ALTER TABLE `user_boosters` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_boosters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_guide`
--

DROP TABLE IF EXISTS `user_guide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_guide` (
  `userID` bigint unsigned DEFAULT NULL,
  `home` tinyint(1) DEFAULT '1',
  `mining` tinyint DEFAULT '1',
  `wallet` tinyint(1) DEFAULT '1',
  `badges` tinyint(1) DEFAULT '1',
  `level` tinyint(1) DEFAULT '1',
  `teamProfile` tinyint(1) DEFAULT '1',
  `news` tinyint(1) DEFAULT '1',
  `shop` tinyint(1) DEFAULT '1',
  `userProfile` tinyint(1) DEFAULT '1',
  KEY `user_guide_userid_foreign` (`userID`),
  CONSTRAINT `user_guide_userid_foreign` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_guide`
--

LOCK TABLES `user_guide` WRITE;
/*!40000 ALTER TABLE `user_guide` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_guide` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_levels`
--

DROP TABLE IF EXISTS `user_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `mining_session` int DEFAULT NULL,
  `spin_wheel` int DEFAULT NULL,
  `current_level` int DEFAULT NULL,
  `achieved_at` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `user_levels_user_id_index` (`user_id`),
  KEY `user_levels_current_level_index` (`current_level`),
  CONSTRAINT `user_levels_current_level_foreign` FOREIGN KEY (`current_level`) REFERENCES `level` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_levels_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_levels`
--

LOCK TABLES `user_levels` WRITE;
/*!40000 ALTER TABLE `user_levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci,
  `email` text COLLATE utf8mb4_unicode_ci,
  `phone` text COLLATE utf8mb4_unicode_ci,
  `country` text COLLATE utf8mb4_unicode_ci,
  `password` text COLLATE utf8mb4_unicode_ci,
  `token` text COLLATE utf8mb4_unicode_ci,
  `custom_coin_speed` decimal(10,2) DEFAULT NULL COMMENT 'Custom coin speed for individual user, null means use overall settings',
  `coin` text COLLATE utf8mb4_unicode_ci,
  `is_mining` text COLLATE utf8mb4_unicode_ci,
  `mining_end_time` text COLLATE utf8mb4_unicode_ci,
  `coin_end_time` text COLLATE utf8mb4_unicode_ci,
  `total_coin_claim` text COLLATE utf8mb4_unicode_ci,
  `last_active` text COLLATE utf8mb4_unicode_ci,
  `mining_time` text COLLATE utf8mb4_unicode_ci,
  `username` text COLLATE utf8mb4_unicode_ci,
  `username_count` text COLLATE utf8mb4_unicode_ci,
  `total_invite` int DEFAULT NULL,
  `invite_setup` text COLLATE utf8mb4_unicode_ci,
  `account_status` text COLLATE utf8mb4_unicode_ci,
  `ban_reason` text COLLATE utf8mb4_unicode_ci,
  `ban_date` text COLLATE utf8mb4_unicode_ci,
  `otp` text COLLATE utf8mb4_unicode_ci,
  `join_date` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-14 20:29:36
