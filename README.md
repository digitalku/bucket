# Bucket

A private media file hosting app. Log in, upload images or videos, get a direct URL and Markdown link instantly.

**Built with:** Laravel 13 · PHP 8.3 · MariaDB

---

## Requirements

- PHP 8.3+
- Composer
- MariaDB or MySQL
- A web server (nginx or Apache)

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/bucket.git
cd bucket
```

### 2. Install dependencies

```bash
composer install
npm install
npm run build
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your values:

```env
APP_URL=https://your-domain.com
MAX_UPLOAD_MB=200

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Create the storage symlink

```bash
php artisan storage:link
```

### 6. Create the first admin account

```bash
php artisan db:seed
```

Follow the prompts to set a username and password.

---

## Upload size limit

The max upload size is controlled by **three layers** that must all match:

| Layer | Setting |
|---|---|
| `.env` | `MAX_UPLOAD_MB=200` |
| `php.ini` | `upload_max_filesize = 200M` and `post_max_size = 205M` |
| nginx | `client_max_body_size 205M;` |

If they are out of sync, the admin dashboard will show a warning.

---

## Production deployment

```bash
# After uploading files to the server:
php artisan migrate --force
php artisan storage:link
php artisan db:seed          # first time only
php artisan config:cache
php artisan route:cache
```

Set in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

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

## License

Copyright [Digitalku](https://www.digitalku.com). You may use and modify this software, but you must retain the copyright notice.
