# FinanceKu

Aplikasi web **personal finance tracker** berbasis mobile-first untuk penggunaan pribadi (single-user), dibangun dengan Laravel, MySQL, Tailwind CSS, dan Alpine.js.

## Tech Stack

- **Backend:** PHP 8.4+, Laravel 13
- **Database:** MySQL
- **Frontend:** Blade, Tailwind CSS v4, Alpine.js, Vite
- **Testing & Formatting:** Pest, Laravel Pint

## Fitur Saat Ini (Fase 1 — Fondasi)

- **Autentikasi Single-User:** Halaman login (`/login`) dan logout (`/logout`) tanpa registrasi publik, dilengkapi rate limiting login, Remember Me aktif secara default, dan `SESSION_LIFETIME` yang diperpanjang (`43200` menit / 30 hari).
- **Skema Database & Model Eloquent:**
  - `users` (`current_streak`, `last_active_date`)
  - `categories` (`name`, `type`, `icon`, `color`) + relasi `hasMany` ke transaksi
  - `transactions` (`category_id`, `type`, `amount`, `note`, `transaction_date`) + relasi `belongsTo` ke kategori
  - `budgets` (`period_type`, `amount`, `is_dynamic`, `start_date`, `end_date`)
- **Seeder Awal:** Akun default serta kategori awal untuk **Expense** (*Makan & Minum*, *Transportasi*, *Belanja*, *Tagihan*, *Lainnya*) dan **Income** (*Gaji*, *Bonus*, *Freelance*, *Lainnya*).
 
## Instalasi & Setup Lokal

1. **Clone repositori & install dependensi:**
   ```bash
   git clone <URL_REPO_GITHUB>
   cd FinanceKu
   composer install
   npm install
   ```

2. **Konfigurasi environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan konfigurasi MySQL di `.env` (default: `DB_DATABASE=financeku`), lalu pastikan database `financeku` sudah dibuat di MySQL.

3. **Migrasi & seed database:**
   ```bash
   php artisan migrate --seed
   ```

4. **Build aset frontend & jalankan server lokal:**
   ```bash
   npm run build
   php artisan serve
   ```
   Atau gunakan mode development:
   ```bash
   composer run dev
   ```

## Akun Default (Development)

Setelah menjalankan `php artisan migrate --seed`, Anda dapat masuk menggunakan akun default dari `UserSeeder`:

- **Email:** `admin@financeku.test`
- **Password:** `password`

> Segera ganti email dan password default tersebut sebelum digunakan di lingkungan produksi.

## Pengujian & Code Formatting

Jalankan test suite (Pest):
```bash
php artisan test --compact
```

Rapikan format kode PHP (Laravel Pint):
```bash
php vendor/bin/pint
```

## Lisensi

Proyek ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
