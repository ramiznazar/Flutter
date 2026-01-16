-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 07:51 PM
-- Server version: 8.0.44-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_gamez`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ads_setting`
--

CREATE TABLE `ads_setting` (
  `id` bigint UNSIGNED NOT NULL,
  `applovin_sdk_key` text COLLATE utf8mb4_unicode_ci,
  `applovin_inter_id` text COLLATE utf8mb4_unicode_ci,
  `applovin_reward_id` text COLLATE utf8mb4_unicode_ci,
  `applovin_native_id` text COLLATE utf8mb4_unicode_ci,
  `status` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` bigint UNSIGNED NOT NULL,
  `badge_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mining_sessions_required` int DEFAULT NULL,
  `spin_wheel_required` int DEFAULT NULL,
  `invite_friends_required` int DEFAULT NULL,
  `crutox_in_wallet_required` int DEFAULT NULL,
  `social_media_task_completed` tinyint(1) DEFAULT NULL,
  `badges_icon` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coin_settings`
--

CREATE TABLE `coin_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `seconds_per_coin` text COLLATE utf8mb4_unicode_ci,
  `max_seconds_allow` text COLLATE utf8mb4_unicode_ci,
  `claim_time_in_sec` text COLLATE utf8mb4_unicode_ci,
  `max_coin_claim_allow` text COLLATE utf8mb4_unicode_ci,
  `token` text COLLATE utf8mb4_unicode_ci,
  `token_price` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currency`
--

CREATE TABLE `currency` (
  `id` bigint UNSIGNED NOT NULL,
  `currency` text COLLATE utf8mb4_unicode_ci,
  `value` text COLLATE utf8mb4_unicode_ci,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `status` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `giveaway`
--

CREATE TABLE `giveaway` (
  `id` bigint UNSIGNED NOT NULL,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `title` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `link` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_submissions`
--

CREATE TABLE `kyc_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `front_image` text COLLATE utf8mb4_unicode_ci,
  `back_image` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `level`
--

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
  `is_ads_block` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_08_19_000000_create_failed_jobs_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2025_12_24_234939_create_ads_setting_table', 1),
(4, '2025_12_24_235118_create_badges_table', 1),
(5, '2025_12_24_235156_create_coin_settings_table', 1),
(6, '2025_12_24_235226_create_currency_table', 1),
(7, '2025_12_24_235233_create_giveaway_table', 1),
(8, '2025_12_24_235247_create_level_table', 1),
(9, '2025_12_24_235304_create_news_table', 1),
(10, '2025_12_24_235325_create_news_like_table', 1),
(11, '2025_12_24_235358_create_settings_table', 1),
(12, '2025_12_24_235422_create_shop_table', 1),
(13, '2025_12_24_235435_create_shop_views_table', 1),
(14, '2025_12_24_235450_create_social_media_setting_table', 1),
(15, '2025_12_24_235549_create_social_media_tokens_table', 1),
(16, '2025_12_24_235614_create_spin_table', 1),
(17, '2025_12_24_235633_create_spin_cailmed_table', 1),
(18, '2025_12_24_235652_create_spin_setting_table', 1),
(19, '2025_12_24_235659_create_users_table', 1),
(20, '2025_12_24_235706_create_user_guide_table', 1),
(21, '2025_12_24_235713_create_user_levels_table', 1),
(22, '2025_12_24_235724_create_kyc_submissions_table', 1),
(23, '2025_12_24_235745_create_admin_table', 1),
(24, '2025_12_24_235937_create_task_completions_table', 1),
(25, '2025_12_24_235949_create_user_boosters_table', 1),
(26, '2025_12_25_000021_create_mystery_box_claims_table', 1),
(27, '2025_12_25_000038_add_columns_to_settings_table', 1),
(28, '2025_12_25_000111_add_columns_to_news_table', 1),
(29, '2025_12_25_000128_add_columns_to_social_media_setting_table', 1),
(30, '2025_12_25_000649_add_columns_to_giveaway_table', 1),
(31, '2025_12_25_000708_add_columns_to_shop_table', 1),
(32, '2025_12_25_000718_add_columns_to_kyc_submissions_table', 1),
(33, '2025_12_25_000730_add_columns_to_mystery_box_claims_table', 1),
(34, '2025_12_25_093221_add_auth_token_to_users_table', 1),
(35, '2025_12_25_093222_add_custom_coin_speed_to_users_table', 1),
(36, '2025_12_25_093223_add_timestamps_to_all_tables', 1),
(37, '2025_12_25_093224_fix_news_table_for_sql_import', 1),
(38, '2025_12_25_093225_fix_settings_table_for_sql_import', 1),
(39, '2025_12_25_093226_fix_all_tables_for_sql_import', 1),
(40, '2025_12_26_142335_fix_users_id_auto_increment', 1),
(41, '2026_01_01_114601_add_remember_token_to_admin_table', 1),
(42, '2026_01_03_095201_add_custom_coin_speed_to_users_table', 1),
(43, '2026_01_14_193641_create_jobs_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `mystery_box_claims`
--

CREATE TABLE `mystery_box_claims` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `box_type` enum('common','rare','epic','legendary') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ads_watched` int DEFAULT '0',
  `ads_required` int DEFAULT NULL,
  `last_ad_watched_at` datetime DEFAULT NULL,
  `cooldown_until` datetime DEFAULT NULL,
  `box_opened` tinyint(1) DEFAULT '0',
  `reward_coins` decimal(10,2) DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `ID` int NOT NULL,
  `Image` text COLLATE utf8mb4_unicode_ci,
  `Title` text COLLATE utf8mb4_unicode_ci,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  `AdShow` tinyint(1) DEFAULT NULL,
  `RAdShow` tinyint(1) DEFAULT NULL,
  `Likes` text COLLATE utf8mb4_unicode_ci,
  `isliked` tinyint(1) DEFAULT NULL,
  `Status` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_like`
--

CREATE TABLE `news_like` (
  `ID` int NOT NULL,
  `News_ID` int DEFAULT NULL,
  `User_ID` int DEFAULT NULL,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
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
  `about_us_link` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE `shop` (
  `ID` int NOT NULL,
  `Image` text COLLATE utf8mb4_unicode_ci,
  `Title` text COLLATE utf8mb4_unicode_ci,
  `Link` text COLLATE utf8mb4_unicode_ci,
  `Likes` text COLLATE utf8mb4_unicode_ci,
  `isliked` tinyint DEFAULT NULL,
  `Status` tinyint DEFAULT NULL,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_views`
--

CREATE TABLE `shop_views` (
  `ID` int NOT NULL,
  `Shop_ID` int DEFAULT NULL,
  `User_ID` int DEFAULT NULL,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media_setting`
--

CREATE TABLE `social_media_setting` (
  `ID` int NOT NULL,
  `Name` text COLLATE utf8mb4_unicode_ci,
  `Icon` text COLLATE utf8mb4_unicode_ci,
  `Link` text COLLATE utf8mb4_unicode_ci,
  `Token` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_media_tokens`
--

CREATE TABLE `social_media_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `social_media_id` int DEFAULT NULL,
  `claim_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spin`
--

CREATE TABLE `spin` (
  `ID` int NOT NULL,
  `Prize` text COLLATE utf8mb4_unicode_ci,
  `Type` text COLLATE utf8mb4_unicode_ci,
  `Color` text COLLATE utf8mb4_unicode_ci,
  `CreatedAt` text COLLATE utf8mb4_unicode_ci,
  `Status` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spin_cailmed`
--

CREATE TABLE `spin_cailmed` (
  `UserID` int DEFAULT NULL,
  `Total` int DEFAULT NULL,
  `EndAt` text COLLATE utf8mb4_unicode_ci,
  `StartedAt` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spin_setting`
--

CREATE TABLE `spin_setting` (
  `ID` int NOT NULL,
  `ShowAd` tinyint(1) DEFAULT NULL,
  `AdType` text COLLATE utf8mb4_unicode_ci,
  `MaxLimit` int DEFAULT NULL,
  `Time` text COLLATE utf8mb4_unicode_ci,
  `SpinShow` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_completions`
--

CREATE TABLE `task_completions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `task_id` int DEFAULT NULL,
  `task_type` enum('daily','onetime') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `reward_available_at` datetime DEFAULT NULL,
  `reward_claimed` tinyint(1) DEFAULT '0',
  `reward_claimed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
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
  `join_date` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_boosters`
--

CREATE TABLE `user_boosters` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `booster_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '2x',
  `started_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_guide`
--

CREATE TABLE `user_guide` (
  `userID` bigint UNSIGNED DEFAULT NULL,
  `home` tinyint(1) DEFAULT '1',
  `mining` tinyint DEFAULT '1',
  `wallet` tinyint(1) DEFAULT '1',
  `badges` tinyint(1) DEFAULT '1',
  `level` tinyint(1) DEFAULT '1',
  `teamProfile` tinyint(1) DEFAULT '1',
  `news` tinyint(1) DEFAULT '1',
  `shop` tinyint(1) DEFAULT '1',
  `userProfile` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_levels`
--

CREATE TABLE `user_levels` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `mining_session` int DEFAULT NULL,
  `spin_wheel` int DEFAULT NULL,
  `current_level` int DEFAULT NULL,
  `achieved_at` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_username_unique` (`username`),
  ADD UNIQUE KEY `admin_email_unique` (`email`);

--
-- Indexes for table `ads_setting`
--
ALTER TABLE `ads_setting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coin_settings`
--
ALTER TABLE `coin_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currency`
--
ALTER TABLE `currency`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `giveaway`
--
ALTER TABLE `giveaway`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `kyc_submissions`
--
ALTER TABLE `kyc_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kyc_submissions_user_id_index` (`user_id`),
  ADD KEY `kyc_submissions_status_index` (`status`);

--
-- Indexes for table `level`
--
ALTER TABLE `level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mystery_box_claims`
--
ALTER TABLE `mystery_box_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mystery_box_claims_user_id_index` (`user_id`),
  ADD KEY `mystery_box_claims_box_type_index` (`box_type`),
  ADD KEY `mystery_box_claims_cooldown_until_index` (`cooldown_until`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `news_like`
--
ALTER TABLE `news_like`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `news_like_news_id_foreign` (`News_ID`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `shop_views`
--
ALTER TABLE `shop_views`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `social_media_setting`
--
ALTER TABLE `social_media_setting`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `social_media_tokens`
--
ALTER TABLE `social_media_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `social_media_tokens_user_id_index` (`user_id`),
  ADD KEY `social_media_tokens_social_media_id_index` (`social_media_id`);

--
-- Indexes for table `spin`
--
ALTER TABLE `spin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `spin_cailmed`
--
ALTER TABLE `spin_cailmed`
  ADD UNIQUE KEY `spin_cailmed_userid_unique` (`UserID`);

--
-- Indexes for table `spin_setting`
--
ALTER TABLE `spin_setting`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `task_completions`
--
ALTER TABLE `task_completions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_completions_user_id_index` (`user_id`),
  ADD KEY `task_completions_task_id_index` (`task_id`),
  ADD KEY `task_completions_task_type_index` (`task_type`),
  ADD KEY `task_completions_reward_available_at_index` (`reward_available_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_boosters`
--
ALTER TABLE `user_boosters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_boosters_user_id_index` (`user_id`),
  ADD KEY `user_boosters_expires_at_index` (`expires_at`),
  ADD KEY `user_boosters_is_active_index` (`is_active`);

--
-- Indexes for table `user_guide`
--
ALTER TABLE `user_guide`
  ADD KEY `user_guide_userid_foreign` (`userID`);

--
-- Indexes for table `user_levels`
--
ALTER TABLE `user_levels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_levels_user_id_index` (`user_id`),
  ADD KEY `user_levels_current_level_index` (`current_level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ads_setting`
--
ALTER TABLE `ads_setting`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coin_settings`
--
ALTER TABLE `coin_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currency`
--
ALTER TABLE `currency`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `giveaway`
--
ALTER TABLE `giveaway`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_submissions`
--
ALTER TABLE `kyc_submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `mystery_box_claims`
--
ALTER TABLE `mystery_box_claims`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_like`
--
ALTER TABLE `news_like`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop`
--
ALTER TABLE `shop`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shop_views`
--
ALTER TABLE `shop_views`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media_setting`
--
ALTER TABLE `social_media_setting`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_media_tokens`
--
ALTER TABLE `social_media_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spin`
--
ALTER TABLE `spin`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spin_setting`
--
ALTER TABLE `spin_setting`
  MODIFY `ID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_completions`
--
ALTER TABLE `task_completions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_boosters`
--
ALTER TABLE `user_boosters`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_levels`
--
ALTER TABLE `user_levels`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kyc_submissions`
--
ALTER TABLE `kyc_submissions`
  ADD CONSTRAINT `kyc_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mystery_box_claims`
--
ALTER TABLE `mystery_box_claims`
  ADD CONSTRAINT `mystery_box_claims_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `news_like`
--
ALTER TABLE `news_like`
  ADD CONSTRAINT `news_like_news_id_foreign` FOREIGN KEY (`News_ID`) REFERENCES `news` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_completions`
--
ALTER TABLE `task_completions`
  ADD CONSTRAINT `task_completions_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `social_media_setting` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_completions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_boosters`
--
ALTER TABLE `user_boosters`
  ADD CONSTRAINT `user_boosters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_guide`
--
ALTER TABLE `user_guide`
  ADD CONSTRAINT `user_guide_userid_foreign` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_levels`
--
ALTER TABLE `user_levels`
  ADD CONSTRAINT `user_levels_current_level_foreign` FOREIGN KEY (`current_level`) REFERENCES `level` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_levels_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
