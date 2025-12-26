# Crutox Admin Panel - Complete Documentation

## Overview

The Crutox Admin Panel is a comprehensive management system that allows administrators to control all aspects of the Crutox mining application without requiring app updates. All settings are stored in the database and applied in real-time.

---

## Table of Contents

1. [Dashboard](#1-dashboard)
2. [Content Management](#2-content-management)
   - [News Management](#21-news-management)
   - [Tasks Management](#22-tasks-management)
   - [Shop Management](#23-shop-management)
   - [Giveaway Management](#24-giveaway-management)
3. [Settings](#3-settings)
   - [Mining Speed Settings](#31-mining-speed-settings)
   - [Referral Rewards](#32-referral-rewards)
   - [Mystery Box Settings](#33-mystery-box-settings)
4. [User Management](#4-user-management)
   - [Users & Coins](#41-users--coins)
   - [KYC Management](#42-kyc-management)
   - [KYC Settings](#43-kyc-settings)

---

## 1. Dashboard

**Location:** `index.php`

### Features

#### User Count Management
- **Real Users Display:** Shows actual number of registered users from database (read-only, auto-calculated)
- **Display Users (Fake):** Manually set user count to display in app (e.g., 99,000)
- **Goal Users:** Set target user count (e.g., 1,000,000)
- **Progress Display:** Shows progress bar with percentage (Display Users / Goal Users)

### How to Use

1. Navigate to **Dashboard** from sidebar
2. View real user count (auto-calculated from database)
3. Set **Display Users** to any number you want to show in app
4. Set **Goal Users** target number
5. Click **Update User Count**
6. Changes apply immediately - app will show "Display Users / Goal Users"

### Use Cases

- Show fake user count to create social proof (e.g., 99,000/1,000,000)
- Adjust goal users for marketing campaigns
- Monitor real vs displayed user counts

---

## 2. Content Management

### 2.1. News Management

**Location:** `news.php`

#### Features
- **Add News:** Create new news articles with title, content, image, and redirect link
- **Edit News:** Update existing news articles
- **Delete News:** Remove news articles
- **Status Management:** Activate/deactivate news (Active/Inactive)
- **Redirect Links:** Configure where users are redirected when clicking news

#### How to Use

1. Navigate to **News Management** from sidebar
2. **To Add News:**
   - Fill in News Title, Content, Redirect Link, Image URL
   - Select Status (Active/Inactive)
   - Click **Save News**
3. **To Edit News:**
   - Click **Edit** button on any news item
   - Modify fields and click **Update News**
4. **To Delete News:**
   - Click **Delete** button on any news item
   - Confirm deletion

#### Fields
- **News Title:** Title of the news article
- **News Content:** Description/content of the news
- **Redirect Link:** URL where users are redirected (required)
- **Image URL:** Image to display with news
- **Status:** Active (shown in app) or Inactive (hidden)

---

### 2.2. Tasks Management

**Location:** `tasks.php`

#### Features

##### Daily Tasks (3 Tasks)
- **Manage 3 Daily Tasks:** Configure task name, reward, and redirect link for each
- **24-Hour Reset Timer:** Set when daily tasks reset (every 24 hours)
- **5-Minute Timer:** Users get 5-minute timer after starting task, then can claim reward
- **Manual Control:** All 3 tasks can be changed anytime from admin panel

##### One-Time Tasks
- **Add One-Time Tasks:** Create tasks that can only be completed once per user
- **1-Hour Timer:** Users get 1-hour timer, then reward is given automatically
- **Edit/Delete:** Manage existing one-time tasks

#### How to Use

**Daily Tasks:**
1. Navigate to **Tasks Management** from sidebar
2. Scroll to **Daily Tasks Settings** section
3. Configure all 3 tasks:
   - Task Name (e.g., "Follow Instagram")
   - Reward in Coins (e.g., 2)
   - Redirect Link (e.g., "https://instagram.com/...")
4. Set **Next Reset Time** (24-hour cycle)
5. Click **Save Daily Tasks**

**One-Time Tasks:**
1. Click **Add One-Time Task** button
2. Fill in Task Name, Reward, Redirect Link
3. Click **Save Task**
4. To edit/delete, use buttons in the tasks table

#### Task System Details

**Daily Tasks:**
- Users can complete each task once per 24-hour cycle
- After starting task, 5-minute timer begins
- After 5 minutes, user can claim reward
- Tasks reset every 24 hours automatically

**One-Time Tasks:**
- Users can complete each task only once (ever)
- After starting task, 1-hour timer begins
- After 1 hour, reward is given automatically (regardless of actual completion)
- No manual claim needed - fully automatic

---

### 2.3. Shop Management

**Location:** `shop.php`

#### Features
- **Add Shop Items:** Create new shop items with name, image, price, and redirect link
- **Edit Shop Items:** Update existing shop items
- **Delete Shop Items:** Remove shop items
- **Status Management:** Activate/deactivate items (Active/Inactive)
- **Redirect Links:** Configure where users are redirected when clicking shop items

#### How to Use

1. Navigate to **Shop Management** from sidebar
2. **To Add Item:**
   - Fill in Item Name, Redirect Link, Item Image URL
   - Select Status (Active/Inactive)
   - Click **Save Shop Item**
3. **To Edit Item:**
   - Click **Edit** button on any item
   - Modify fields and click **Update Shop Item**
4. **To Delete Item:**
   - Click **Delete** button on any item
   - Confirm deletion

#### Fields
- **Item Name:** Name of the shop item
- **Redirect Link:** URL where users are redirected (required)
- **Item Image URL:** Image to display for the item
- **Status:** Active (shown in app) or Inactive (hidden)

---

### 2.4. Giveaway Management

**Location:** `giveaway.php`

#### Features
- **Add Giveaways:** Create new giveaways with title, description, icon, and redirect link
- **Edit Giveaways:** Update existing giveaways
- **Delete Giveaways:** Remove giveaways
- **Redirect Links:** Configure where users are redirected when clicking giveaways

#### How to Use

1. Navigate to **Giveaway** from sidebar
2. **To Add Giveaway:**
   - Fill in Giveaway Title, Description, Redirect Link, Icon URL
   - Click **Save Giveaway**
3. **To Edit Giveaway:**
   - Click **Edit** button on any giveaway
   - Modify fields and click **Update Giveaway**
4. **To Delete Giveaway:**
   - Click **Delete** button on any giveaway
   - Confirm deletion

#### Fields
- **Giveaway Title:** Title of the giveaway
- **Description:** Description/details of the giveaway
- **Redirect Link:** URL where users are redirected (required)
- **Icon URL:** Icon/image to display for the giveaway

---

## 3. Settings

### 3.1. Mining Speed Settings

**Location:** `mining-settings.php`

#### Features
- **Mining Speed:** Configure coins mined per hour
- **Base Mining Rate:** Base mining rate without boosters
- **Maximum Mining Speed:** Maximum possible mining speed with all boosters

#### How to Use

1. Navigate to **Mining Speed Settings** from sidebar
2. Enter values for:
   - **Mining Speed (Coins per Hour):** Main mining speed
   - **Base Mining Rate:** Base rate without boosters
   - **Maximum Mining Speed:** Maximum speed with boosters
3. Click **Update Mining Speed**
4. Changes apply immediately

#### Settings Details
- All values are in coins per hour
- Changes take effect immediately
- Current settings are displayed in a table below the form

---

### 3.2. Referral Rewards

**Location:** `referral-settings.php`

#### Features
- **Referrer Reward:** Coins given to person who refers (inviter)
- **Referee Reward:** Coins given to person who is referred (invitee)
- **Maximum Referrals:** Maximum number of referrals a user can have
- **Bonus Reward:** Bonus reward when reaching maximum referrals

#### How to Use

1. Navigate to **Referral Rewards** from sidebar
2. Enter values for:
   - **Referrer Reward:** Coins for inviter
   - **Referee Reward:** Coins for invitee
   - **Maximum Referrals:** Max referrals per user
   - **Bonus Reward:** Bonus coins at max referrals
3. Click **Update Referral Settings**
4. Changes apply immediately

#### Settings Details
- All rewards are in coins
- Changes take effect immediately
- Current settings are displayed in a table below the form

---

### 3.3. Mystery Box Settings

**Location:** `mystery-box.php`

#### Features
- **4 Box Types:** Common, Rare, Epic, Legendary
- **Cooldown Period:** Adjustable cooldown between each ad watch (in minutes)
- **Ads Required:** Number of ads user must watch to open box
- **Min/Max Coins:** Reward range for each box type

#### How to Use

1. Navigate to **Mystery Box Settings** from sidebar
2. For each box type (Common, Rare, Epic, Legendary):
   - **Cooldown Period (Minutes):** Time between each ad watch
     - Set to **0** to remove cooldown (users can watch ads immediately)
     - Set to any number (e.g., 5, 10, 30) to add cooldown
   - **Ads Required:** Number of ads needed to open box
   - **Min Coins:** Minimum reward coins
   - **Max Coins:** Maximum reward coins
3. Click **Update [Box Type]**
4. Changes apply immediately - **no app update needed**

#### Cooldown System

**How Cooldown Works:**
- Cooldown is applied **between each ad watch**
- Example: Rare box with 5-minute cooldown
  - User watches Ad 1 → 5-minute cooldown starts
  - User tries to watch Ad 2 immediately → Blocked (must wait 5 minutes)
  - After 5 minutes → User can watch Ad 2
  - User watches Ad 2 → 5-minute cooldown starts again
  - Process repeats until all ads are watched

**Removing Cooldown:**
- Set cooldown to **0** for any box type
- Users can watch all ads immediately with no waiting

**Default Settings:**
- **Common Box:** 0 minutes cooldown, 1 ad required
- **Rare Box:** 5 minutes cooldown, 3 ads required
- **Epic Box:** 10 minutes cooldown, 6 ads required
- **Legendary Box:** 30 minutes cooldown, 10 ads required

---

## 4. User Management

### 4.1. Users & Coins

**Location:** `users.php`

#### Features

##### Give Crutox Coins to Users
- **Search Users:** Search by User ID, Username, or Email
- **Add Coins:** Give coins to users (positive number)
- **Remove Coins:** Remove coins from users (negative number)
- **Transaction Notes:** Add reason/note for giving coins

##### Give Boosters to Users
- **Assign Boosters:** Give mining boosters to users
- **Booster Types:** 2x, 3x, or 5x multiplier
- **Duration:** Set duration in hours (0.1 to 24 hours)
- **Transaction Notes:** Add reason/note for giving booster

##### Users List
- **View All Users:** See all registered users with balances
- **Active Boosters Display:** See which users have active boosters
- **Search Functionality:** Search users by ID, username, email, or name
- **Pagination:** Navigate through user list

#### How to Use

**Give Coins:**
1. Navigate to **Users & Coins** from sidebar
2. In **Give Crutox Coins to User** section:
   - Enter User ID / Username / Email
   - Enter Amount (positive to add, negative to remove)
   - Add Reason/Note (optional)
   - Click **Give Coins**

**Give Boosters:**
1. In **Give Booster to User** section:
   - Enter User ID / Username / Email
   - Select Booster Type (2x, 3x, or 5x)
   - Enter Duration in Hours (0.1 to 24)
   - Add Reason/Note (optional)
   - Click **Give Booster**

**View Users:**
1. Scroll to **Users List** section
2. Use search box to find specific users
3. View user details including:
   - User ID, Username, Email, Name
   - Coins Balance
   - Active Booster (if any) with expiry time
   - Join Date

#### Booster System

**How Boosters Work:**
- Boosters multiply mining speed (2x, 3x, or 5x)
- Duration can be set from 0.1 to 24 hours
- Active boosters are displayed in users list
- Boosters expire automatically after duration

**Booster Types:**
- **2x Booster:** Doubles mining speed
- **3x Booster:** Triples mining speed
- **5x Booster:** 5x mining speed multiplier

---

### 4.2. KYC Management

**Location:** `kyc-management.php`

#### Features
- **View KYC Submissions:** See all KYC verification requests
- **Approve/Reject KYC:** Approve or reject user KYC submissions
- **View Details:** See user KYC information and documents

#### How to Use

1. Navigate to **KYC Management** from sidebar
2. View list of KYC submissions
3. Review user information and documents
4. Click **Approve** or **Reject** for each submission
5. Status updates immediately

---

### 4.3. KYC Settings

**Location:** `kyc-settings.php`

#### Features
- **Mining Sessions Required:** Set minimum mining sessions needed for KYC
- **Referrals Required:** Set minimum referrals needed for KYC
- **Other KYC Requirements:** Configure KYC eligibility criteria

#### How to Use

1. Navigate to **KYC Settings** from sidebar
2. Configure KYC requirements:
   - Minimum mining sessions
   - Minimum referrals
   - Other eligibility criteria
3. Click **Update KYC Settings**
4. Changes apply immediately

---

## Key Features Summary

### ✅ Real-Time Updates
- All settings changes apply immediately
- No app update required for most changes
- Settings stored in database and read in real-time

### ✅ Content Management
- News, Tasks, Shop, and Giveaway all have redirect links
- All content can be added, edited, or deleted from admin panel
- Status management (Active/Inactive) for content

### ✅ User Management
- Give coins to users (add or remove)
- Give boosters to users (2x, 3x, 5x multipliers)
- View user list with balances and active boosters
- Search functionality for finding users

### ✅ Settings Management
- Mining speed configuration
- Referral rewards configuration
- Mystery box cooldown and rewards (adjustable without app update)
- User count management (real and fake users)

### ✅ Task System
- 3 Daily Tasks (manually configurable, 24-hour reset, 5-minute timer)
- One-Time Tasks (1-hour timer, automatic reward distribution)
- All tasks have redirect links

---

## Database Structure

All settings are stored in the `settings` table:

- `current_users` - Display user count (fake/manual)
- `goal_users` - Goal user count
- `mining_speed` - Mining speed (coins per hour)
- `base_mining_rate` - Base mining rate
- `max_mining_speed` - Maximum mining speed
- `referrer_reward` - Referrer reward coins
- `referee_reward` - Referee reward coins
- `max_referrals` - Maximum referrals per user
- `bonus_reward` - Bonus reward coins
- `daily_tasks_reset_time` - Daily tasks reset time
- `{box_type}_box_cooldown` - Mystery box cooldown (common, rare, epic, legendary)
- `{box_type}_box_ads` - Ads required for mystery box
- `{box_type}_box_min_coins` - Minimum reward coins
- `{box_type}_box_max_coins` - Maximum reward coins

Content is stored in:
- `news` - News articles
- `social_media_setting` - Tasks (first 3 = daily, rest = one-time)
- `shop` - Shop items
- `giveaway` - Giveaway items

---

## API Integration

All admin panel actions are integrated with backend APIs:

- `/api/admin/news_manage.php` - News management
- `/api/admin/tasks_manage.php` - Tasks management
- `/api/admin/shop_manage.php` - Shop management
- `/api/admin/giveaway_manage.php` - Giveaway management
- `/api/admin/settings_manage.php` - Settings management
- `/api/admin/users_manage.php` - Users and coins management

---

## Security

- **Authentication Required:** All admin pages require login
- **Session Management:** Admin sessions are managed securely
- **Input Validation:** All user inputs are validated and sanitized
- **SQL Injection Protection:** Prepared statements used for database queries

---

## Access

**Login Page:** `login.php`
**Dashboard:** `index.php` (after login)

---

## Support

For technical support or questions about the admin panel, contact the development team.

---

**Last Updated:** Current Date
**Version:** 1.0
**Status:** Production Ready ✅

