# CCTV Business Management - Deploy Guide

## Hosting Pe Deploy Kaise Karein

### Step 1: Files Upload
- Git pull ya FTP se saari files hosting pe upload karo
- `vendor/` folder already included hai, composer install ki zaroorat NAHI hai

### Step 2: `.env` File Banao
```bash
cp .env.example .env
```
Phir `.env` file edit karo aur ye change karo:
- `APP_URL=https://yourdomain.com`
- `DB_HOST=localhost`
- `DB_PORT=3306`
- `DB_DATABASE=your_db_name`
- `DB_USERNAME=your_db_user`
- `DB_PASSWORD=your_db_password`

### Step 3: App Key Generate
```bash
php artisan key:generate
```

### Step 4: Database Migrate + Seed
```bash
php artisan migrate --force
php artisan db:seed --force
```

### Step 5: Storage Link
```bash
php artisan storage:link
```

### Step 6: Cache (Optional - Speed)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 7: Permissions (Linux Hosting)
```bash
chmod -R 775 storage bootstrap/cache
```

---

## Login Credentials (After Seed)

| User | Email | Password | Role |
|------|-------|----------|------|
| Admin | admin@goldsecurity.in | password | Company Admin |
| Manager | manager@goldsecurity.in | password | Manager |
| Technician | tech@goldsecurity.in | password | Technician |
| Accountant | accounts@goldsecurity.in | password | Accountant |
| Customer | customer@goldsecurity.in | password | Customer |

Quick Login Page: `https://yourdomain.com/quick-login`

---

## Requirements
- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- mod_rewrite enabled (Apache)
