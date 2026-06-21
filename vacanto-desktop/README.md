# Vacanto Desktop (Laravel + NativePHP)

Laravel 11 port of the Vacanto job & services platform, packaged for desktop with [NativePHP](https://nativephp.com).

## Requirements

| Mode | PHP | Database | Node.js |
|------|-----|----------|---------|
| Web dev (`php artisan serve`) | 8.2+ | MySQL (XAMPP) | optional |
| Desktop (`composer native:dev`) | **8.3+** | SQLite | **22+** |

## Web development (MySQL)

```bash
cd vacanto-desktop
cp .env.example .env   # configure MySQL: vacanto_laravel
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=VacantoSeeder
php artisan serve
```

Open http://127.0.0.1:8000

**Admin:** `admin@platform.com` / `admin123`

## Desktop development (NativePHP + SQLite)

1. Upgrade PHP to **8.3+** (NativePHP requirement; XAMPP 8.2 will not run the desktop app).
2. Copy the desktop environment file:

```bash
cp .env.nativephp.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=VacantoSeeder
```

3. Start the desktop app:

```bash
composer native:dev
# or: php artisan native:run
```

4. Build for production:

```bash
php artisan native:publish
```

## NativePHP configuration

- App ID: `com.vacanto.desktop` (`config/nativephp.php`)
- Window: 1280×800 (`app/Providers/NativeAppServiceProvider.php`)
- Updater disabled by default for local development

## Admin document review

Pending companies and freelancers can be reviewed at **Admin → Manage Users → Review**, including uploaded government IDs and certification documents.

## Stack

- Laravel 11, Blade, Eloquent, MySQL/SQLite
- Stripe payments
- NativePHP Desktop 2.x (Electron)
- Custom CSS (from legacy Vacanto theme)
