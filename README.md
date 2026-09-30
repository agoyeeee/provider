# provider

A data provider / utility registry with a public map. Provider records are maintained by admins, surfaced on a public landing page and `/peta` map, and managed from an admin dashboard.

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white) ![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)

## Features

- Public landing page with aggregate stats (records, unique providers, district/village coverage) and featured providers
- `/peta` full map view built from provider coordinates
- Admin dashboard with totals, recent records, and per-kecamatan / per-kelurahan charts
- Provider CRUD (provinsi/kota/kecamatan/kelurahan, kode referensi wilayah, utilitas, provider name, ODP, Sijali name + link, coordinates, photo, survey date, assigned user)
- Excel import (XLSX) with header normalization and Indonesian coordinate parsing; export endpoint returns JSON
- User management for super-admin (NIK, contact, name, email, password, role)
- Role-based access: `super_admin`, `admin`, `pemilik_umkm`, `verifikator`
- Admin / super-admin middleware guards
- Breeze auth (login, register, password reset, email verification) and profile management

## Stack

Backend:

- PHP ^8.2, Laravel ^12.0
- Inertia Laravel ^2.0, Breeze ^2.3, Sanctum ^4.0
- darkaonline/l5-swagger ^9.0, phpoffice/phpspreadsheet ^5.3, tightenco/ziggy ^2.0

Frontend:

- Vue ^3.4.0, Vite ^7.1.5, `@inertiajs/vue3` ^2.0.0
- `@tanstack/vue-table` ^8.21.3
- `@unovis/vue` and `@unovis/ts` ^1.6.1 (charts)
- reka-ui ^2.5.0, vee-validate ^4.15.1 with `@vee-validate/zod` ^4.15.1 and zod ^3.25.76
- Tailwind CSS

## Requirements

- PHP 8.2+ with gd, zip, intl, pdo_mysql
- Composer, Node.js 20+, MySQL

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Configure the `DB_*` variables in `.env` for MySQL, then run:

```bash
npm run dev      # Vite dev server
php artisan serve
```

## Common commands

`makefile` targets:

| Target | Command |
| --- | --- |
| `make dev` | `npm run dev` |
| `make ser` | `php artisan serve` |
| `make run` | `npm run build` |
| `make cln` | `php artisan optimize:clear` |
| `make db` | `php artisan migrate:fresh --seed` |
| `make docs` | `php artisan l5-swagger:generate` |

## Docker

`Dockerfile` is a three-stage build (composer dependencies → Vite assets → `php:8.3-fpm-alpine` with nginx and supervisor). `docker-compose.yml` runs the app with host networking, serving on port 80 (`APP_URL` defaults to `http://localhost:8082`) and mounting `.env`.

```bash
docker compose up -d --build
```

## Routes

| Method | Path | Description |
| --- | --- | --- |
| GET | `/` | Public landing page |
| GET | `/peta` | Public map |
| GET | `/admin/dashboard` | Admin dashboard |
| resource | `/admin/provider` | Provider CRUD |
| GET | `/admin/provider/export` | Export provider data |
| POST | `/admin/provider/import` | Import providers from XLSX |
| resource | `/admin/users` | User management (super-admin only) |
| GET/PATCH/DELETE | `/profile` | Profile |

## Notes

`docs/` contains Indonesian change notes covering schema updates and map/link behaviour, plus integration notes for the Sakti Karanganyar OAuth flow and an SSO error-handling middleware. No SSO routes are wired up, so SSO is not a shipped feature.

## Testing

```bash
composer test   # php artisan test
```
