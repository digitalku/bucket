# Digitalku Bucket

A private media file hosting app. Log in, upload images or videos, get a direct URL and Markdown link instantly.

**Built with:** Laravel 13 · PHP 8.3 · MariaDB

---

## Features

- Drag & drop upload (images and videos only)
- Paste from clipboard (Ctrl+V)
- Direct URL and Markdown link for each file
- Per-user file gallery with delete
- Admin panel: manage users, files, and 2FA
- Two-factor authentication (TOTP) per user
- File ownership transfer between users

---

## Requirements

- PHP 8.3+
- Composer
- MariaDB / MySQL (or SQLite for local dev)
- A web server (nginx or Apache)
- Node.js + npm (for building frontend assets)

---

## Installation

### Option A — Shared Hosting / cPanel

> Best for deploying to `bucket.digitalku.com` on a cPanel server.

#### 1. Upload the project

Upload the full project to your domain's folder, e.g.:

```
/home/cpaneluser/bucket.digitalku.com/
```

#### 2. Create the database

In cPanel → **MySQL Databases**, create a database and user, then grant all privileges.

#### 3. Configure environment

Copy `.env.example` to `.env` and fill in your values:

```bash
cp .env.example .env
```

```env
APP_NAME="Digitalku Bucket"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bucket.digitalku.com

MAX_UPLOAD_MB=200

DB_CONNECTION=mariadb
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

FILESYSTEM_DISK=public
```

#### 4. Install dependencies (via SSH)

```bash
cd /home/cpaneluser/bucket.digitalku.com
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

#### 5. Generate app key

```bash
php artisan key:generate
```

#### 6. Create required directories

These directories are not included in the repository and must be created manually:

```bash
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p bootstrap/cache

chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

#### 7. Run migrations

```bash
php artisan migrate --force
```

#### 8. Create the storage symlink

```bash
php artisan storage:link
```

#### 9. Create the first admin account

```bash
php artisan db:seed
```

Follow the prompts to set a username and password.

#### 10. Cache config and routes

```bash
php artisan config:cache
php artisan route:cache
```

#### 11. Set up the .htaccess

Rename `.htaccess.cpanel.example` to `.htaccess` at the document root:

```
/home/cpaneluser/bucket.digitalku.com/.htaccess
```

> The `public/.htaccess` is already correct — do not modify it.

---

### Option B — Local Development (DDEV)

> Best for development on your own machine.

#### 1. Clone the repository

```bash
git clone https://github.com/your-username/bucket.git
cd bucket
```

#### 2. Start DDEV

```bash
ddev start
```

#### 3. Install dependencies

```bash
ddev exec composer install
ddev exec npm install
ddev exec npm run build
```

#### 4. Configure environment

```bash
cp .env.example .env
```

The default `.env.example` is pre-configured for DDEV (MariaDB at `db:3306`). Just generate the app key:

```bash
ddev exec php artisan key:generate
```

#### 5. Run migrations

```bash
ddev exec php artisan migrate
```

#### 6. Create the storage symlink

```bash
ddev exec php artisan storage:link
```

#### 7. Create the first admin account

```bash
ddev ssh
php artisan db:seed
```

Follow the prompts to set a username and password.

#### 8. Open the app

```
https://bucket.ddev.site
```

---

### Option C — Standard VPS / nginx

#### 1. Clone and install

```bash
git clone https://github.com/your-username/bucket.git /var/www/bucket
cd /var/www/bucket

composer install --no-dev --optimize-autoloader
npm install && npm run build
```

#### 2. Configure environment

```bash
cp .env.example .env
# Edit .env with your database and app settings
php artisan key:generate
```

#### 3. Set up storage

```bash
mkdir -p storage/framework/{sessions,views,cache/data} bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link
```

#### 4. Run migrations and seed

```bash
php artisan migrate --force
php artisan db:seed        # first time only
php artisan config:cache
php artisan route:cache
```

#### 5. Configure nginx

Point your nginx `root` to the `public/` directory:

```nginx
server {
    listen 80;
    server_name bucket.digitalku.com;
    root /var/www/bucket/public;
    index index.php;

    client_max_body_size 205M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Upload size limit

The max upload size is controlled by **three layers** that must all match:

| Layer | Setting |
|---|---|
| `.env` | `MAX_UPLOAD_MB=200` |
| `php.ini` | `upload_max_filesize = 200M` and `post_max_size = 205M` |
| nginx / Apache | `client_max_body_size 205M` |

If they are out of sync, the admin dashboard will show a warning.

---

## Storage backends

**Local disk** (`FILESYSTEM_DISK=public`) — default. Files stored in `storage/app/public/YYYY/MM/`. Requires `php artisan storage:link`.

**Amazon S3** (`FILESYSTEM_DISK=s3`) — set the following in `.env`:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket
AWS_FOLDER=bucket
```

S3 bucket must have a public read policy. IAM user needs `s3:PutObject` and `s3:DeleteObject` permissions. `storage:link` is not needed when using S3.

---

## Troubleshooting

**`Please provide a valid cache path`**
The `storage/framework/` subdirectories are missing. Run step 6 from the cPanel install guide above.

**Blank page / HTTP 500**
Set `APP_DEBUG=true` temporarily to see the error. Remember to set it back to `false` in production.

**Uploads failing silently**
Check that `MAX_UPLOAD_MB`, `upload_max_filesize`, and `client_max_body_size` all match. The admin dashboard shows a warning if they don't.

**`storage:link` fails on cPanel**
Run it via SSH. Some cPanel hosts block symlinks — in that case use S3 as the storage backend instead.

---

## License

Copyright [Digitalku](https://www.digitalku.com). You may use and modify this software, but you must retain the copyright notice.
