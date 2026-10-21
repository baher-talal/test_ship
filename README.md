# Shipping Management System

A complete shipping-company management system built with **Laravel 7 / PHP 7.3 / MySQL**, with a modern Bootstrap 5 interface available in **Arabic (RTL)** and **English (LTR)**.

## Features

| # | Feature | Details |
|---|---------|---------|
| 1 | Shipments and orders | Create/edit/delete, automatic tracking number, services (standard / express / same day), prepaid or COD, advanced search and filters, printable shipping label |
| 2 | Live tracking | Full history for every status change (time - location - note - user) + a **public tracking page** `/track` for customers, no login required |
| 3 | Customers and couriers | Customer profile with shipments and dues; courier vehicle, status (available / busy) and performance; couriers can be linked to their own login account |
| 4 | Smart notifications | In-app notifications (bell + page) on: new shipment, assignment, status change, invoice issued, **automatic alert for delayed shipments** (scheduled command, hourly) |
| 5 | Multi-branch and multi-user | Multiple roles per user, individually grantable permissions, optional branch (remote / rotating / headquarters staff) |
| 6 | Modern interface | RTL/LTR, dashboard with charts (Chart.js), mobile friendly, sidebar, quick tracking search |
| 7 | Secure and stable | CSRF, bcrypt passwords, full input validation, permission-based access, login throttling, foreign key constraints, DB transactions for invoices |
| + | Invoices | Issue an invoice from a customer's shipments (any shipment, prepaid or COD), discount and tax, pay/cancel, edit/delete after issuing, print |
| + | Reports | Period + branch + courier: summary, by status, courier and branch performance, top destinations, CSV export |

## Requirements

- PHP **7.3** or higher (up to 7.4) with: `mbstring, openssl, pdo_mysql, tokenizer, xml, ctype, json, bcmath`
- Composer
- MySQL 5.7+ / MariaDB 10.3+

## Installation

```bash
# 1) Unzip and enter the folder
cd shipping-system

# 2) Install packages
composer install

# 3) Environment file
cp .env.example .env
php artisan key:generate

# 4) Create an empty database named shipping_system (utf8mb4), then edit the connection settings in .env
#    DB_DATABASE / DB_USERNAME / DB_PASSWORD

# 5) Create the tables + demo data
php artisan migrate --seed

# 6) Run
php artisan serve
```

Open: <http://localhost:8000>

> To install without demo data run `php artisan migrate` only, then create the general manager with `php artisan tinker`:
> ```php
> $u = new App\Models\User(); $u->forceFill(['name'=>'Admin','email'=>'you@example.com','password'=>bcrypt('a-strong-password'),'is_active'=>1])->save();
> DB::table('role_user')->insert(['role_id' => DB::table('roles')->where('slug','admin')->value('id'), 'user_id' => $u->id]);
> ```

## Demo accounts (password: `password`)

| Role | Email |
|------|-------|
| General manager | admin@shipping.test |
| Cairo branch manager | manager@shipping.test |
| Employee | employee@shipping.test |
| Courier | courier@shipping.test |

**Change the passwords right after deploying to production.**

## Languages (Arabic / English)

- The system is bilingual: an **English / العربية** button in the top bar and on the login and public tracking pages.
- The chosen language is stored in a cookie for one year, and the page direction switches automatically (RTL <-> LTR).
- **The source code is written in English.** UI texts are written as `__('English text')`; the Arabic translations live in `resources/lang/ar.json` (key = English text).
- Validation messages: `resources/lang/en/validation.php` and `resources/lang/ar/validation.php`.
- To add a new text: write it in code as `__('Your English text')`, then add its Arabic translation to `resources/lang/ar.json`.
- The default language is set in `config/app.php` (`locale`).

## Roles and permissions (job hierarchy)

- One user can hold **several roles** (general manager > branch manager > supervisor / accountant > employee, plus courier).
- Every role has a **hierarchy level** and permissions that can be edited from the **Roles and permissions** page; new roles can be created.
- A user can be given **extra individual permissions** on top of the permissions of their roles.
- **Branch is optional**: workplace = branch / rotating between branches / remote / headquarters. Users with the "all branches" permission see everyone's data.
- A non-general manager cannot manage someone at a higher level or grant a role above their own level.

## Invoices and collection

- An invoice can be issued for any shipment (prepaid or COD) with a basis of your choice: **shipping fee only** / **total (shipping + goods price)** / **amount to collect from the receiver**.
- On the invoice, the **shipping fee** and the **goods price** are separate items (one line each per shipment). Discount and tax rows appear only when you enter a value for them.
- On a shipment: **shipping fee**, **goods price** and **total** fields; the **amount to collect** is calculated automatically (goods price when COD + shipping fee when paid by the receiver).

## Project structure

```
app/Models            Branch, User, Role, Customer, Courier, Shipment, ShipmentHistory, Invoice, InvoiceItem, UserNotification
app/Http/Controllers  Auth, Dashboard, Shipment, Customer, Courier, Branch, User, Role, Invoice, Report, Notification, Profile, Tracking, Locale
app/Console/Commands  CheckDelayedShipments (shipments:check-delays)
database/migrations   Database tables, roles and permissions, extra shipment fields
database/seeds        Demo data
resources/views       Blade views (RTL/LTR)
resources/lang        ar.json (Arabic translations), en/ and ar/ (validation messages)
routes/web.php        Routes
config/shipping.php   Tax, delivery days and price estimate settings
config/permissions.php Permission list and default roles
tests/Feature         Shipment flow test
```

## Delayed shipment alerts

Add one cron line on the server:

```
* * * * * cd /path/to/shipping-system && php artisan schedule:run >> /dev/null 2>&1
```

Or run it manually: `php artisan shipments:check-delays`

## Production deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
2. Point the web server Document Root to the `public` folder.
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Give write permission to `storage` and `bootstrap/cache`.
6. Enable HTTPS and set `SESSION_SECURE_COOKIE=true`.

## Settings

`config/shipping.php` and `.env`: default tax rate (`INVOICE_TAX_RATE`), expected delivery days per service, and automatic shipping-fee estimate prices.

## Note

The interface loads Bootstrap, Chart.js and the Cairo font from a CDN, so devices need internet access. To run it offline, download these files into `public/` and update the links in `resources/views/layouts/app.blade.php`.
