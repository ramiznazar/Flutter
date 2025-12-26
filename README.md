# Crutox - Mining Application Backend

## 📋 Project Overview

Crutox is a cryptocurrency mining application backend built with Laravel. The system provides a complete API for mobile applications and a comprehensive admin panel for content and user management. The project was converted from PHP to Laravel framework following MVC architecture and Laravel best practices.

---

## 🏗️ Project Architecture

### Technology Stack
- **Framework:** Laravel 11.x
- **Database:** MySQL
- **Authentication:** Custom token-based (API) + Session-based (Admin)
- **Frontend:** Blade Templates (Admin Panel)
- **API:** RESTful JSON API

### Project Structure
```
├── app/
│   ├── Console/Commands/          # Scheduled tasks (cron jobs)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/               # Public API controllers
│   │   │   │   └── Admin/         # Admin API controllers
│   │   │   └── Admin/             # Admin panel view controllers
│   │   └── Middleware/            # Custom middleware
│   └── Models/                    # Eloquent models (23 models)
├── database/
│   └── migrations/                # Database migrations (38 migrations)
├── resources/
│   └── views/
│       └── admin/                 # Admin panel Blade views
├── routes/
│   ├── api.php                    # API routes
│   └── web.php                    # Web routes (admin panel)
└── php-project/                   # Original PHP files (reference)
```

---

## 🗄️ Database Structure

### Core Tables (23 Models)

#### User Management
- **users** - User accounts, balances, authentication tokens
- **user_levels** - User level progression, mining sessions
- **user_boosters** - Active boosters/multipliers
- **user_guides** - User onboarding status

#### Content Management
- **news** - News articles with images and links
- **shop** - Shop items/giftcards
- **giveaway** - Giveaway campaigns
- **social_media_setting** - Task definitions (daily & one-time)

#### Game Mechanics
- **mystery_box_claims** - Mystery box progress and rewards
- **task_completions** - Task progress and timer tracking
- **spin** - Spin wheel prizes
- **spin_cailmed** - Spin claims history
- **news_likes** - News likes tracking
- **shop_views** - Shop item views tracking

#### System Configuration
- **settings** - All system settings (mining, referrals, mystery box, KYC, etc.)
- **coin_settings** - Coin/mining rate configuration
- **currency** - Currency exchange rates
- **ads_setting** - Advertisement settings
- **spin_setting** - Spin wheel configuration
- **level** - Level definitions
- **badge** - Badge definitions

#### KYC & Admin
- **kyc_submissions** - KYC document submissions
- **admin** - Admin accounts

### Key Features
- ✅ All tables match original SQL structure
- ✅ Dynamic column addition for backward compatibility
- ✅ Timestamps only where originally present
- ✅ Primary keys match original structure (uppercase `ID` where applicable)

---

## 🔌 API Endpoints

### Base URL
```
/api
```

### Authentication Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | User login (returns auth_token) |
| POST | `/signup` | User registration |
| POST | `/create_account` | Account creation (alias) |
| POST | `/otp_request` | Request OTP for password reset |
| POST | `/otp_request_new` | New OTP request |
| POST | `/verify_otp` | Verify OTP code |
| POST | `/verify_otp_and_set_password` | Verify OTP and set password |
| POST | `/change_password` | Change password with old password |
| POST | `/reset_password` | Reset password (triggers OTP flow) |
| GET | `/get_email` | Update OTP for email |

### User Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/get_user_stats` | Get user statistics |
| POST | `/update_profile` | Update user profile |
| POST | `/edit_profile` | Edit profile (alias) |
| POST | `/change_pic` | Change profile picture |
| POST | `/get_team` | Get referral team |
| POST | `/getLevel` | Get user level |
| POST | `/getBadges` | Get user badges |
| POST | `/check_levels` | Check level progression |
| POST | `/update_user_guide` | Update onboarding guide status |
| POST | `/update_user_ping` | Update user ping status |
| POST | `/setup_username` | Setup username |
| POST | `/setup_invite` | Setup invite code |
| POST | `/delete_account_request` | Request account deletion |
| POST | `/reactivate_account` | Reactivate deleted account |

### Mining Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/start_mining` | Start mining session |
| POST | `/start_coin` | Start coin mining |
| POST | `/claim_bonus` | Claim mining bonus |
| POST | `/bonus_history` | Get bonus history |
| POST | `/social_claim` | Claim social media reward |
| POST | `/social_list` | Get social media tasks list |

### Task Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/task_start` | Start a task (enforces timer) |
| POST | `/task_claim_reward` | Claim task reward (validates timer) |
| POST | `/task_track` | Track task interaction |

**Task System Features:**
- Daily tasks: 5-minute timer enforced server-side
- One-time tasks: 1-hour timer enforced server-side
- Automatic reward distribution via scheduled task

### Booster Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/booster_status` | Get active booster status |
| POST | `/booster_claim` | Claim 2x booster (1-hour duration) |

**Booster System:**
- 1-hour duration enforced server-side
- Prevents reuse until expiry
- Automatically deactivates expired boosters

### Mystery Box Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/mystery_box_watch_ad` | Watch ad for mystery box (enforces cooldown) |
| POST | `/mystery_box_click` | Track mystery box click |
| POST | `/mystery_box_open` | Open mystery box (validates ads watched) |

**Mystery Box System:**
- Cooldown between ads enforced server-side
- Configurable per box type (common, rare, epic, legendary)
- Random rewards within min-max range from settings

### KYC Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/kyc_check_eligibility` | Check if user can submit KYC |
| POST | `/kyc_submit` | Submit KYC documents |
| POST | `/submit_kyc` | Submit KYC (alias) |
| POST | `/kyc_get_status` | Get KYC submission status |
| POST | `/get_kyc_progress` | Get KYC progress |

**KYC Requirements:**
- Configurable mining sessions required
- Configurable referrals required
- Per-user KYC submissions

### News Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/get_all_news` | Get all news articles |
| POST | `/get_news` | Get specific news article |
| POST | `/add_news` | Add news (user-facing) |
| POST | `/delete_news` | Delete news (user-facing) |
| POST | `/like_news` | Like/unlike news |
| POST | `/set_news_view` | Track news view |

### Shop Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/get_all_shops` | Get all shop items |
| POST | `/set_shop_view` | Track shop item view |
| POST | `/giftcard_track` | Track giftcard interaction |

### Giveaway Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/get_giveaway` | Get active giveaways |

### Spin Wheel Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/spin` | Spin the wheel |
| POST | `/spin_claim` | Claim spin reward |
| POST | `/get_myspin_info` | Get spin history |

### Settings Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/other_settings` | Get all settings |
| POST | `/get_currencies` | Get currency exchange rates |
| POST | `/getTotalUsers` | Get total user count |
| POST | `/time` | Get server time |
| POST | `/ads` | Get ad settings |

### Utility Endpoints
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/send_notification` | Send push notification (OneSignal) |

### Admin API Endpoints

#### Base URL
```
/api/admin
```

#### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login_admin` | Admin login |

#### Content Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/news_manage` | Get all news |
| POST | `/news_manage` | Create news |
| PUT | `/news_manage/{id}` | Update news |
| DELETE | `/news_manage/{id}` | Delete news |
| GET | `/tasks_manage` | Get tasks (daily/onetime/all) |
| POST | `/tasks_manage` | Create/update tasks |
| PUT | `/tasks_manage/{id}` | Update task |
| DELETE | `/tasks_manage/{id}` | Delete task |
| GET | `/shop_manage` | Get all shop items |
| POST | `/shop_manage` | Create shop item |
| PUT | `/shop_manage/{id}` | Update shop item |
| DELETE | `/shop_manage/{id}` | Delete shop item |
| GET | `/giveaway_manage` | Get all giveaways |
| POST | `/giveaway_manage` | Create giveaway |
| PUT | `/giveaway_manage/{id}` | Update giveaway |
| DELETE | `/giveaway_manage/{id}` | Delete giveaway |

#### Settings Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/settings_manage` | Get all settings |
| POST | `/settings_manage` | Update settings |
| PUT | `/settings_manage` | Update settings (alias) |
| GET | `/coin_speed_overall` | Get overall coin speed |
| POST | `/coin_speed_overall` | Update overall coin speed |

#### User Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users_manage` | Search users |
| POST | `/users_manage/give_coins` | Give coins to user |
| POST | `/users_manage/give_booster` | Give booster to user |
| GET | `/users_manage/stats` | Get user stats (mining sessions, referrals) |
| POST | `/users_manage/stats` | Update user stats |
| GET | `/users_manage/coin_speed` | Get user's custom coin speed |
| POST | `/users_manage/coin_speed` | Update user's custom coin speed |
| POST | `/mystery_box_reset` | Reset user's mystery box data |
| POST | `/user_stats_manage` | Manage user stats (GET/POST) |

#### KYC Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/kyc_manage` | Get KYC submissions |
| PUT | `/kyc_manage/{id}` | Update KYC status |

---

## 🖥️ Admin Panel

### Access
```
/admin/login
```

### Default Credentials (Hardcoded)
- **Email:** admin@crutox.com
- **Password:** admin$$$@@@

### Admin Panel Features

#### Dashboard
- User count management
- System overview statistics

#### News Management (`/admin/news`)
- Create, read, update, delete news articles
- Upload images
- Add redirect links
- Manage status (active/inactive)

#### Tasks Management (`/admin/tasks`)
- Manage daily tasks (first 3 tasks)
- Manage one-time tasks
- Set rewards, redirect links, icons
- Configure task types

#### Shop Management (`/admin/shop`)
- Create, update, delete shop items
- Manage prices and descriptions
- Upload images
- Add redirect links

#### Giveaway Management (`/admin/giveaway`)
- Create, update, delete giveaways
- Set start/end dates
- Configure rewards and redirect links
- Manage status

#### Settings Management
- **Mining Settings** (`/admin/mining-settings`)
  - Mining speed
  - Base mining rate
  - Max mining speed
  
- **Referral Settings** (`/admin/referral-settings`)
  - Referrer reward
  - Referee reward
  - Max referrals
  - Bonus reward threshold
  
- **Mystery Box Settings** (`/admin/mystery-box`)
  - Cooldown periods per box type
  - Ads required per box type
  - Min/max coin rewards per box type
  
- **KYC Settings** (`/admin/kyc-settings`)
  - Required mining sessions
  - Required referrals

#### Users Management (`/admin/users`)
- Search users by email, username, or ID
- View user balances
- Give coins to users
- Give boosters to users
- Reset mystery box data
- View user stats (mining sessions, referrals)
- Manage custom coin speed per user

#### KYC Management (`/admin/kyc`)
- View all KYC submissions
- Update KYC status (pending/approved/rejected)
- View KYC documents

#### Profile Management (`/admin/profile`)
- Update admin profile
- Change username, name, email
- Change password

---

## ⚙️ System Configuration

### Settings Table Structure

All system settings are stored in the `settings` table and can be managed via admin panel:

#### Mining Settings
- `mining_speed` - Mining speed multiplier
- `base_mining_rate` - Base mining rate
- `max_mining_speed` - Maximum mining speed

#### Referral Settings
- `referrer_reward` - Reward for referrer
- `referee_reward` - Reward for referee
- `max_referrals` - Maximum referrals allowed
- `bonus_reward` - Bonus reward threshold

#### User Count Progress
- `current_users` - Current user count
- `goal_users` - Target user count

#### Mystery Box Settings (Per Box Type)
- `{box_type}_box_cooldown` - Cooldown in minutes (common, rare, epic, legendary)
- `{box_type}_box_ads` - Number of ads required
- `{box_type}_box_min_coins` - Minimum reward coins
- `{box_type}_box_max_coins` - Maximum reward coins

#### KYC Settings
- `kyc_mining_sessions` - Required mining sessions (default: 14)
- `kyc_referrals_required` - Required referrals (default: 10)

#### Task Settings
- `daily_tasks_reset_time` - Last reset time for daily tasks

#### Ad Waterfall Settings
- `ad_waterfall_order` - JSON array of ad providers
- `ad_waterfall_enabled` - Enable/disable waterfall

---

## 🔄 Scheduled Tasks (Cron Jobs)

### Daily Tasks Reset
**Command:** `php artisan tasks:daily-reset`  
**Schedule:** Daily at midnight  
**Purpose:** Resets daily tasks every 24 hours

### Task Rewards Distribution
**Command:** `php artisan tasks:distribute-rewards`  
**Schedule:** Every 5 minutes  
**Purpose:** Automatically distributes rewards for one-time tasks after 1 hour

### Setup Cron Job
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🚀 Setup Instructions

### 1. Prerequisites
- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Node.js and NPM (for assets)

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Configure database connection in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crutox
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Import Database (Optional)
If you have SQL backup:
```bash
mysql -u username -p database_name < php-project/mining/crutox_backup_reduced.sql
```

**Note:** Some migrations add columns dynamically. If importing SQL backup, ensure migrations run after import.

### 7. Copy Admin Assets
```bash
# Windows PowerShell
xcopy /E /I "php-project\mining\admin\assets" "public\assets\admin"

# Linux/Mac
cp -r php-project/mining/admin/assets/* public/assets/admin/
```

### 8. Create Admin User
Via SQL:
```sql
INSERT INTO `admin` (`username`, `email`, `password`, `name`, `created_at`, `updated_at`) 
VALUES ('admin', 'admin@crutox.com', '$2y$10$...', 'Admin User', NOW(), NOW());
```

Or use the hardcoded credentials:
- Email: `admin@crutox.com`
- Password: `admin$$$@@@`

### 9. Setup Scheduled Tasks
Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Start Development Server
```bash
php artisan serve
```

Access:
- Admin Panel: `http://localhost:8000/admin/login`
- API: `http://localhost:8000/api`

---

## 🔒 Security Features

- ✅ CSRF protection on all forms
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Blade escaping)
- ✅ Password hashing (bcrypt for admin, plain text for users - matches original)
- ✅ Admin authentication middleware
- ✅ Input validation
- ✅ CORS configuration
- ✅ Token-based API authentication

---

## 📊 Project Statistics

- **Total Models:** 23
- **Total Migrations:** 38
- **Total API Controllers:** 21 (13 public + 8 admin)
- **Total Admin View Controllers:** 10
- **Total Blade Views:** 20+
- **Total Console Commands:** 2
- **Total API Routes:** 60+
- **Total Web Routes:** 30+
- **Total Middleware:** 2 custom (AdminAuth, ApiAuth)

---

## 🔑 Key Features

### Backend Logic Enforcement
- ✅ **Timer Enforcement:** All task timers enforced server-side
- ✅ **Cooldown Enforcement:** Mystery box and booster cooldowns enforced server-side
- ✅ **Reward Distribution:** All rewards validated before distribution
- ✅ **Daily Task Reset:** Automatic reset every 24 hours
- ✅ **Dynamic Settings:** All settings configurable from admin panel

### User Management
- ✅ Individual user coin speed control
- ✅ Mining sessions and referrals management
- ✅ Booster assignment
- ✅ Mystery box data reset
- ✅ Account status management

### Content Management
- ✅ News articles with images and links
- ✅ Shop items with prices and descriptions
- ✅ Giveaway campaigns with date ranges
- ✅ Task management (daily and one-time)
- ✅ All content manageable via admin panel

---

## 📝 Important Notes

1. **Database Structure:** All migrations match the original SQL backup structure
2. **Backward Compatibility:** API endpoints maintain similar structure to original PHP APIs
3. **Dynamic Columns:** Some columns are added dynamically to match original PHP behavior
4. **Timestamps:** Only added where they exist in the original SQL
5. **Primary Keys:** Some tables use uppercase `ID` to match original structure
6. **Password Storage:** Users use plain text passwords (matches original), admins use bcrypt

---

## 🐛 Troubleshooting

### Common Issues

1. **Column not found errors:**
   - Run migrations: `php artisan migrate`
   - Some columns are added dynamically by controllers

2. **Admin assets not loading:**
   - Copy assets: `xcopy /E /I "php-project\mining\admin\assets" "public\assets\admin"`

3. **Route not found:**
   - Clear route cache: `php artisan route:clear`

4. **Database connection errors:**
   - Check `.env` file configuration
   - Verify database exists and credentials are correct

5. **Migration errors:**
   - Check if tables already exist
   - Some migrations add columns conditionally

---

## 📞 Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database connection in `.env`
3. Ensure all migrations have run successfully
4. Check that assets are copied correctly
5. Verify scheduled tasks are running

---

## 📄 License

This project is proprietary software.

---

**Last Updated:** December 2025  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
