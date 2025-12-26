CREATE TABLE `ads_setting` (
  `id` int NOT NULL AUTO_INCREMENT,
  `applovin_sdk_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `applovin_inter_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `applovin_reward_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `applovin_native_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `ads_setting`
VALUES (
        '1',
        'rTx3FKZHh3a0mTeH2Z1dQIfw5ltMS6x28eLq15MRwEzqRC53O1AAob5FzvWoHkWBeTFfUTSEQ8GTHIVMGOTDmM',
        'ca-app-pub-7208007749319336/6474121388',
        'Rewarded_Android',
        '4b8c963d504ae58f',
        '1'
    );

CREATE TABLE `badges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `badge_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mining_sessions_required` int DEFAULT NULL,
  `spin_wheel_required` int DEFAULT NULL,
  `invite_friends_required` int DEFAULT NULL,
  `crutox_in_wallet_required` int DEFAULT NULL,
  `social_media_task_completed` tinyint(1) DEFAULT NULL,
  `badges_icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 21 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `badges`
VALUES (
        '1',
        'Newbie Explorer: Once User Creates Account',
        '',
        '',
        '',
        '',
        '',
        'https://crutox.com/badges/Newbie%20Explorer.png'
    );

INSERT INTO
    `badges`
VALUES (
        '2',
        'Mining Novice: Once User Starts Their First Mining Session',
        '1',
        '',
        '',
        '',
        '',
        'https://crutox.com/badges/Mining%20Novice.png'
    );

INSERT INTO
    `badges`
VALUES (
        '3',
        'Social Apprentice: Once User Invites 5 Friends',
        '',
        '',
        '5',
        '',
        '',
        'https://crutox.com/badges/Social%20Apprentice.png'
    );

INSERT INTO
    `badges`
VALUES (
        '4',
        'Friendship Forger: Once User Invites 10 Friends',
        '',
        '',
        '10',
        '',
        '',
        'https://crutox.com/badges/Friendship%20Forger.png'
    );

INSERT INTO
    `badges`
VALUES (
        '5',
        'Community Architect: Once User Invites 20 Friends',
        '',
        '',
        '20',
        '',
        '',
        'https://crutox.com/badges/Community%20Architect.png'
    );

INSERT INTO
    `badges`
VALUES (
        '6',
        'Networking Prodigy: Once User Invites 50 Friends',
        '',
        '',
        '50',
        '',
        '',
        'https://crutox.com/badges/Networking%20Prodigy.png'
    );

INSERT INTO
    `badges`
VALUES (
        '7',
        'Bronze Digger: Mine for 30 Sessions',
        '30',
        '',
        '',
        '',
        '',
        'https://crutox.com/badges/Bronze%20Digger.png'
    );

INSERT INTO
    `badges`
VALUES (
        '8',
        'Silver Seeker: Mine for 90 Sessions|',
        '90',
        '',
        '',
        '',
        '',
        'https://crutox.com/badges/Silver%20Seeker.png'
    );

INSERT INTO
    `badges`
VALUES (
        '9',
        'Gold Gleaner: Mine for 200 Sessions',
        '200',
        '',
        '',
        '',
        '',
        'https://crutox.com/badges/Gold%20Gleaner.png'
    );

INSERT INTO
    `badges`
VALUES (
        '10',
        'Diamond Delver: Mine for 500 Sessions',
        '500',
        '',
        '',
        '',
        '',
        'https://crutox.com/badges/Diamond%20Delver.png'
    );

INSERT INTO
    `badges`
VALUES (
        '11',
        'Wheel Apprentice: Spin the Wheel 60 Time',
        '',
        '60',
        '',
        '',
        '',
        'https://crutox.com/badges/Wheel%20Apprentice.png'
    );

INSERT INTO
    `badges`
VALUES (
        '12',
        'Wheel Enthusiast: Spin the Wheel 180 Times',
        '',
        '180',
        '',
        '',
        '',
        'https://crutox.com/badges/Wheel%20Enthusiast.png'
    );

INSERT INTO
    `badges`
VALUES (
        '13',
        'Wheel Master: Spin the Wheel 500 Times',
        '',
        '500',
        '',
        '',
        '',
        'https://crutox.com/badges/Wheel%20Master.png'
    );

INSERT INTO
    `badges`
VALUES (
        '14',
        'Wheel Grandmaster: Spin the Wheel 1000 Times',
        '',
        '1000',
        '',
        '',
        '',
        'https://crutox.com/badges/Wheel%20Grandmaster.png'
    );

INSERT INTO
    `badges`
VALUES (
        '15',
        'Bronze Collector: Have 10 Crutox in Wallet',
        '',
        '',
        '',
        '10',
        '',
        'https://crutox.com/badges/Bronze%20Collector.png'
    );

INSERT INTO
    `badges`
VALUES (
        '16',
        'Silver Stasher: Have 50 Crutox in Wallet',
        '',
        '',
        '',
        '50',
        '',
        'https://crutox.com/badges/Silver%20Stasher.png'
    );

INSERT INTO
    `badges`
VALUES (
        '17',
        'Gold Hoarder: Have 100 Crutox in Wallet',
        '',
        '',
        '',
        '100',
        '',
        'https://crutox.com/badges/Gold%20Hoarder.png'
    );

INSERT INTO
    `badges`
VALUES (
        '18',
        'Diamond Tycoon: Have 500 Crutox in Wallet',
        '',
        '',
        '',
        '500',
        '',
        'https://crutox.com/badges/Diamond%20Tycoon.png'
    );

INSERT INTO
    `badges`
VALUES (
        '19',
        'Platinum Mogul: Have 1000 Crutox in Wallet',
        '',
        '',
        '',
        '1000',
        '',
        'https://crutox.com/badges/Platinum%20Mogul.png'
    );

INSERT INTO
    `badges`
VALUES (
        '20',
        'Social Sovereign: Complete All Social Media Tasks',
        '',
        '',
        '',
        '',
        '1',
        'https://crutox.com/badges/Social%20Sovereign.png'
    );

CREATE TABLE `coin_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `seconds_per_coin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `max_seconds_allow` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `claim_time_in_sec` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `max_coin_claim_allow` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token_price` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `coin_settings`
VALUES (
        '1',
        '3600',
        '21600',
        '3600',
        '4',
        '0.000002314814815',
        '0.0004'
    );

CREATE TABLE `currency` (
  `id` int NOT NULL AUTO_INCREMENT,
  `currency` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `currency`
VALUES (
        '1',
        'USDT',
        '1',
        'https://img.icons8.com/material-outlined/48/tether.png',
        '1'
    );

INSERT INTO
    `currency`
VALUES (
        '2',
        'BUSD',
        '1',
        'https://img.icons8.com/external-black-fill-lafs/64/external-Binance-USD-cryptocurrency-black-fill-lafs.png',
        '1'
    );

INSERT INTO
    `currency`
VALUES (
        '3',
        'USDC',
        '0',
        'https://img.icons8.com/external-black-fill-lafs/64/external-USDC-cryptocurrency-black-fill-lafs.png',
        '1'
    );

INSERT INTO
    `currency`
VALUES (
        '4',
        'TUSD',
        '0.9993',
        'https://img.icons8.com/external-black-fill-lafs/64/external-TrueUSD-cryptocurrency-black-fill-lafs.png',
        '1'
    );

INSERT INTO
    `currency`
VALUES (
        '5',
        'USDP',
        '0',
        'https://img.icons8.com/external-black-fill-lafs/64/external-Paxos-cryptocurrency-black-fill-lafs.png',
        '1'
    );

CREATE TABLE `giveaway` (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `level` (
  `id` int NOT NULL,
  `lvl_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mining_sessions` int NOT NULL,
  `spin_wheel` int NOT NULL,
  `total_invite` int NOT NULL,
  `user_account_old` int NOT NULL,
  `perk_crutox_per_time` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `perk_mining_time` int NOT NULL,
  `perk_crutox_reward` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `perk_other_access` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_ads_block` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `level`
VALUES (
        '1',
        'Novice',
        '0',
        '0',
        '0',
        '0',
        '0.5',
        '12',
        '0',
        'Eligible For Mining Crutox\n\n• 0.5 Crutox Per 12 Hours\n\n• Mining Session: 12 Hours\n\n• Access To Novice Discord Channel',
        '0'
    );

INSERT INTO
    `level`
VALUES (
        '2',
        'Elite',
        '60',
        '90',
        '5',
        '30',
        '1',
        '12',
        '10',
        '1 Crutox Per 12 Hours\n\n• Mining Session: 12 Hours\n\n• Receive 10 Crutox Upon Reaching Elite\n\n• Access To Exclusive Elite Discord Channel',
        '0'
    );

INSERT INTO
    `level`
VALUES (
        '3',
        'Pro',
        '120',
        '180',
        '10',
        '60',
        '5',
        '24',
        '20',
        '5 Crutox Per 24 Hours\n\n• Mining Session: 24 Hours\n\n• Receive 20 Crutox Upon Reaching Pro\n\n• Access To Exclusive Pro Discord Channel',
        '0'
    );

INSERT INTO
    `level`
VALUES (
        '4',
        'Master',
        '180',
        '300',
        '20',
        '90',
        '1',
        '24',
        '50',
        '1 Crutox Per 24 Hours\n\n• Mining Session: 24 Hours\n\n• Receive 50 Crutox Upon Reaching Master\n\n• Access To Exclusive Master Discord Channel\n\n• Ad-free Experience',
        '1'
    );

CREATE TABLE `news` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CreatedAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `AdShow` tinyint(1) NOT NULL,
  `RAdShow` tinyint(1) NOT NULL,
  `Likes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isliked` tinyint(1) NOT NULL,
  `Status` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 64 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `news`
VALUES (
        '61',
        'https://gamez.altervista.org/mining/api/images/cover.jpg',
        'Welcome To Crutox',
        'Dear Users,\r\n\r\nCrutox is your simple, mobile mining app. Mine cryptocurrency effortlessly every 12 hours. No complicated setups—just start the app and earn. Stay connected for upcoming features on our socials and join our growing community. Welcome to the future of mobile mining!\r\n\r\nSincerely,\r\nCrutox Team',
        '2023-10-26',
        '0',
        '0',
        '9086',
        '0',
        '1'
    );

INSERT INTO
    `news`
VALUES (
        '63',
        'https://gamez.altervista.org/mining/api/images/IMG_1999 2.jpg',
        ' Celebrating a Milestone: 5000 Downloads on the Play Store!',
        'We are thrilled to announce that Crutox app has crossed a significant milestone – 5000 downloads on the Play Store! 🎉\r\n\r\nThis achievement is a testament to the incredible support and enthusiasm from our valued users. 🫶\r\n\r\nFollow us on our Socials To Stay Updated 🔔',
        '2024-01-04',
        '0',
        '0',
        '7339',
        '0',
        '1'
    );

CREATE TABLE `news_like` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `News_ID` int NOT NULL,
  `User_ID` int NOT NULL,
  `CreatedAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `News_ID` (`News_ID`),
  CONSTRAINT `news_like_ibfk_1` FOREIGN KEY (`News_ID`) REFERENCES `news` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 16600 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO `news_like` VALUES ( '17', '61', '9', '2023-10-26-22:11:37' );

INSERT INTO
    `news_like`
VALUES (
        '18',
        '61',
        '25',
        '2023-10-28-08:48:02'
    );

INSERT INTO
    `news_like`
VALUES (
        '19',
        '61',
        '44',
        '2023-11-05-18:07:52'
    );

CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `update_version` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `maintenance` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `force_update` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `update_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `maintenance_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `update_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pirvacy_policy_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `term_n_condition_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `support_email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `faq_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `white_paper_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `road_map_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `about_us_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `settings`
VALUES (
        '1',
        '1.1.9',
        '0',
        '0',
        'What\'s New:\n\nMinor Bug Fixes',
        'Server issue',
        'https://play.google.com/store/apps/details?id=com.mine.crutox&hl=en&gl=US',
        'https://crutox.com/Privacy',
        'https://crutox.com/Terms',
        'support@crutox.com',
        'https://crutox.com/FAQ',
        'https://crutox.com/',
        '',
        'https://crutox.com/'
    );

CREATE TABLE `shop` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Likes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `isliked` tinyint NOT NULL,
  `Status` tinyint NOT NULL,
  `CreatedAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `shop`
VALUES (
        '1',
        'https://pbs.twimg.com/media/F-WAajbawAAyPg2.jpg',
        'Crutox',
        'https://crutox.com/',
        '12592',
        '0',
        '1',
        '2023-10-26'
    );

CREATE TABLE `shop_views` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Shop_ID` int NOT NULL,
  `User_ID` int NOT NULL,
  `CreatedAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 12502 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE `social_media_setting` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Icon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 7 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `social_media_setting`
VALUES (
        '1',
        'Twitter',
        'https://img.icons8.com/color/48/114450/twitter-circled',
        'https://twitter.com/CrutoxApp',
        '2'
    );

INSERT INTO
    `social_media_setting`
VALUES (
        '2',
        'Instagram',
        'https://img.icons8.com/color/48/000000/instagram-new--v1.png',
        'https://instagram.com/crutox',
        '2'
    );

INSERT INTO
    `social_media_setting`
VALUES (
        '3',
        'Telegram',
        'https://img.icons8.com/color/48/oWiuH0jFiU0R/telegram-app',
        'https://t.me/crutox',
        '2'
    );

INSERT INTO
    `social_media_setting`
VALUES (
        '4',
        'Discord',
        'https://img.icons8.com/color/48/oWiuH0jFiU0R/discord',
        'https://discord.gg/usnMa6hjUK',
        '2'
    );

INSERT INTO
    `social_media_setting`
VALUES (
        '6',
        'Youtube',
        'https://img.icons8.com/?size=48&id=19318&format=png',
        'https://www.youtube.com/@CrutoxApp',
        '2'
    );

CREATE TABLE `social_media_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `social_media_id` int DEFAULT NULL,
  `claim_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `social_media_id` (`social_media_id`)
) ENGINE = MyISAM AUTO_INCREMENT = 150211 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `social_media_tokens`
VALUES (
        '1',
        '11',
        '1',
        '2023-10-13 14:38:47'
    );

INSERT INTO
    `social_media_tokens`
VALUES (
        '2',
        '28',
        '1',
        '2023-10-13 16:17:04'
    );

INSERT INTO
    `social_media_tokens`
VALUES (
        '4',
        '11',
        '6',
        '2023-10-18 18:43:54'
    );

CREATE TABLE `spin` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Prize` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Color` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CreatedAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 10 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `spin`
VALUES (
        '1',
        '0.001',
        'Default',
        '#AACAF7',
        '2022-12-28',
        '1'
    );

INSERT INTO
    `spin`
VALUES (
        '2',
        '0.004',
        'Default',
        '#80ECFF',
        '2022-12-28',
        '1'
    );

INSERT INTO
    `spin`
VALUES (
        '3',
        '0.01',
        'Default',
        '#FFFFFF',
        '2022-12-28',
        '1'
    );

INSERT INTO
    `spin`
VALUES (
        '4',
        '0.04',
        'Default',
        '#AACAF7',
        '2022-12-28',
        '1'
    );

INSERT INTO
    `spin`
VALUES (
        '5',
        '0.07',
        'Default',
        '#80ECFF',
        '2022-12-28',
        '1'
    );

INSERT INTO
    `spin`
VALUES (
        '6',
        '0.1',
        'Default',
        '#FFFFFF',
        '2022-12-28',
        '1'
    );

CREATE TABLE `spin_cailmed` (
  `UserID` int NOT NULL,
  `Total` int NOT NULL,
  `EndAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `StartedAt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `UserID` (`UserID`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `spin_cailmed`
VALUES (
        '3',
        '1',
        '2024-07-09-06:38:08',
        '2024-07-08-18:38:08'
        '2024-07-08-18:38:08'
    );

INSERT INTO
    `spin_cailmed`
VALUES (
        '9',
        '1',
        '2023-10-27-20:50:28',
        '2023-10-27-08:50:28'
    );

INSERT INTO
    `spin_cailmed`
VALUES (
        '11',
        '1',
        '2025-01-25-22:39:45',
        '2025-01-25-10:39:45'
    );

CREATE TABLE `spin_setting` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `ShowAd` tinyint(1) NOT NULL,
  `AdType` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `MaxLimit` int NOT NULL,
  `Time` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `SpinShow` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `spin_setting`
VALUES (
        '1',
        '1',
        'INTERSTITIAL',
        '4',
        '43200',
        '1'
    );



CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `country` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `coin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_mining` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mining_end_time` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `coin_end_time` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_coin_claim` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_active` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mining_time` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username_count` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_invite` int NOT NULL,
  `invite_setup` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `account_status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ban_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ban_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `otp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `join_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 86900 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `users`
VALUES (
        '3',
        'John Doe',
        'user786@krypton.com',
        '1234567890',
        'United States',
        'password123',
        '33.35000025598099',
        '2200',
        '0',
        '2024-06-24-03:57:25',
        '0',
        '0',
        '',
        '43200',
        'johndoe',
        '1',
        '4',
        '5',
        'active',
        '',
        '',
        '',
        '2023-03-15 11:23:26'
    );

INSERT INTO
    `users`
VALUES (
        '5',
        'Zain Ul Abaideen',
        'user456@krypton.com',
        '+923040685740',
        'Pakistan',
        '33941463',
        '0.0700001120016',
        '1',
        '0',
        '2023-09-21-19:46:22',
        '0000-00-00 00:00:00',
        '0',
        '',
        '36000',
        'zayn',
        '1',
        '2',
        '9',
        'active',
        '',
        '',
        '',
        '2023-03-31 02:00:37'
    );

INSERT INTO
    `users`
VALUES (
        '9',
        'Zain Ul Abaidin',
        'zain@gmail.com',
        '+923040685741',
        'Pakistan',
        '12345678',
        '4.270001776000809',
        '111',
        '0',
        '2023-07-23-05:23:18',
        '0000-00-00 00:00:00',
        '0',
        '',
        '43200',
        'zain',
        '1',
        '5',
        'skip',
        'active',
        'test',
        '',
        '',
        '2023-04-24 13:49:05'
    );

CREATE TABLE `user_guide` (
    `userID` int NOT NULL,
    `home` tinyint(1) NOT NULL DEFAULT '1',
    `mining` tinyint NOT NULL DEFAULT '1',
    `wallet` tinyint(1) NOT NULL DEFAULT '1',
    `badges` tinyint(1) NOT NULL DEFAULT '1',
    `level` tinyint(1) NOT NULL DEFAULT '1',
    `teamProfile` tinyint(1) NOT NULL DEFAULT '1',
    `news` tinyint(1) NOT NULL DEFAULT '1',
    `shop` tinyint(1) NOT NULL DEFAULT '1',
    `userProfile` tinyint(1) NOT NULL DEFAULT '1',
    KEY `userID` (`userID`),
    CONSTRAINT `user_guide_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_levels` (
    `id` int NOT NULL AUTO_INCREMENT,
    `user_id` int NOT NULL,
    `mining_session` int NOT NULL,
    `spin_wheel` int NOT NULL,
    `current_level` int NOT NULL,
    `achieved_at` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `current_level` (`current_level`),
    CONSTRAINT `user_levels_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `user_levels_ibfk_2` FOREIGN KEY (`current_level`) REFERENCES `level` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 82967 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

INSERT INTO
    `user_levels`
VALUES (
        '1',
        '3',
        '1',
        '0',
        '1',
        '2023-11-20 14:03:10'
    );

-- ============================================
-- Database Migration for Crutox Admin Panel
-- Add missing columns required by admin panel
-- If columns already exist, errors can be safely ignored
-- ============================================

-- Add Link column to news table (for redirect links)
ALTER TABLE `news` ADD COLUMN `Link` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL AFTER `Description`;

-- Add columns to social_media_setting table for task management
ALTER TABLE `social_media_setting` ADD COLUMN `task_type` VARCHAR(50) DEFAULT 'onetime' AFTER `Token`;
ALTER TABLE `social_media_setting` ADD COLUMN `Status` TINYINT(1) DEFAULT 1 AFTER `task_type`;

-- Add columns to giveaway table
ALTER TABLE `giveaway` ADD COLUMN `reward` DECIMAL(10,2) DEFAULT 0 AFTER `description`;
ALTER TABLE `giveaway` ADD COLUMN `start_date` DATETIME NULL AFTER `reward`;
ALTER TABLE `giveaway` ADD COLUMN `end_date` DATETIME NULL AFTER `start_date`;
ALTER TABLE `giveaway` ADD COLUMN `status` VARCHAR(50) DEFAULT 'active' AFTER `end_date`;
ALTER TABLE `giveaway` ADD COLUMN `redirect_link` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `link`;

-- Add columns to shop table
ALTER TABLE `shop` ADD COLUMN `Description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `Title`;
ALTER TABLE `shop` ADD COLUMN `Price` DECIMAL(10,2) DEFAULT 0 AFTER `Description`;

-- Add columns to settings table for mining, referral, user count, and mystery box settings
ALTER TABLE `settings` ADD COLUMN `mining_speed` DECIMAL(10,2) DEFAULT 10.00 AFTER `about_us_link`;
ALTER TABLE `settings` ADD COLUMN `base_mining_rate` DECIMAL(10,2) DEFAULT 5.00 AFTER `mining_speed`;
ALTER TABLE `settings` ADD COLUMN `max_mining_speed` DECIMAL(10,2) DEFAULT 50.00 AFTER `base_mining_rate`;
ALTER TABLE `settings` ADD COLUMN `referrer_reward` INT DEFAULT 50 AFTER `max_mining_speed`;
ALTER TABLE `settings` ADD COLUMN `referee_reward` INT DEFAULT 25 AFTER `referrer_reward`;
ALTER TABLE `settings` ADD COLUMN `max_referrals` INT DEFAULT 100 AFTER `referee_reward`;
ALTER TABLE `settings` ADD COLUMN `bonus_reward` INT DEFAULT 500 AFTER `max_referrals`;
ALTER TABLE `settings` ADD COLUMN `current_users` INT DEFAULT 99000 AFTER `bonus_reward`;
ALTER TABLE `settings` ADD COLUMN `goal_users` INT DEFAULT 1000000 AFTER `current_users`;
ALTER TABLE `settings` ADD COLUMN `daily_tasks_reset_time` DATETIME NULL AFTER `goal_users`;
ALTER TABLE `settings` ADD COLUMN `common_box_cooldown` INT DEFAULT 0 AFTER `daily_tasks_reset_time`;
ALTER TABLE `settings` ADD COLUMN `common_box_ads` INT DEFAULT 1 AFTER `common_box_cooldown`;
ALTER TABLE `settings` ADD COLUMN `common_box_min_coins` DECIMAL(10,2) DEFAULT 1.00 AFTER `common_box_ads`;
ALTER TABLE `settings` ADD COLUMN `common_box_max_coins` DECIMAL(10,2) DEFAULT 5.00 AFTER `common_box_min_coins`;
ALTER TABLE `settings` ADD COLUMN `rare_box_cooldown` INT DEFAULT 5 AFTER `common_box_max_coins`;
ALTER TABLE `settings` ADD COLUMN `rare_box_ads` INT DEFAULT 3 AFTER `rare_box_cooldown`;
ALTER TABLE `settings` ADD COLUMN `rare_box_min_coins` DECIMAL(10,2) DEFAULT 5.00 AFTER `rare_box_ads`;
ALTER TABLE `settings` ADD COLUMN `rare_box_max_coins` DECIMAL(10,2) DEFAULT 15.00 AFTER `rare_box_min_coins`;
ALTER TABLE `settings` ADD COLUMN `epic_box_cooldown` INT DEFAULT 10 AFTER `rare_box_max_coins`;
ALTER TABLE `settings` ADD COLUMN `epic_box_ads` INT DEFAULT 6 AFTER `epic_box_cooldown`;
ALTER TABLE `settings` ADD COLUMN `epic_box_min_coins` DECIMAL(10,2) DEFAULT 15.00 AFTER `epic_box_ads`;
ALTER TABLE `settings` ADD COLUMN `epic_box_max_coins` DECIMAL(10,2) DEFAULT 50.00 AFTER `epic_box_min_coins`;
ALTER TABLE `settings` ADD COLUMN `legendary_box_cooldown` INT DEFAULT 30 AFTER `epic_box_max_coins`;
ALTER TABLE `settings` ADD COLUMN `legendary_box_ads` INT DEFAULT 10 AFTER `legendary_box_cooldown`;
ALTER TABLE `settings` ADD COLUMN `legendary_box_min_coins` DECIMAL(10,2) DEFAULT 50.00 AFTER `legendary_box_ads`;
ALTER TABLE `settings` ADD COLUMN `legendary_box_max_coins` DECIMAL(10,2) DEFAULT 200.00 AFTER `legendary_box_min_coins`;

-- Add KYC settings columns to settings table
ALTER TABLE `settings` ADD COLUMN `kyc_mining_sessions` INT DEFAULT 14 AFTER `legendary_box_max_coins`;
ALTER TABLE `settings` ADD COLUMN `kyc_referrals_required` INT DEFAULT 10 AFTER `kyc_mining_sessions`;

-- Create KYC submissions table
CREATE TABLE IF NOT EXISTS `kyc_submissions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `full_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `dob` DATE NOT NULL,
    `front_image` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `back_image` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `admin_notes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `status` (`status`),
    CONSTRAINT `kyc_submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- Additional Database Migrations
-- Admin Panel Authentication, Task System, Boosters, Mystery Box
-- ============================================


-- Admin Panel Authentication - Database Migration
-- Run this script to create the admin table for authentication

CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user
-- Default credentials: username: admin, password: admin123
-- IMPORTANT: Change the password after first login!
INSERT INTO `admin` (`username`, `email`, `password`, `name`) 
VALUES ('admin123', 'admin@gmail.com', '$2y$10$oGyuW94JqC/YQTOuHe22JOdKY5wN2YiiUhr8dzDp/xL2v237dcr1a', 'Admin User');

-- Database Migration for Task System, Boosters, and Mystery Box Cooldowns
-- Run this script to add required tables and columns

-- Task Completion Tracking Table
-- Tracks when users start/complete tasks and their timer status
CREATE TABLE IF NOT EXISTS `task_completions` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `task_id` INT NOT NULL,
    `task_type` ENUM('daily', 'onetime') NOT NULL,
    `started_at` DATETIME NOT NULL,
    `reward_available_at` DATETIME NOT NULL,
    `reward_claimed` TINYINT(1) DEFAULT 0,
    `reward_claimed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `task_id` (`task_id`),
    KEY `task_type` (`task_type`),
    KEY `reward_available_at` (`reward_available_at`),
    CONSTRAINT `task_completions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `task_completions_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `social_media_setting` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Booster Tracking Table
-- Tracks active boosters for users (2x multiplier, 1 hour duration)
CREATE TABLE IF NOT EXISTS `user_boosters` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `booster_type` VARCHAR(50) DEFAULT '2x',
    `started_at` DATETIME NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `expires_at` (`expires_at`),
    KEY `is_active` (`is_active`),
    CONSTRAINT `user_boosters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Mystery Box Claims Tracking Table
-- Tracks when users claim mystery boxes and enforces cooldowns
CREATE TABLE IF NOT EXISTS `mystery_box_claims` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `box_type` ENUM('common', 'rare', 'epic', 'legendary') NOT NULL,
    `ads_watched` INT DEFAULT 0,
    `ads_required` INT NOT NULL,
    `last_ad_watched_at` DATETIME NULL,
    `cooldown_until` DATETIME NULL,
    `box_opened` TINYINT(1) DEFAULT 0,
    `reward_coins` DECIMAL(10,2) NULL,
    `opened_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `box_type` (`box_type`),
    KEY `cooldown_until` (`cooldown_until`),
    CONSTRAINT `mystery_box_claims_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add ad waterfall configuration columns to settings table
-- Note: If columns already exist, errors can be safely ignored
ALTER TABLE `settings` ADD COLUMN `ad_waterfall_order` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'JSON array: ["admob", "meta", "unity", "applovin"]';
ALTER TABLE `settings` ADD COLUMN `ad_waterfall_enabled` TINYINT(1) DEFAULT 1;

-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- If you get errors about duplicate columns, those columns already exist and can be ignored

