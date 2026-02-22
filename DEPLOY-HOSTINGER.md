# Git + Hostinger Shared Hosting Deploy Guide

## Part 1: Git par code push karna

### 1.1 GitHub par naya repo banao
1. [github.com](https://github.com) par login karo.
2. **New repository** click karo.
3. Repo name do (e.g. `mms-custm`), **Private** ya **Public** choose karo.
4. **Create repository** click karo (README / .gitignore mat add karo, project me already hai).

### 1.2 Local project ko Git se connect karo
Apne project folder me terminal/PowerShell kholo (`c:\Users\vivek\Pictures\mms custm`):

```bash
# Pehle se git init hai to ye skip karo
git init

# Remote add karo (apna repo URL daalo)
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
```

Agar pehle se `origin` add hai to URL change karna ho to:

```bash
git remote set-url origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
```

### 1.3 Code commit aur push
```bash
# Saari changes stage karo
git add .

# Commit
git commit -m "Add customer advance feature and full MMS app"

# Branch name check karo (usually main ya master)
git branch

# Push (pehli baar)
git push -u origin main
```

Agar branch name `master` hai to:

```bash
git push -u origin master
```

**Important:** `.env` file commit mat karo — wo `.gitignore` me honi chahiye (already hai). Sirf `.env.example` share karo; production par naya `.env` banaoge.

---

## Part 2: Hostinger Shared Hosting par deploy

Hostinger shared hosting par **SSH** limited hota hai; zyada tar **FTP/File Manager** se kaam karna padta hai. Laravel ke liye **document root** `public` folder hona zaruri hai.

### 2.1 Hostinger par ye cheezein check karo
- **PHP version:** 8.1 ya 8.2 (cPanel / hPanel me PHP version select karo).
- **MySQL:** Database create kar sakte ho (cPanel/hPanel → MySQL Databases).
- **Document root:** Domain/subdomain ka root **Laravel ke `public` folder** par point ho (ye Hostinger par subdomain ya “public” as root se set hota hai).

### 2.2 Option A: Git se code lana (agar SSH / Git available ho)
Agar Hostinger plan me **SSH** aur **Git** available ho:

1. SSH se connect karo.
2. Jis folder me site chalani hai (e.g. `public_html` ya `domains/yourdomain.com`) uske andar:
   ```bash
   git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git .
   ```
3. `.env` banao (neeche Option B jaisa).
4. `composer install --no-dev --optimize-autoloader` (agar composer available ho).
5. `php artisan key:generate`
6. `php artisan migrate --force`
7. Document root ko Laravel ke **public** folder par point karo.

### 2.3 Option B: FTP / File Manager se upload (common on shared hosting)
Jab Git/SSH na ho, tab ZIP upload karke extract karte ho.

**Step 1: Local machine par**
- Project folder me `vendor` **include** karo (shared hosting par composer nahi chala sakte to dependency folder bhi upload karni padti hai).
- `.env` **mat** include karo.
- Saari project files ka **ZIP** banao (folder `mms custm` ko zip karo).

**Step 2: Hostinger File Manager**
1. hPanel → **File Manager** kholo.
2. `public_html` (ya jahan site chalani hai) me jao.
3. Purana content hatao ya backup le lo.
4. **Upload** karke ye ZIP upload karo.
5. **Extract** karo. Result: `public_html/mms custm/` jaisa structure ya direct `public_html` me files.

**Step 3: Laravel ke liye folder structure**
Laravel me entry point `public/index.php` hai. Hostinger par do common setups:

**Method 1 – Subdomain / separate domain root**
- Maan lo extract ke baad path hai: `public_html/mms/app/` (sari Laravel files yahan).
- Phir **Domain/Subdomain** settings me **Document root** set karo:  
  `public_html/mms/app/public`  
  Isse URL open hone par `public/index.php` run hoga.

**Method 2 – public_html = public folder (move karke)**
- Extract karo: maan lo `public_html/laravel/` me sari files (app, bootstrap, config, public, …).
- `laravel/public/*` saari files (index.php, .htaccess, etc.) **copy** karke `public_html/` me paste karo.
- `public_html/index.php` kholo, line 2–3 par path fix karo:
  ```php
  // Change this:
  require __DIR__.'/../vendor/autoload.php';
  $app = require_once __DIR__.'/../bootstrap/app.php';
  // To (adjust path if your folder name is different):
  require __DIR__.'/../laravel/vendor/autoload.php';
  $app = require_once __DIR__.'/../laravel/bootstrap/app.php';
  ```
  Yani `__DIR__.'/../laravel/...'` me `laravel` wo folder hai jisme app, bootstrap, vendor, config hai.

### 2.4 Database setup (Hostinger)
1. hPanel → **Databases** → **MySQL Databases**.
2. Naya **Database** banao (e.g. `u123_mms`).
3. Naya **User** banao, strong password do.
4. User ko database par **All Privileges** do.
5. **phpMyAdmin** se empty database dikhni chahiye.

### 2.4a Web Installer (1-click setup, recommended)
Server par code upload ke baad, browser se ye steps follow karo:

1. **Pehli baar:** Site ka URL kholo: `https://yourdomain.com/install.php`  
   - Ye automatically `.env` create karega (agar nahi hai).
2. Phir open karo: `https://yourdomain.com/install`  
   - Form me **Application URL**, **Database Host**, **Database Name**, **Username**, **Password** bharo.
3. **Install Now** click karo.  
   - Backend .env update karega, migrations chalayega, storage link banayega.
4. Install complete hone ke baad aap **Login** page par redirect ho jaoge.

**Note:** Domain ka document root Laravel ke `public` folder par hona chahiye taaki `yourdomain.com/install.php` aur `yourdomain.com/install` dono chal sakein.

### 2.5 .env file (Hostinger par — agar web installer na use karo)
1. File Manager me Laravel root me (jahan `artisan` hai) `.env.example` copy karke `.env` banao.
2. `.env` edit karo:

```env
APP_NAME="MMS"
APP_ENV=production
APP_KEY=                    # Step 2.6 me generate karenge
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost          # Hostinger usually localhost
DB_PORT=3306
DB_DATABASE=u123_mms       # Jo database name diya
DB_USERNAME=u123_user      # Jo user banaya
DB_PASSWORD=your_password
```

### 2.6 App key aur cache (agar PHP/artisan chal sake)
Agar Hostinger par **SSH** ya **PHP CLI** (cron/terminal) se `php artisan` chalta ho:

```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan storage:link
php artisan migrate --force
```

Agar **artisan** bilkul na chale (pure shared hosting):
- **App key:** Local pe `php artisan key:generate` chala ke `.env` me jo `APP_KEY` aaye wo copy karke Hostinger ke `.env` me paste karo.
- **Migrations:** Local pe same database credentials se (ya export) migrations chala ke database structure bana lo, phir Hostinger DB import karo; ya Hostinger pe SQL import karo.

### 2.7 Storage link (file uploads / receipts)
Agar `php artisan storage:link` na chale:
- `public/storage` → symlink banao jo `../storage/app/public` ko point kare.
- Ya File Manager me `storage/app/public` ko `public/storage` se manually link karo (host support kare to).

### 2.8 Permissions
- `storage` aur `bootstrap/cache` folders **writable** hone chahiye (755 ya 775, owner jis user se web server chal raha ho).

### 2.9 Security
- `APP_DEBUG=false` production par hamesha.
- `.env` kabhi bhi public URL se accessible na ho (Laravel default .htaccess ye prevent karta hai agar document root `public` hai).

---

## Short checklist

| Step | Task |
|------|------|
| 1 | GitHub par repo banao, local se `git remote add origin ...` + `git push` |
| 2 | Hostinger par PHP 8.1+, MySQL database + user banao |
| 3 | Code upload karo (Git clone ya ZIP + extract) |
| 4 | Document root = Laravel ka `public` folder set karo |
| 5 | `.env` banao (DB, APP_URL, APP_DEBUG=false), APP_KEY generate karo |
| 6 | Migrations chalao (SSH/artisan ya local se DB export/import) |
| 7 | `storage` / `bootstrap/cache` writable, `storage:link` (agar possible) |

---

## Agli baar update deploy kaise karein

- **Agar Git + SSH hai:** Server par `git pull`, phir `composer install`, `php artisan migrate --force`, `php artisan config:cache`.
- **Agar sirf FTP hai:** Naya ZIP banao (code + vendor), upload karo, extract karo; database changes agar hain to migrations local se chala ke SQL export karke Hostinger pe import karo.

Isi flow se aap code ko Git par le ja sakte ho aur Hostinger shared hosting par deploy kar sakte ho.
