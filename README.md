# Nike Business Intelligence Dashboard

Aplikasi dashboard Business Intelligence berbasis Laravel + Vue (Inertia.js) untuk analisis data penjualan Nike. Dilengkapi sistem autentikasi berbasis role, upload CSV via web, dan visualisasi data interaktif.

---

## Fitur Utama

- **Multi-role access**: `super_admin`, `analyst`, `manager`, `staff`
- **Dashboard Overview** — metrik penjualan publik (tanpa login)
- **Analyst Workbench** — grafik interaktif, filter, export CSV
- **Strategy Hub** — ringkasan eksekutif per kuartal dan region
- **Data Ingestion** — upload file CSV langsung dari browser
- **Identity Control** — manajemen akun operator (khusus super_admin)
- **ETL via CLI** — import CSV lewat Artisan command

---

## Persyaratan

- PHP >= 8.2
- Composer
- Node.js >= 18 + NPM
- MySQL (Laragon / XAMPP / dll)

---

## Setup Setelah Clone

### 1. Clone repository
```bash
git clone <repo-url> d:\github\business-intelligence
cd d:\github\business-intelligence
```

### 2. Buat database MySQL
Buat database baru di Laragon (atau tool lain) dengan nama `business_intelligence`.

### 3. Salin file environment
```bash
copy .env.example .env
```

### 4. Sesuaikan file `.env`
```env
APP_NAME="Nike BI"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Install dependency PHP
```bash
composer install
```

### 6. Install dependency frontend
```bash
npm install
```

### 7. Generate app key
```bash
php artisan key:generate
```

### 8. Jalankan migration
```bash
php artisan migrate
```

### 9. Seed data awal (user + data Nike)
```bash
php artisan db:seed
```

Perintah ini akan membuat 4 akun default dan mengimpor data CSV secara otomatis.

### 10. Jalankan server (2 terminal terpisah)
```bash
# Terminal 1 — Backend
php artisan serve

# Terminal 2 — Frontend (Vite)
npm run dev
```

### 11. Buka aplikasi
- http://127.0.0.1:8000

---

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@nike.test | password |
| Analyst | analyst@nike.test | password |
| Manager | manager@nike.test | password |
| Staff | staff@nike.test | password |

> Akun baru dibuat oleh Super Admin melalui menu **IDENTITY** — halaman registrasi publik dinonaktifkan.

---

## Hak Akses per Role

| Fitur | Super Admin | Analyst | Manager | Staff |
|-------|:-----------:|:-------:|:-------:|:-----:|
| Dashboard Overview | ✓ | ✓ | ✓ | ✓ |
| Analyst Workbench | ✓ | ✓ | — | — |
| Strategy Hub | ✓ | — | ✓ | — |
| Data Ingestion (Upload) | ✓ | — | — | ✓ |
| Identity Control (Admin) | ✓ | — | — | — |

---

## Import Data CSV via CLI (Opsional)

Jika ingin mengimpor ulang data CSV tanpa seeder:
```bash
php artisan import:nike-csv
```

Atau tentukan path file sendiri:
```bash
php artisan import:nike-csv --file="C:\path\ke\file.csv"
```

File CSV default: `database/seeders/data/Nike_Sales_Uncleaned.csv`

---

## Struktur File Penting

```
business-intelligence/
├── app/
│   ├── Console/Commands/
│   │   └── ImportNikeCsvCommand.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php       ← CRUD akun operator
│   │   │   ├── AnalystController.php     ← data & chart analyst
│   │   │   ├── DashboardController.php   ← overview publik
│   │   │   ├── ManagerController.php     ← strategy hub
│   │   │   └── UploadController.php      ← upload CSV
│   │   └── Middleware/
│   │       ├── HandleInertiaRequests.php ← share flash & auth
│   │       └── RoleMiddleware.php        ← proteksi per role
│   ├── Models/
│   │   ├── User.php
│   │   ├── DimProduk.php
│   │   ├── DimPelanggan.php
│   │   ├── DimWaktu.php
│   │   └── FactPenjualan.php
│   └── Services/
│       └── CsvImportService.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── NikeDataSeeder.php
│       └── data/
│           └── Nike_Sales_Uncleaned.csv  ← letakkan file CSV di sini
├── resources/
│   └── js/
│       ├── Layouts/
│       │   └── AppLayout.vue
│       └── Pages/
│           ├── Auth/
│           │   └── Login.vue
│           └── Dashboard/
│               ├── overview.vue          ← dashboard publik
│               ├── analyst.vue           ← workbench analyst
│               ├── manager.vue           ← strategy hub
│               ├── CSVUpload.vue         ← upload data
│               └── Admin.vue             ← kelola akun
├── routes/
│   ├── web.php
│   ├── api.php
│   └── auth.php
├── .env
├── jsconfig.json
├── composer.json
├── package.json
└── vite.config.js
```

---

## Perintah Penting

```bash
php artisan migrate          # buat semua tabel
php artisan migrate:fresh --seed  # reset & isi ulang database
php artisan db:seed          # jalankan seeder saja
php artisan import:nike-csv  # import CSV via CLI
php artisan serve            # jalankan server Laravel
npm run dev                  # jalankan frontend Vite (development)
npm run build                # build frontend untuk production
```

---

## Catatan

- Database MySQL harus sudah dibuat sebelum menjalankan `migrate`.
- File CSV harus diletakkan di `database/seeders/data/Nike_Sales_Uncleaned.csv` sebelum seeding.
- `npm run dev` harus berjalan bersamaan dengan `php artisan serve` saat development.
- Akun baru hanya bisa dibuat oleh **Super Admin** melalui menu Identity di dashboard.