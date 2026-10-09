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
