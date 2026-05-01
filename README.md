# Temporary Storage

Application for temporary file sharing. Users upload PDF or DOCX files (up to 10 MB); files are automatically deleted after 24 hours via a scheduled Artisan command.

## Tech Stack

- **[Laravel 13](https://laravel.com/)** (PHP 8.3) — framework
- **Livewire 3** — reactive file list without page reloads
- **Tailwind CSS 4** — utility-first styling (Vite build)
- **MySQL 8.0** — persistent storage
- **RabbitMQ** — async notification queue

## Architecture

```
app/
├── Contracts/Services/FileStorageServiceInterface.php  ← service contract
├── Services/FileStorageService.php                     ← implementation
├── Http/Controllers/FileController.php                 ← thin controller
├── Http/Requests/UploadFileRequest.php                 ← validation
├── Livewire/FileList.php                               ← reactive list
├── Models/UploadedFile.php                             ← Eloquent model
├── Console/Commands/FilesCleanupCommand.php            ← files:cleanup
└── Jobs/SendFileDeletedNotificationJob.php             ← async notification
```

All file business logic is encapsulated behind `FileStorageServiceInterface`. Controllers only inject the interface and return responses. The UI is two pages:

- `/` — uploader (drag-and-drop or file picker, async jQuery upload)
- `/files` — paginated file list (Livewire), with View and Delete actions per file
- `/files/{id}` — file detail page (name, type, size, upload date, expiry)

## Requirements

- PHP 8.3+, Composer
- Node.js 20+, npm
- MySQL 8.0
- RabbitMQ (for the deletion notification queue; optional for basic usage)

## Installation

```bash
# Full setup in one step (installs deps, generates .env, runs migrations, builds assets):
composer setup
```

### Environment (`.env` — already configured)

```
DB_CONNECTION=mysql
DB_HOST=mysql-8.0.local
DB_PORT=3306
DB_DATABASE=temporary-storage
DB_USERNAME=root
DB_PASSWORD=
```

## Available Commands

### Composer

| Command | Description |
|---|---|
| `composer setup` | Full install: dependencies, `.env`, app key, migrations, npm build |
| `composer test` | Clear config cache and run the PHPUnit test suite |
| `composer cs-fix` | Run Laravel Pint (PSR-12 code style fixer) on project files |
| `composer phpstan` | Run PHPStan static analysis at Level 7 |

### Artisan

| Command | Description |
|---|---|
| `php artisan migrate` | Run pending database migrations |
| `php artisan migrate:fresh --seed` | Drop all tables, re-migrate, and seed |
| `php artisan files:cleanup` | Delete all expired uploaded files from disk and DB |

### npm

| Command | Description |
|---|---|
| `npm run build` | Compile assets for production (Vite) |
| `npm run dev` | Start Vite dev server with HMR |
| `npm run test:e2e` | Run Playwright E2E tests (headed, single worker) and open HTML report |

## Running Tests

```bash
# PHP unit + feature tests
composer test

# E2E tests (requires the site running at https://temporary-storage.loc)
npm run test:e2e
```

E2E tests are located in `tests/e2e/files.spec.js` and cover the full upload → view → delete lifecycle, CSRF error handling, and rate limiting.
