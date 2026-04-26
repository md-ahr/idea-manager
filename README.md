# Idea

A Laravel web app for capturing and working through **ideas** as structured steps. Authenticated users can create ideas, update steps, attach images, and manage their profile (including email change notifications).

## Requirements

- PHP 8.3+ with extensions required by [Laravel 13](https://laravel.com/docs/13.x/deployment#server-requirements) (e.g. `mbstring`, `openssl`, `pdo`, `fileinfo`, `json`)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (current LTS is fine) and npm

## Quick start

From the project root:

```bash
composer run setup
```

This installs PHP and npm dependencies, ensures `.env` and `APP_KEY` exist, runs migrations, and builds frontend assets.

Then start the full dev stack (HTTP server, queue worker, [Pail](https://laravel.com/docs/logging) log tail, and Vite):

```bash
composer run dev
```

Open the app in your browser. By default, `/` redirects to `/ideas` (auth required), so you will need a user—use **Register** or your usual seeding flow.

### Manual setup (alternative)

If you prefer step-by-step:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # if using sqlite and the file is missing
php artisan migrate
npm install
npm run build
php artisan serve
```

Configure `.env` for your database (`DB_*`), mail, and `APP_URL`. The default example uses SQLite and database-backed sessions, cache, and queues.

## Tests

```bash
composer run test
# or
php artisan test --compact
```

Browser tests use Pest with the [browser plugin](https://pestphp.com/docs/browser-testing) (Playwright). If browser tests fail on a fresh machine, install Playwright browsers once:

```bash
npx playwright install
```

## Code style

```bash
composer run format
```

Runs Rector and Laravel Pint on the project.

## Stack

| Layer    | Technology |
| -------- | ---------- |
| Backend  | Laravel 13, PHP 8.3+ |
| Frontend | Vite, Tailwind CSS v4, Alpine.js |
| Tests    | Pest, Pest Laravel plugin, browser tests |

[AGENTS.md](./AGENTS.md) describes Cursor/Laravel Boost conventions used in this repository.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
