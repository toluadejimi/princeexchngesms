# SMS Rental Platform – Setup Instructions

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ (for Vite/Tailwind)
- MySQL 8+ or SQLite

## 1. Install dependencies

```bash
composer install
npm install
```

## 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure `.env`:

- **Database**: For MySQL set `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Default is SQLite.
- **SMS providers** (optional for first run; can be set in Admin later):
  - `DAISYSMS_API_KEY` – [DaisySMS](https://daisysms.com/docs/api) API key (USA numbers).
  - `GLOBAL_SMS_API_KEY` – Multi-country provider API key (e.g. SMSPool).

## 3. Database

```bash
php artisan migrate
php artisan db:seed
```

Seeder creates:

- Admin: `admin@example.com` / `password` (is_admin = true, wallet = $100)
- User: `user@example.com` / `password` (wallet = $50)
- Two API servers (DaisySMS USA, Global) – add real API keys in Admin → Servers.

## 4. Build frontend

```bash
npm run build
# or for development:
npm run dev
```

## 5. Run the app

```bash
php artisan serve
```

Visit `http://localhost:8000`. Log in as admin to configure servers and pricing.

## 6. Queues & scheduler (production)

- **Expire old rentals**: Add to crontab: `* * * * * php /path/to/artisan schedule:run`
- **Process jobs**: `php artisan queue:work` (or use Supervisor).

Optional: dispatch `PollRentalSmsJob` after creating a rental to poll for SMS (e.g. every 5s until code received or expired).

## Admin

- URL: `/admin` (requires `is_admin` user).
- **Servers**: Enable/disable, set base URL, API key, type (usa_only / multi_country), profit margin.
- **Pricing**: Set price per server + country + service (e.g. USA + WhatsApp = $0.55).
- **Wallet**: Adjust user balance (Admin → use Wallet adjust form if you add one, or tinker).

## Security

- API keys are encrypted in DB (Laravel `Crypt`).
- Rate limits: 10 rent requests/minute, 30 API (services/countries) requests/minute.
- All provider requests are logged in `api_request_logs` (no API key in logs).

## DaisySMS (USA)

- Docs: https://daisysms.com/docs/api
- Country is fixed to USA (187). No country selector in UI when USA server is selected.

## Multi-country provider

- Implemented as `MultiCountrySmsService`; base URL and endpoints follow a generic pattern.
- Adapt `MultiCountrySmsService` (endpoints, response parsing) to your provider’s API (e.g. SMSPool Postman collection).
