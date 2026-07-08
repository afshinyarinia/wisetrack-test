# Post Analytics API

A Laravel API for managing posts and tracking daily unique post views. The project uses Sanctum token authentication, queued welcome emails, public post browsing, and analytics endpoints designed for chart data.

## Requirements

- PHP 8.3+
- Composer
- SQLite, MySQL, or PostgreSQL

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

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

The first app container startup runs migrations automatically. To seed sample data inside Docker:

```bash
docker compose exec app php artisan db:seed
```

Useful Docker commands:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan route:list --path=api
docker compose exec app php artisan migrate:fresh --seed
docker compose logs -f queue
```

Docker uses `.env.docker` by default through `docker-compose.yml`. The committed values are local development values only.

For local development, SQLite is enough:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
QUEUE_CONNECTION=database
```

Create the SQLite file before running migrations:

```bash
type nul > database/database.sqlite
```

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

The public top-viewed endpoint also uses Laravel cache. In Docker that cache is Redis-backed, with a short one-minute TTL so the endpoint stays responsive without keeping analytics stale for long.

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
