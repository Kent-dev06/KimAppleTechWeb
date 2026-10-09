# Kim Apple Tech Repair Management System

Laravel application for customer smartphone repair appointments and staff service management. Device registration and appointment creation accept smartphones only.

## Laragon setup

1. Start Laragon services and create the `kim_apple_tech` MySQL database.
2. Install PHP dependencies and configure the app:

   ```sh
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

3. Check the MySQL settings in `.env` (`DB_DATABASE=kim_apple_tech`, `DB_USERNAME=root`, blank password by default for a local Laragon install), then initialize the schema and sample accounts:

   ```sh
   php artisan migrate --seed
   php artisan serve
   ```

4. Visit `http://127.0.0.1:8000`.

## Sample accounts

| Role | Email | Password |
| --- | --- | --- |
| Shared staff | `staff@kimapple.tech` | `password` |
| Customer | `customer@example.com` | `password` |
| Customer | `customer2@example.com` | `password` |

The sample credentials are for local development and acceptance testing.

## Tests

Run the feature and application tests with:

```sh
php artisan test
```

The tests use an in-memory SQLite database. The configured Laragon application database uses MySQL.

## Live repair notifications

The repair portal stores notifications in the database and broadcasts them privately with Laravel Reverb. If Reverb is unavailable, the bell polls every 20 seconds.

For a fresh Laragon setup, copy the `REVERB_*` and `VITE_REVERB_*` values from `.env.example` into `.env` and set `BROADCAST_CONNECTION=reverb`. Keep `REVERB_HOST=127.0.0.1`, `REVERB_PORT=8080`, and `REVERB_SCHEME=http` for local development.

Run the following from the project folder:

```sh
php artisan migrate
npm install
npm run build
```

Start the Laravel app and Reverb in separate terminals:

```sh
php artisan serve
php artisan reverb:start
```

The authenticated private channel uses the user's `user_id`; users can only subscribe to their own notifications.

## Appointment rules and demo data

Appointment hours and limits can be adjusted with `SHOP_OPENING_TIME`, `SHOP_CLOSING_TIME`, `SHOP_APPOINTMENT_MIN_DURATION_MINUTES`, `SHOP_APPOINTMENT_MAX_DURATION_MINUTES`, and `SHOP_MAX_ACTIVE_PENDING_APPOINTMENTS` in `.env`.

The database seeder creates only reserved `.test` demo accounts. Set `DEMO_ADMIN_PASSWORD`, `DEMO_CLERK_PASSWORD`, and `DEMO_CUSTOMER_PASSWORD` (each at least 8 characters) before explicitly running `php artisan db:seed`. No demo data is seeded automatically on deployment. To remove only those reserved demo accounts and their linked records, run:

```sh
php artisan demo:reset --force
```

The reset command refuses to run without `--force` and is limited to the reserved demo accounts.
