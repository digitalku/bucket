# Digitalku Bucket — CLAUDE.md

Project guide for AI assistants. Read this before making any changes.

## What This Project Is

**Digitalku Bucket** — a private media file hosting app. Users log in to upload images/videos, get a direct URL and Markdown link. Will be deployed at `bucket.digitalku.com`.

## Stack

| Layer | Tech |
|---|---|
| Framework | Laravel 13 (latest) |
| Language | PHP 8.3 (inside DDEV container) |
| Database | MariaDB 10.11 |
| Local dev | DDEV → `https://bucket.ddev.site` |
| Auth | Custom session-based (NOT Laravel's built-in Auth facade) |
| 2FA | `pragmarx/google2fa` + `bacon/bacon-qr-code` |
| Storage | Configurable: local disk (`public`) or Amazon S3 — set via `FILESYSTEM_DISK` in `.env` |

## Local Dev Commands

```bash
ddev start           # start the project
ddev stop            # stop
ddev restart         # restart (required after changing .ddev/php/php.ini or nginx)
ddev ssh             # open shell inside web container
ddev exec php artisan <cmd>   # run artisan commands
ddev exec composer <cmd>      # run composer
```

Run migrations:
```bash
ddev exec php artisan migrate
```

Create first admin (interactive, run inside `ddev ssh`):
```bash
php artisan db:seed
```

## Key Configuration

### `.env` (important keys)
```
APP_NAME="Digitalku Bucket"
APP_URL=https://bucket.ddev.site       # change to https://bucket.digitalku.com in production
MAX_UPLOAD_MB=200                       # adjustable — also update php.ini and nginx if raised
DB_CONNECTION=mariadb
DB_HOST=db
DB_DATABASE=db
DB_USERNAME=db
DB_PASSWORD=db

FILESYSTEM_DISK=public                 # local storage — change to 's3' to use Amazon S3

# S3 (only required when FILESYSTEM_DISK=s3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=digitalku                   # S3 bucket name
AWS_FOLDER=bucket                      # folder prefix inside the bucket (all files go under this)
```

### Storage modes

**Local** (`FILESYSTEM_DISK=public`):
- Files stored at `storage/app/public/YYYY/MM/filename.ext`
- Public URL: `https://bucket.ddev.site/storage/2026/06/photo.jpg`
- Requires `php artisan storage:link` symlink

**Amazon S3** (`FILESYSTEM_DISK=s3`):
- Files stored at `s3://AWS_BUCKET/AWS_FOLDER/YYYY/MM/filename.ext`
- Public URL: `https://digitalku.s3.ap-southeast-1.amazonaws.com/bucket/2026/06/photo.jpg`
- S3 bucket must have a public read policy
- IAM user needs `s3:PutObject` + `s3:DeleteObject` permissions
- `AWS_FOLDER` is configurable — all files are prefixed with this folder name

The `path` column in the `files` table always stores `YYYY/MM/filename.ext` (no folder prefix). The disk config handles the prefix transparently via the `root` option in `config/filesystems.php`.

### Upload size — 3 layers must all match
If you raise `MAX_UPLOAD_MB`, also update:
1. `.ddev/php/php.ini` → `upload_max_filesize` and `post_max_size`
2. `.ddev/nginx_full/nginx-site.conf` → `client_max_body_size`
3. Then run `ddev restart`

The admin dashboard shows a warning if the layers are out of sync.

## Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| username | string unique | login identifier |
| password | string | bcrypt hashed |
| role | enum | `admin` or `user` |
| totp_secret | string nullable | TOTP secret key |
| totp_enabled | boolean | default false |
| created_at / updated_at | timestamp | |

### `files`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | cascade delete |
| filename | string | stored filename (may have `-2` suffix) |
| original_name | string | original upload name |
| path | string | relative path e.g. `2026/06/photo.jpg` |
| mime_type | string | validated MIME |
| size | bigint | bytes |
| created_at / updated_at | timestamp | |

## File Storage

Storage backend is controlled by `FILESYSTEM_DISK` in `.env` (`public` = local, `s3` = Amazon S3).

All controllers use `Storage::disk()` (no hardcoded disk name) so switching is one env change.

`File::publicUrl()` uses `Storage::disk()->url($this->path)` — returns the correct URL for whichever disk is active.

The `path` column stores `YYYY/MM/filename.ext` only — no disk-specific prefix. The S3 folder prefix (`AWS_FOLDER`) is applied transparently via the `root` option in `config/filesystems.php`.

**No auto-cleanup** — files are permanent until manually deleted.

Duplicate filenames get a suffix: `photo.jpg` → `photo-2.jpg` → `photo-3.jpg`.

## Auth System

**Custom session-based auth** — does NOT use `Auth::user()` or `auth()` helper.

Session keys set on login:
```php
session('auth_user_id')    // int
session('auth_username')   // string
session('auth_role')       // 'admin' or 'user'
```

### 2FA Login Flow
1. Username + password → if `totp_enabled`, redirect to `/login/2fa`
2. Session key `totp_pending_user_id` holds user ID during TOTP step
3. Valid TOTP code → clear pending key → set auth session keys

### Middleware
- `RequireAuth` — checks `auth_user_id` in session, redirects to `/login`
- `RequireAdmin` — checks `auth_role`, aborts 403 if not admin

## File & Folder Structure

```
app/
  Http/
    Controllers/
      AuthController.php       # login, TOTP step, logout
      UploadController.php     # upload page + AJAX upload endpoint
      GalleryController.php    # user's own file gallery + delete
      ProfileController.php    # user change own password
      Admin/
        DashboardController.php  # admin stats + config warning
        UserController.php       # create/delete users, change password
        FileController.php       # view all files, delete, change owner
        TotpController.php       # generate secret, verify, enable, disable, reset
    Middleware/
      RequireAuth.php
      RequireAdmin.php
  Models/
    User.php   # role, totp_secret, totp_enabled, isAdmin(), files()
    File.php   # publicUrl(), markdownLink()

resources/views/
  layouts/app.blade.php      # main layout with nav
  auth/
    login.blade.php
    totp.blade.php
  upload.blade.php
  gallery.blade.php
  profile/
    password.blade.php
  admin/
    dashboard.blade.php
    users/
      index.blade.php
      create.blade.php
      change-password.blade.php
      2fa.blade.php
    files/
      index.blade.php

routes/web.php               # all routes defined here

database/
  migrations/
    ..._create_users_table.php
    ..._create_cache_table.php
    ..._create_jobs_table.php
    ..._create_files_table.php
  seeders/
    DatabaseSeeder.php       # creates first admin interactively

.ddev/
  config.yaml                # name: bucket, type: laravel, php: 8.3
  php/php.ini                # upload_max_filesize = 200M
  nginx_full/nginx-site.conf # client_max_body_size 205M
```

## Routes Summary

| Method | URI | Name | Description |
|---|---|---|---|
| GET | `/login` | `login` | Login page |
| POST | `/login` | `login.post` | Process login |
| GET | `/login/2fa` | `login.totp` | TOTP input page |
| POST | `/login/2fa` | `login.totp.post` | Verify TOTP |
| POST | `/logout` | `logout` | Logout |
| GET | `/` | `upload` | Upload page (auth) |
| POST | `/upload` | `upload.store` | AJAX upload endpoint |
| GET | `/gallery` | `gallery` | User's gallery (auth) |
| DELETE | `/files/{file}` | `files.destroy` | Delete own file |
| GET | `/profile/password` | `profile.password` | Change own password |
| POST | `/profile/password` | `profile.password.update` | Save new password |
| GET | `/admin` | `admin.index` | Admin dashboard |
| GET | `/admin/users` | `admin.users.index` | List users |
| GET | `/admin/users/create` | `admin.users.create` | Create user form |
| POST | `/admin/users` | `admin.users.store` | Save new user |
| DELETE | `/admin/users/{user}` | `admin.users.destroy` | Delete user |
| GET | `/admin/users/{user}/password` | `admin.users.password` | Change user password |
| POST | `/admin/users/{user}/password` | `admin.users.password.update` | Save user password |
| GET | `/admin/users/{user}/2fa` | `admin.users.2fa` | 2FA management page |
| POST | `/admin/users/{user}/2fa/generate` | `admin.users.2fa.generate` | Generate new TOTP secret |
| POST | `/admin/users/{user}/2fa/verify` | `admin.users.2fa.verify` | Verify code + activate |
| POST | `/admin/users/{user}/2fa/disable` | `admin.users.2fa.disable` | Disable 2FA |
| POST | `/admin/users/{user}/2fa/reset` | `admin.users.2fa.reset` | Reset (wipe secret) |
| GET | `/admin/files` | `admin.files.index` | All files |
| DELETE | `/admin/files/{file}` | `admin.files.destroy` | Delete any file |
| PATCH | `/admin/files/{file}/owner` | `admin.files.owner` | Change file owner |

## Business Rules

- **Roles**: `admin` and `user`. Admin has full access. User can only upload, view own files, delete own files, change own password.
- **2FA**: Only admin can generate secret, enable, disable, or reset 2FA for any user. Users cannot manage their own 2FA.
- **Activate 2FA flow**: generate secret → show QR → user scans → admin enters code to verify → activate. Code must be valid before 2FA is enabled.
- **File ownership**: Admin can transfer file ownership to another user. Once transferred, previous owner loses access (cannot see or delete).
- **Allowed file types**: images and videos only (validated by MIME type).
- **No expiry**: files are permanent. No cron cleanup.
- **Duplicate filenames**: same-month same-name → `photo-2.jpg`, `photo-3.jpg`, etc.

## Production Deployment Notes

When deploying to `bucket.digitalku.com`:
1. Change `APP_URL` in `.env`
2. Change `APP_ENV=production` and `APP_DEBUG=false`
3. Update nginx `client_max_body_size` on the server
4. Update `upload_max_filesize` and `post_max_size` in server `php.ini`
5. Run `php artisan migrate` on the server
6. Run `php artisan db:seed` to create admin (once only)
7. Run `php artisan config:cache` and `php artisan route:cache`

**If using local storage** (`FILESYSTEM_DISK=public`):
- Run `php artisan storage:link` on the server

**If using S3** (`FILESYSTEM_DISK=s3`):
- Create S3 bucket, apply public read policy
- Fill in `AWS_*` keys in `.env`
- `storage:link` is not needed
