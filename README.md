# SpeedLine - Automotive Workshop Management

A full-stack Laravel 13 web application for managing a high-performance sports car service workshop.

## Stack
- **Backend**: Laravel 13, PHP 8.4
- **Frontend**: Tailwind CSS v4 (via `@tailwindcss/vite`), Vite 8
- **Database**: MySQL 8
- **Payment**: Midtrans Core API (bank transfer, GoPay, QRIS)
- **Charts**: Chart.js
- **Alerts**: SweetAlert2

## Docker Setup (Recommended)

```bash
# Clone / unzip project
cd speedline

# Copy env (already configured for Docker)
cp .env.example .env   # or use the existing .env

# Start services
docker compose up -d --build

# Wait for containers to be healthy, then:
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# App is now at:
http://localhost:8000
```

## Manual Setup (Local)

```bash
composer install
npm install && npm run build
cp .env.example .env
# Edit .env: set DB_HOST=127.0.0.1 and your local DB creds
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Default Credentials (from seeder)

| Role     | Email                    | Password   |
|----------|--------------------------|------------|
| Admin    | admin@speedline.id       | password   |
| Owner    | owner@speedline.id       | password   |
| Mekanik  | mekanik@speedline.id     | password   |
| Kasir    | kasir@speedline.id       | password   |
| Customer | customer@speedline.id    | password   |

## User Flow

```
Customer registers → Adds vehicle → Creates booking
   ↓ (admin confirms)
Admin confirms → In Progress → Creates Transaction (assigns mechanic + spareparts)
   ↓
Customer pays via Midtrans Core API (bank VA / GoPay / QRIS) or Cash
   ↓
Booking Completed
```

## Midtrans (Sandbox)

Using Core API — no Snap popup.  
Payment flow: customer selects method → app calls `/v2/charge` → gets VA number or QR → polls `/v2/{order_id}/status` every 5 seconds until `settlement`.