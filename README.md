
---

# Laravel API Gateway

A lightweight API gateway microservice built with Laravel, designed to relay requests from decentralized sources to downstream services while providing robust logging, monitoring, throttling, and mock authentication capabilities.

## Features
- **Request Relaying**: Proxies incoming requests to configured downstream services.
- **Logging & Monitoring**: Logs request metrics (timestamps, response times, status codes, etc.) in a database with transaction support.
- **Gateway Throttling**: Limits requests to 60 per minute per IP/user, implemented in `RouteServiceProvider`.
- **Mock Authentication**: Simulates JWT authentication using an environment variable.
- **Scalability**: Modular design with middleware, controllers, and services for easy extension.

## Prerequisites
- PHP >= 8.1
- Composer
- MySQL or another supported database
- Laravel CLI (optional, for artisan commands)
- Postman (for testing)

## Setup Instructions

### 1. Clone the Repository
```bash
git clone https://github.com/stefanmitrea01/api-gateway.git
cd api-gateway
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
- Copy the example environment file:
  ```bash
  cp .env.example .env
  ```
- Edit `.env` with your database credentials and preferences:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=api_gateway
  DB_USERNAME=root
  DB_PASSWORD=

  GATEWAY_AUTH_ENABLED=true
  GATEWAY_AUTH_MOCK=true
  MOCK_JWT_TOKEN=simulated-jwt-token-for-testing
  ```

### 4. Build and Start Containers
```bash
docker-compose up -d --build
```

### 5. Initialize Application
```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

### 6. Configure Downstream Service
Edit `config/gateway.php` to point to your target service:
```php
'routes' => [
    'api/v1/*' => 'https://jsonplaceholder.typicode.com', // Public test API
],
```

### 7. Start the Server
```bash
php artisan serve
```
- Default URL: `http://localhost:8000`

## Usage Examples

### Testing with Postman

#### Successful GET Request
- **URL**: `http://localhost:8000/api/v1/posts`
- **Method**: `GET`
- **Headers**:
    - `Authorization: Bearer simulated-jwt-token-for-testing`
    - `Accept: application/json`
- **Response**: JSON list of posts from JSONPlaceholder.
- **Logs**: Check `request_logs` table.

#### POST Request with Body
- **URL**: `http://localhost:8000/api/v1/posts`
- **Method**: `POST`
- **Headers**:
    - `Authorization: Bearer simulated-jwt-token-for-testing`
    - `Content-Type: application/json`
- **Body** (raw JSON):
  ```json
  {
      "title": "Test Post",
      "body": "This is a test",
      "userId": 1
  }
  ```
- **Response**: Created post data (201 Created).

#### Gateway Throttling Test
- Send 70+ `GET` requests to `http://localhost:8000/api/v1/test` in one minute using Postman’s Collection Runner.
- **Expected**: First 60 succeed (200), then 429 Too Many Requests due to gateway throttling in `RouteServiceProvider`.

#### Failed Request
- Update `config/gateway.php` to a non-existent service:
  ```php
  'routes' => [
      'api/v1/*' => 'http://non-existent-service:9999'
  ]
  ```
- **URL**: `http://localhost:8000/api/v1/test`
- **Response**: 502 Bad Gateway, logged in `request_logs`.

## Design Choices

### Architecture
- **Middleware**:
    - `AuthMiddleware`: Handles mock JWT authentication, easily replaceable with real JWT.
- **Controller** (`GatewayController`): Orchestrates request handling, delegating to services.
- **Services**:
    - `GatewayService`: Manages request relaying with error handling.
    - `LoggingService`: Logs metrics in a database with transactions.
- **Model** (`RequestLog`): Stores request details with JSON casting.
- **Gateway Throttling**: Implemented in `RouteServiceProvider` using Laravel’s `RateLimiter` with a custom `gateway:` key prefix for gateway-specific rate limiting.

### Key Decisions
1. **Modularity**: Separated concerns into controller and services for scalability and maintainability.
2. **Config-Driven**: Routes, logging, and auth settings are configurable via `config/gateway.php` and `.env`.
3. **Error Handling**: Catches downstream failures, logs them as 502.
4. **Gateway Throttling in RouteServiceProvider**: Implemented throttling in `RouteServiceProvider` for centralized, route-level control, using Laravel’s `RateLimiter` with configuration from `gateway.throttle`.
5. **Mock Auth**: Simulates JWT with a configurable token.

### Architectural Diagram (Text-Based)
```
[Client] --> [Postman]
    |
    v
[API Gateway (Laravel)]
    ├── RouteServiceProvider (Gateway Throttling)
    ├── AuthMiddleware (JWT Mock)
    ├── GatewayController
    │    ├── GatewayService (Relay Requests)
    │    └── LoggingService (Log Metrics)
    └── Database (Request Logs)
    |
    v
[Downstream Service]
    e.g., JSONPlaceholder, Local Dummy
```

## Troubleshooting
- **403 Forbidden**: Ensure downstream service is accessible and add `User-Agent` if needed.
- **Rate Limiting Stuck**: Clear Redis (`php artisan cache:clear`) if using it and stuck at 429.
- **Logs Not Appearing**: Verify `logging.enabled` in `config/gateway.php`.
---
