# Crutox Backend: Production Deployment (Micro Architecture)

This guide describes how to run the Crutox backend (monolith + 4 microservices) on **three Ubuntu servers** in production. It includes which server runs what, how to install required software on fresh Ubuntu, and essential commands.

**Security:** Do not commit `.env` or passwords to Git. Use SSH keys for server access. Store `INTERNAL_API_SECRET` and DB passwords in `.env` on each server only.

---

## 1. Server allocation

| Server | Role | What runs | Public? |
|--------|------|-----------|--------|
| **Server 1 – Main** (76.13.18.104) | Gateway + DB | Monolith (API gateway, auth, user, admin) + **MySQL** | Yes – only this server gets public API traffic |
| **Server 2** (72.60.194.136) | Services A | Mining service + Task service | No – internal only |
| **Server 3** (76.13.193.147) | Services B | Gamification service + KYC service | No – internal only |

- **Flutter app** and any external client call only **Server 1** (e.g. `https://api.crutox.com` or your domain pointing to 76.13.18.104).
- Server 1 forwards mining/task/gamification/KYC requests to Server 2 and Server 3 over **private HTTP** (use internal IPs or firewall so only Server 1 can reach 2 and 3).

**Optional:** If you prefer to keep the current app unchanged on Main and add services gradually, you can run **monolith + mining + task** on Server 1 and only **gamification + KYC** on Server 2 (leave Server 3 for later). The guide below assumes the 3-server split above.

---

## 2. Server access (reference)

Use SSH keys in production. Replace with your actual user/host.

| Server | SSH (reference) | Notes |
|--------|------------------|--------|
| Main | `ssh root@76.13.18.104` | Current app lives here; MySQL here |
| Server 2 | `ssh root@72.60.194.136` | Fresh Ubuntu – install stack below |
| Server 3 | `ssh root@76.13.193.147` | Fresh Ubuntu – install stack below |

**Do not put SSH passwords in this file or in Git.** Store them in a password manager. Prefer `ssh-copy-id` and key-based login.

---

## 3. Install software on Ubuntu (Servers 2 and 3, and Main if needed)

Run as `root` or with `sudo`. These steps are for **Ubuntu 22.04 or 24.04 LTS**.

### 3.1 Update system

```bash
sudo apt update && sudo apt upgrade -y
```

### 3.2 Install Git

```bash
sudo apt install -y git
git --version
```

### 3.3 Install PHP 8.1+ and extensions (Laravel)

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

# PHP 8.1 and Laravel-related extensions
sudo apt install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-bcmath php8.1-tokenizer php8.1-json

php -v
```

### 3.4 Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 3.5 Install Nginx (for serving Laravel)

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl status nginx
```

### 3.6 On Server 1 (Main) only: MySQL server

If MySQL is **only** on Main:

```bash
sudo apt install -y mysql-server
sudo systemctl enable mysql
sudo mysql_secure_installation
```

Create database and user:

```bash
sudo mysql -e "CREATE DATABASE IF NOT EXISTS my_gamez;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'crutox'@'%' IDENTIFIED BY 'YOUR_SECURE_DB_PASSWORD';"
sudo mysql -e "GRANT ALL ON my_gamez.* TO 'crutox'@'%'; FLUSH PRIVILEGES;"
```

Use a strong password and put it in each app’s `.env` as `DB_PASSWORD`. If your app user is `root` and only local, adjust user/host accordingly.

### 3.7 On Servers 2 and 3: MySQL client only (optional)

If DB is on Main, other servers only need the client to run `php artisan` commands that use DB (e.g. migrate from monolith is run on Main; services just need to connect):

```bash
sudo apt install -y php8.1-mysql
# DB runs on Main; no mysql-server needed on 2 and 3
```

---

## 4. Deploy the application

### 4.1 Server 1 (Main) – Monolith + MySQL

1. **Clone or upload the repo** (e.g. under `/var/www/crutox`):

```bash
cd /var/www
sudo git clone YOUR_REPO_URL crutox
# or upload via rsync/scp from your machine
```

2. **Install dependencies (monolith only):**

```bash
cd /var/www/crutox
composer install --no-dev --optimize-autoloader
```

3. **Environment:**

```bash
cp .env.example .env
php artisan key:generate
nano .env   # or vim
```

Set at least:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-api-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_gamez
DB_USERNAME=crutox
DB_PASSWORD=YOUR_SECURE_DB_PASSWORD

INTERNAL_API_SECRET=GENERATE_A_STRONG_SECRET_AND_USE_IT_ON_ALL_SERVERS

MINING_SERVICE_ENABLED=true
MINING_SERVICE_URL=http://72.60.194.136
MINING_SERVICE_TIMEOUT=15

TASK_SERVICE_ENABLED=true
TASK_SERVICE_URL=http://72.60.194.136:8002
TASK_SERVICE_TIMEOUT=15

GAMIFICATION_SERVICE_ENABLED=true
GAMIFICATION_SERVICE_URL=http://76.13.193.147:8003
GAMIFICATION_SERVICE_TIMEOUT=15

KYC_SERVICE_ENABLED=true
KYC_SERVICE_URL=http://76.13.193.147:8004
KYC_SERVICE_TIMEOUT=15
```

Use **internal IPs** for `*_SERVICE_URL` if your provider gives you private networking (e.g. 10.x.x.x) so traffic does not go over the public internet. Otherwise use public IPs and restrict firewall (see below).

4. **Run migrations (once):**

```bash
php artisan migrate --force
```

5. **Nginx** – point document root to `public`:

Create `/etc/nginx/sites-available/crutox` (example):

```nginx
server {
    listen 80;
    server_name your-api-domain.com;
    root /var/www/crutox/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

Enable and reload:

```bash
sudo ln -s /etc/nginx/sites-available/crutox /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

6. **Permissions:**

```bash
sudo chown -R www-data:www-data /var/www/crutox
sudo chmod -R 755 /var/www/crutox
sudo chmod -R 775 /var/www/crutox/storage /var/www/crutox/bootstrap/cache
```

---

### 4.2 Server 2 – Mining + Task

One option is to run two PHP built-in servers behind Nginx (reverse proxy), or use two Nginx + PHP-FPM vhosts. Below: **two ports (8001, 8002)** with Nginx proxying to PHP-FPM for each app.

1. **Clone full repo** (so you have both `services/mining` and `services/task`):

```bash
sudo mkdir -p /var/www/crutox-services
cd /var/www/crutox-services
sudo git clone YOUR_REPO_URL repo
cd repo
```

2. **Mining service:**

```bash
cd /var/www/crutox-services/repo/services/mining
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env
```

`.env` (mining):

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=76.13.18.104
DB_PORT=3306
DB_DATABASE=my_gamez
DB_USERNAME=crutox
DB_PASSWORD=YOUR_SECURE_DB_PASSWORD

INTERNAL_API_SECRET=SAME_AS_ON_MAIN
```

3. **Task service:**

```bash
cd /var/www/crutox-services/repo/services/task
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env
```

Set same `DB_*` and `INTERNAL_API_SECRET` as mining.

4. **Nginx for Server 2** – proxy to two PHP-FPM pools (mining on 8001, task on 8002).

Example: use **PHP built-in** for simplicity (or add PHP-FPM pools per app).

**Option A – PHP built-in (quick):**

```bash
cd /var/www/crutox-services/repo/services/mining
sudo -u www-data php artisan serve --host=0.0.0.0 --port=8001
# Keep this running (e.g. with systemd or screen)
```

Second terminal:

```bash
cd /var/www/crutox-services/repo/services/task
sudo -u www-data php artisan serve --host=0.0.0.0 --port=8002
```

**Option B – Nginx + PHP-FPM** (recommended for production): create two vhosts, each with `root` pointing to the service’s `public` and `fastcgi_pass` to a dedicated FPM pool (e.g. mining pool, task pool) listening on a socket or port. Then on Main, set:

- `MINING_SERVICE_URL=http://72.60.194.136` (port 80 if Nginx vhost for mining is default) or `http://72.60.194.136:8001` if you proxy to 8001.
- `TASK_SERVICE_URL=http://72.60.194.136:8002` (or the URL of the task vhost).

5. **Permissions:**

```bash
sudo chown -R www-data:www-data /var/www/crutox-services/repo/services/mining
sudo chown -R www-data:www-data /var/www/crutox-services/repo/services/task
sudo chmod -R 775 storage bootstrap/cache
```

---

### 4.3 Server 3 – Gamification + KYC

Same idea as Server 2.

1. **Clone repo** (or rsync from Main):

```bash
sudo mkdir -p /var/www/crutox-services
cd /var/www/crutox-services
sudo git clone YOUR_REPO_URL repo
cd repo
```

2. **Gamification:**

```bash
cd /var/www/crutox-services/repo/services/gamification
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env
```

Set `DB_HOST=76.13.18.104`, same `DB_*` and `INTERNAL_API_SECRET`.

3. **KYC:**

```bash
cd /var/www/crutox-services/repo/services/kyc
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env
```

Add:

```env
GATEWAY_PUBLIC_URL=https://your-api-domain.com
```

Same `DB_*` and `INTERNAL_API_SECRET`.

4. Run **gamification** on 8003 and **KYC** on 8004 (PHP built-in or Nginx + FPM as on Server 2). On Main:

- `GAMIFICATION_SERVICE_URL=http://76.13.193.147:8003`
- `KYC_SERVICE_URL=http://76.13.193.147:8004`

5. **Permissions** as above for both apps.

---

## 5. Firewall and security

- **Server 1 (Main):** Open 80/443 for Nginx; restrict 22 (SSH) to your IP if possible.
- **Servers 2 and 3:** Open only what the Main server needs to call (e.g. 8001, 8002 on Server 2; 8003, 8004 on Server 3). Restrict those ports to **Main server IP (76.13.18.104)** only so the world cannot hit your services.

Example (ufw) on **Server 2**:

```bash
sudo ufw allow from 76.13.18.104 to any port 8001
sudo ufw allow from 76.13.18.104 to any port 8002
sudo ufw allow 22
sudo ufw enable
```

On **Server 3**:

```bash
sudo ufw allow from 76.13.18.104 to any port 8003
sudo ufw allow from 76.13.18.104 to any port 8004
sudo ufw allow 22
sudo ufw enable
```

Use **private/internal IPs** for service URLs and firewall rules if your provider supports them.

---

## 6. Run services persistently (systemd)

So mining/task/gamification/KYC keep running after logout, use systemd.

Example for **Mining** on Server 2 (`/etc/systemd/system/crutox-mining.service`):

```ini
[Unit]
Description=Crutox Mining Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/crutox-services/repo/services/mining
ExecStart=/usr/bin/php artisan serve --host=0.0.0.0 --port=8001
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Then:

```bash
sudo systemctl daemon-reload
sudo systemctl enable crutox-mining
sudo systemctl start crutox-mining
sudo systemctl status crutox-mining
```

Create similar units for task (8002), gamification (8003), kyc (8004) on the correct server. Adjust paths and ports.

---

## 7. SSL (HTTPS) on Main

Only the Main server needs to be public and should use HTTPS. Example with Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-api-domain.com
```

Set `APP_URL=https://your-api-domain.com` in Main’s `.env`.

---

## 8. Quick reference: which server runs what

| Server | IP | Apps | Ports |
|--------|-----|------|--------|
| Main | 76.13.18.104 | Monolith (Nginx + PHP-FPM), MySQL | 80, 443 |
| Server 2 | 72.60.194.136 | Mining, Task | 8001, 8002 |
| Server 3 | 76.13.193.147 | Gamification, KYC | 8003, 8004 |

---

## 9. Basic server commands

### SSH

```bash
ssh root@76.13.18.104
ssh root@72.60.194.136
ssh root@76.13.193.147
```

### System

```bash
sudo systemctl status nginx
sudo systemctl restart nginx
sudo systemctl status php8.1-fpm
sudo systemctl restart php8.1-fpm
sudo systemctl status mysql
```

### App (on Main)

```bash
cd /var/www/crutox
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Services (Server 2 / Server 3)

```bash
sudo systemctl status crutox-mining
sudo systemctl restart crutox-mining
sudo systemctl status crutox-task
sudo systemctl restart crutox-task
# same for crutox-gamification, crutox-kyc on Server 3
```

### Deploy update (pull code)

**Main:**

```bash
cd /var/www/crutox
git pull
composer install --no-dev --optimize-autoloader
php artisan config:cache
sudo systemctl reload php8.1-fpm
```

**Server 2:**

```bash
cd /var/www/crutox-services/repo
git pull
cd services/mining && composer install --no-dev --optimize-autoloader
cd ../task && composer install --no-dev --optimize-autoloader
sudo systemctl restart crutox-mining crutox-task
```

**Server 3:** Same pattern for `gamification` and `kyc`.

### Logs

```bash
tail -f /var/www/crutox/storage/logs/laravel.log
tail -f /var/log/nginx/error.log
journalctl -u crutox-mining -f
```

---

## 10. Checklist

- [ ] Ubuntu updated; Git, PHP 8.1+, Composer, Nginx installed on all servers.
- [ ] MySQL on Main; database `my_gamez` and user created; migrations run from monolith.
- [ ] Monolith on Main; `.env` with production settings and service URLs pointing to Server 2 and 3.
- [ ] Same `INTERNAL_API_SECRET` in monolith and every service `.env`.
- [ ] Mining + Task on Server 2; Gamification + KYC on Server 3; each with correct `DB_*` and `INTERNAL_API_SECRET`.
- [ ] KYC service has `GATEWAY_PUBLIC_URL` set to the public API URL (Main).
- [ ] Firewall: only Main public (80/443); 8001–8004 on Server 2 and 3 restricted to Main IP.
- [ ] systemd units for mining, task, gamification, KYC so they restart on reboot.
- [ ] SSL on Main (Certbot); `APP_URL` and Flutter app point to `https://your-api-domain.com`.

Use this file as the single reference for running the Crutox micro-architecture backend in production on your three servers.
