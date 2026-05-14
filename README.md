# ⚡ Nike Intelligence Network (NIKE BI)

A high-performance Business Intelligence Dashboard built with **Laravel 11**, **Vue 3 (Inertia.js)**, and **TailwindCSS**. This system is designed for deep analysis of Nike's global sales, product catalog, and customer sentiment.

---

## 🚀 Key Features

### 📡 Data Intelligence
- **Smart Ingestion Pipeline**: Upload CSV files directly. System features **Auto-Detection** (identifies file types based on headers) and **Smart Mapping** (fuzzy matching for varied column names).
- **Star Schema Architecture**: Data is processed into optimized `Fact` and `Dimension` tables for high-speed analytical queries.
- **Support for Multi-Data Nodes**: 
  - `SALES`: Transactional data and revenue metrics.
  - `PRODUCTS`: Full inventory catalog with image CDN support.
  - `REVIEWS`: Customer feedback and sentiment analysis.

### 🔐 Security & Access Control
- **Multi-Tier RBAC**: Distinct roles for `Super Admin`, `Analyst`, `Manager`, and `Staff`.
- **Public Privacy Mode**: Guests (non-logged users) see a "Teaser" dashboard. Sensitive financial data (Revenue, Profit, Exact Order IDs) is automatically hidden.
- **Identity Terminal**: Centralized user management restricted to Super Admins.

### 📊 Advanced Analytics
- **Market Intelligence**: Real-time charts for monthly trends, regional dominance, and channel performance.
- **Customer Sentiment**: Polar area analysis of star ratings and fit feedback.
- **Top Performer Tracking**: Automated ranking of products by volume and revenue.

---

## 🛠️ Technical Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue 3 + Inertia.js (Composition API)
- **Styling**: Vanilla CSS + Tailwind (Premium Dark Aesthetics)
- **Charts**: Chart.js 4+
- **Database**: MySQL (Optimized Star Schema)

---

## 📦 Installation & Setup

### 1. Initialize Repository
```bash
git clone <repo-url>
cd business-intelligence
composer install
npm install
```

### 2. Environment Configuration
Copy `.env.example` to `.env` and configure your database settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=business_intelligence
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database & App Keys
```bash
php artisan key:generate
php artisan migrate --seed
```
*Note: The seeder will automatically create default accounts and ingest baseline data.*

### 4. Launch Development Environment
Run these in separate terminal windows:
```bash
php artisan serve
npm run dev
```

## Catatan

- Database MySQL harus sudah dibuat sebelum menjalankan `migrate`.
- File CSV harus diletakkan di `database/seeders/data/Nike_Sales_Uncleaned.csv` sebelum seeding.
- `npm run dev` harus berjalan bersamaan dengan `php artisan serve` saat development.
- Akun baru hanya bisa dibuat oleh **Super Admin** melalui menu Identity di dashboard.