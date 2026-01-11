# TraceKit Laravel Test App

A comprehensive Laravel 11 example application demonstrating the TraceKit Laravel APM package for distributed tracing, performance monitoring, and code debugging.

## Features

This test app showcases:

- ✅ **Automatic HTTP request tracing** - All requests traced via Laravel middleware
- ✅ **Database query monitoring** - Track SQL queries with execution time
- ✅ **Code monitoring with snapshots** - Live debugging with variable inspection
- ✅ **Cross-service communication** - CLIENT spans for outgoing HTTP calls
- ✅ **Service dependency mapping** - Automatic service graph generation
- ✅ **Error tracking** - Capture exceptions with full trace context
- ✅ **Cache operation monitoring** - Track cache hits/misses
- ✅ **Queue job tracking** - Monitor background job performance

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- SQLite (or your preferred database)
- TraceKit account and API key (get one at [tracekit.dev](https://tracekit.dev))
- (Optional) Other test services running for cross-service testing

## Setup

### 1. Install Dependencies

```bash
# Navigate to the project directory
cd tracekit/laravel-test

# Install PHP dependencies
composer install
```

### 2. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Generate Laravel application key
php artisan key:generate

# Edit .env and add your TraceKit API key
# TRACEKIT_API_KEY=your-api-key-here
```

Get your API key from: https://app.tracekit.dev

### 3. Setup Database

```bash
# Create SQLite database (default configuration)
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 4. Run the Application

```bash
# Start the Laravel development server
php artisan serve --port=8083

# The server will start on http://localhost:8083
```

## Available Endpoints

| Endpoint | Method | Description | TraceKit Features Demonstrated |
|----------|--------|-------------|-------------------------------|
| `/` | GET | Service info & endpoint list | Basic HTTP tracing |
| `/health` | GET | Health check | Simple status endpoint |
| `/test` | GET | Code monitoring test | Snapshot capture, variable inspection |
| `/error-test` | GET | Trigger an error | Error recording with trace context |
| `/checkout` | GET | Checkout simulation | Database queries, cache operations |
| `/api/data` | GET | Data endpoint | Called by other services |
| `/api/call-go` | GET | Call Go service | CLIENT spans, cross-service tracing |
| `/api/call-node` | GET | Call Node.js service | Distributed tracing |
| `/api/call-python` | GET | Call Python service | Service dependency mapping |
| `/api/call-php` | GET | Call PHP service | Cross-service communication |
| `/api/call-all` | GET | Call all services | Multi-service distributed trace |

## Testing

### Quick Test

```bash
# Test all endpoints
curl http://localhost:8083/
curl http://localhost:8083/health
curl http://localhost:8083/test
curl http://localhost:8083/checkout
```

### Code Monitoring Test

The `/test` endpoint demonstrates code snapshot capture:

```bash
curl http://localhost:8083/test
```

This will capture three snapshots:
1. **test-route-entry** - Route entry point with request metadata
2. **test-processing** - During processing with user_id and cart_total
3. **test-complete** - Final state with user count and status

View these snapshots in your TraceKit dashboard under Code Monitoring.

### Cross-Service Communication Test

Requires other test services to be running:

```bash
# Test calling Go service (requires go test-app on :8082)
curl http://localhost:8083/api/call-go

# Test calling Node.js service (requires node-test on :8084)
curl http://localhost:8083/api/call-node

# Test calling Python service (requires python-test on :5001)
curl http://localhost:8083/api/call-python

# Test calling PHP service (requires php-test on :8086)
curl http://localhost:8083/api/call-php

# Test calling ALL services (full distributed trace)
curl http://localhost:8083/api/call-all
```

### Error Tracking Test

```bash
# Trigger an intentional error
curl http://localhost:8083/error-test
```

This will:
- Create a trace with error status
- Record exception details
- Capture error context and stack trace
- Alert you via configured channels

### Database & Cache Test

```bash
# Test endpoint with DB queries and caching
curl http://localhost:8083/checkout
```

This endpoint demonstrates:
- Multiple database queries tracked
- Cache operations monitored
- Transaction tracking
- Query performance metrics

## What Gets Traced

### Automatic Tracing

The TraceKit Laravel middleware automatically captures:

- **HTTP Requests**
  - Request method, path, headers
  - Response status code and size
  - Request duration

- **Database Queries**
  - SQL statements
  - Query execution time
  - Bindings (sanitized)
  - Connection name

- **Cache Operations**
  - Cache hits/misses
  - Key names
  - TTL values

- **Queue Jobs**
  - Job class and payload
  - Execution time
  - Success/failure status

### Code Monitoring Snapshots

Use the `tracekit_snapshot()` helper to capture variable state:

```php
// Basic snapshot
tracekit_snapshot('checkpoint-name', [
    'user_id' => $userId,
    'cart_total' => $cartTotal,
    'status' => 'processing',
]);

// Available in routes/web.php
tracekit_snapshot('route-entry', [
    'route' => 'checkout',
    'method' => request()->method(),
    'timestamp' => now()->toISOString(),
]);
```

### Cross-Service Tracing

When calling other services, TraceKit automatically:
- Creates CLIENT spans for outgoing HTTP calls
- Propagates trace context via `traceparent` header
- Maps service dependencies for visualization

```php
// Automatic CLIENT span creation
$response = Http::get(GO_SERVICE_URL . '/api/internal');
```

## Viewing Traces

### Local Development
Open your TraceKit dashboard at: http://app.tracekit.dev/traces

### Production
View traces at: https://app.tracekit.dev

### Code Monitoring
View snapshots at: http://app.tracekit.dev

## Configuration

All TraceKit configuration is in your `.env` file:

| Variable | Description | Default | Example |
|----------|-------------|---------|---------|
| `TRACEKIT_API_KEY` | Your TraceKit API key | (required) | `ctxio_abc123...` |
| `TRACEKIT_SERVICE_NAME` | Name of this service | `laravel-test-app` | `my-api-service` |
| `TRACEKIT_ENDPOINT` | TraceKit server endpoint | `https://api.tracekit.dev/v1/traces` | `https://api.tracekit.dev/v1/traces` |
| `TRACEKIT_ENABLED` | Enable/disable tracing | `true` | `false` |
| `TRACEKIT_CODE_MONITORING_ENABLED` | Enable code snapshots | `true` | `false` |

## Troubleshooting

### "TRACEKIT_API_KEY not configured"
- Ensure you've copied `.env.example` to `.env`
- Add your API key to the `TRACEKIT_API_KEY` variable
- Restart the server: `php artisan serve`

### Traces not appearing in dashboard
- Check that TraceKit server is running
- Verify `TRACEKIT_ENABLED=true` in `.env`
- Check `TRACEKIT_ENDPOINT` is correct
- Look for errors in Laravel logs: `storage/logs/laravel.log`

### Database errors
- Ensure SQLite file exists: `touch database/database.sqlite`
- Run migrations: `php artisan migrate`
- Check database permissions

### Cross-service calls timing out
- Ensure other test services are running:
  - go test-app: http://localhost:8082
  - node-test: http://localhost:8084
  - python-test: http://localhost:5001
  - php-test: http://localhost:8086

## Production Deployment

When deploying to production:

### 1. Update Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
TRACEKIT_ENDPOINT=https://api.tracekit.dev/v1/traces
TRACEKIT_SERVICE_NAME=my-production-app
```

### 2. Optimize Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

### 3. Secure Secrets

- Never commit `.env` file to version control
- Use environment variables or secrets manager
- Rotate API keys regularly
- Generate new `APP_KEY` for each environment

## Learn More

- [TraceKit Documentation](https://docs.tracekit.dev)
- [TraceKit Laravel Package](https://github.com/Tracekit-Dev/laravel-apm)
- [Laravel Documentation](https://laravel.com/docs)

## License

MIT License - See main TraceKit repository for details.
