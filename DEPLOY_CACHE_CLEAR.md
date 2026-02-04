# Clear cache on live server after deploy

Run these commands **on the live server** (in your project root, e.g. `/var/www/my_gamez`) so API changes take effect and responses are not cached:

```bash
# 1. Clear Laravel config cache (so new code/config is used)
php artisan config:clear

# 2. Clear Laravel application cache
php artisan cache:clear

# 3. Clear compiled views (if you use Blade caching)
php artisan view:clear

# 4. Restart PHP-FPM so all workers pick up new code (use your server's command)
# Ubuntu/Debian with PHP 8.1:
sudo systemctl restart php8.1-fpm
# Or:
# sudo service php-fpm restart
```

**One-liner** (run from project root):

```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

Then restart PHP-FPM (or your web server) so in-memory caches are cleared.

**Note:** Login and `GET /api/admin/users_manage?email=...` now send `Cache-Control: no-store, no-cache` so browsers and CDNs do not cache these responses.
