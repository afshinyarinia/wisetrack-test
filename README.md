# Post Analytics API

A Laravel API for managing posts and tracking daily unique post views. The project uses Sanctum token authentication, queued welcome emails, public post browsing, and analytics endpoints designed for chart data.

## Requirements

- PHP 8.3+
- Composer
- MySQL 8.4+
- Redis, when using the Docker queue/cache setup

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
docker compose up -d mysql redis
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The default `.env.example` points Laravel at the MySQL service exposed by Docker on `127.0.0.1:33060`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=33060
DB_DATABASE=post_analytics
DB_USERNAME=post_analytics
DB_PASSWORD=post_analytics_password
```

If you use your own MySQL server instead of the Docker service, update the `DB_*` values before running `php artisan migrate --seed`.

## Docker Setup

The repository includes a Docker stack with Nginx, PHP-FPM, MySQL, Redis, and a dedicated queue worker.

```bash
docker compose up --build
```

The API will be available at:

```text
http://localhost:8080/api
```

The Docker environment uses:

- MySQL for the application database
- Redis for cache, sessions, and queues
- A queue worker service for the welcome email job
- A shared storage volume for uploaded post images
- `stderr` logging so Laravel logs go to container output instead of `storage/logs/laravel.log`

The first app container startup runs migrations automatically. To seed sample data inside Docker:

```bash
docker compose exec app php artisan db:seed
```

The main seeder creates demo users, 100 posts, and analytics view rows for January 2026, matching the default Postman collection date variables. To reset and reseed the Docker database:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Useful Docker commands:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan route:list --path=api
docker compose exec app php artisan migrate:fresh --seed
docker compose logs -f queue
```

Docker uses `.env.docker` by default through `docker-compose.yml`. The committed values are local development values only.

## Queue

Registration dispatches a welcome email job. Use the database queue driver locally:

```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

Tests fake the queue where needed and assert the welcome job is dispatched.

In Docker, the queue uses Redis:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=redis
```

The public top-viewed endpoint also uses Laravel cache. In Docker that cache is Redis-backed. The default TTL is 60 seconds through `ANALYTICS_TOP_VIEWED_CACHE_TTL_SECONDS`. Successful new unique views can bump the top-viewed cache version at most once per TTL window, so the endpoint avoids per-view cache churn while keeping rankings eventually fresh. Old entries expire naturally.

## API Endpoints

### Auth

| Method | Endpoint | Auth | Description |
|---|---|---:|---|
| POST | `/api/register` | No | Register a user and return a Sanctum token |
| POST | `/api/login` | No | Login and return a Sanctum token |
| GET | `/api/user` | Yes | Return the authenticated user |
| POST | `/api/logout` | Yes | Revoke the current token |

### Posts

| Method | Endpoint | Auth | Description |
|---|---|---:|---|
| GET | `/api/posts` | No | Paginated public post list |
| GET | `/api/posts/{post}` | No | Public post detail and view tracking |
| POST | `/api/posts` | Yes | Create a post with optional featured image |
| GET | `/api/posts/top-viewed` | No | Top posts for a date range with rank and aggregate meta |

### Analytics

| Method | Endpoint | Auth | Description |
|---|---|---:|---|
| GET | `/api/posts/{post}/analytics/daily` | Yes | Daily chart rows with `from` and `to` filters |
| GET | `/api/posts/{post}/analytics/summary` | Yes | Daily chart rows plus summary totals for the same date filters |

## Example Requests

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Afshin\",\"email\":\"afshin@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
```

```bash
curl http://127.0.0.1:8000/api/posts/1/analytics/daily?from=2026-01-01^&to=2026-01-31 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <token>"
```

## Architecture

The code follows a structured Laravel approach:

- Controllers only coordinate HTTP requests and responses.
- Form Requests validate request input.
- DTOs move validated input into application code.
- Actions represent use cases such as registration, post creation, view tracking, and analytics.
- Repositories own analytics and view persistence queries.
- Value objects protect date ranges and visitor identity.
- API Resources define response shape.
- Jobs handle asynchronous email work.

Folder layout:

```text
app/
  Http/
    Controllers/        HTTP entrypoints grouped by Auth, Posts, and Analytics
    Requests/           Validation and request-to-DTO helpers
    Resources/          API response transformers
  Jobs/                 Queue jobs, such as welcome email dispatch
  Mail/                 Mailables used by queued jobs
  Models/               Laravel app-level models, currently User
  Modules/
    Auth/               Login and registration actions/data objects
    Posts/              Post model plus create, list, and show use cases
    Analytics/          View tracking, reports, repositories, value objects, services
    Shared/             Cross-feature infrastructure abstractions
  Providers/            Service container bindings for interfaces to implementations
database/
  factories/            Test and seeder model factories
  migrations/           MySQL schema for users, posts, post views, jobs, cache, tokens
  seeders/              Main demo-data seeder for users, posts, and analytics rows
docker/
  mysql/init/           Local MySQL bootstrap, including the test database
  nginx/                Nginx container config
  php/                  PHP-FPM container and entrypoint
routes/
  api.php               Public API routes
  console.php           Artisan-only commands such as posts:seed
tests/
  Feature/              API, seeder, and command coverage
```

The module folders are organized by feature first, then by role:

- `Actions` contain application use cases.
- `Data` objects carry validated input into actions.
- `Models` contain Eloquent models owned by that feature.
- `Repositories` isolate persistence details when queries or atomic writes are non-trivial.
- `Services` contain reusable domain calculations or identity resolution.
- `ValueObjects` protect small business concepts such as date ranges and visitor identity.

Interface bindings live in `AppServiceProvider`, keeping controllers and actions dependent on contracts where the implementation is likely to change.

The code avoids a full domain framework, command bus, CQRS, and broad repository wrapping. The extra structure is used where it protects actual business rules.

## View Tracking Design

The task requires daily unique views by registered users and guests. The `post_views` table stores:

- `post_id`
- nullable `user_id`
- `visitor_hash`
- `ip_address`
- `user_agent`
- `viewed_date`
- exact `viewed_at`

The uniqueness rule is enforced by a database index on `post_id`, `visitor_hash`, and `viewed_date`.

`viewed_date` makes daily analytics index-friendly and avoids running `DATE(viewed_at)` in every query. `visitor_hash` gives both authenticated users and guests a stable uniqueness key. This also avoids nullable `user_id` problems in unique indexes, where some databases allow multiple `NULL` values.

View recording uses `insertOrIgnore`, so duplicate requests or concurrent requests for the same visitor and day stay idempotent.

## Testing

```bash
php artisan test
```

Tests use the dedicated MySQL database `post_analytics_test`. Fresh Docker volumes create this database automatically through `docker/mysql/init/01-create-test-database.sql`. If you already had a MySQL volume before this file existed, create it once manually:

```bash
docker compose exec mysql mysql -uroot -proot_password -e "CREATE DATABASE IF NOT EXISTS post_analytics_test; GRANT ALL PRIVILEGES ON post_analytics_test.* TO 'post_analytics'@'%';"
```

Covered behavior:

- User registration and duplicate email validation
- Login and token-authenticated current user endpoint
- Invalid login handling
- Authenticated post creation
- Guest post creation rejection
- Post pagination
- Post show view tracking
- Duplicate daily view prevention
- Daily analytics counts and zero-filled dates
- Top viewed post sorting

## Known Limitations

- Guest identity is based on IP address and user agent, which is approximate.
- Analytics are computed from raw `post_views`; at high traffic volume this should move to daily aggregate tables.
- Image storage defaults to Laravel's configured public disk. Production should use durable object storage such as S3-compatible storage.
