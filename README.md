# Laravel Base Setup

A modern Laravel application starter template with **Laravel Octane**, **FrankenPHP**, **Docker**, and comprehensive development tools.

**🎯 Ready-to-use features included:**
- ✅ Complete authentication system (register/login/logout)
- ✅ API documentation with Scramble (auto-generated)
- ✅ Standardized API responses
- ✅ Database migrations and user management

## 🚀 Features

- **Laravel 12** with the latest PHP 8.4
- **Laravel Octane** with **FrankenPHP** for high-performance HTTP server
- **Docker** containerization with Laravel Sail
- **MySQL 8.0** database with PHPMyAdmin
- **Redis** for caching and session storage
- **Meilisearch** for full-text search capabilities
- **Mailpit** for email testing and debugging
- **✅ Laravel Sanctum** - Complete authentication system with login/register/logout endpoints **already implemented**
- **✅ Scramble API Documentation** - Automatic OpenAPI documentation generation **pre-configured and ready**
- **Vite** with TailwindCSS 4.0 for modern frontend development
- **ApiResponse Helper** - Standardized API response formatting included
- Pre-configured testing environment with PHPUnit

## 📋 Summary

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Development](#development)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Services](#services)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## 🛠 Requirements

The project is fully containerized using Docker, so you only need:

- **Docker** (version 20.10 or higher)
- **Docker Compose** (version 2.0 or higher)
- **Git**

No need for local PHP, MySQL, or other dependencies!

## 📦 Installation

1. **Clone the repository:**
   ```bash
   git clone <your-repository-url>
   cd laravel-base-setup
   ```

2. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Start the development environment:**
   ```bash
   ./run.sh
   ```

The `run.sh` script will:
- Build and start all Docker containers
- Set proper permissions
- Enter the container bash automatically

4. **Install dependencies (inside the container):**
   ```bash
   composer install
   npm install
   ```

5. **Generate application key and run migrations:**
   ```bash
   php artisan key:generate
   php artisan migrate
   ```

   **Note:** The authentication system is ready to use immediately after migration! The included migrations set up:
   - Users table with soft deletes
   - Personal access tokens table (Sanctum)
   - Admin user functionality
   - Cache and jobs tables

## ⚙️ Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Application
APP_NAME=your-app-name
APP_PORT=8080
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=your-database
DB_USERNAME=root
DB_PASSWORD=your-password

# FrankenPHP/Octane
OCTANE_SERVER=frankenphp

# Ports
PHPMYADMIN_PORT=3000
FORWARD_DB_PORT=3306
FORWARD_REDIS_PORT=6379
FORWARD_MEILISEARCH_PORT=7700
```

### FrankenPHP Configuration

The application uses FrankenPHP as the Octane server for superior performance. Configuration is handled automatically through Docker Compose and Laravel Octane.

## 🏃‍♂️ Usage

### Starting the Application

```bash
# Start all services
./run.sh

# Or manually with Docker Compose
docker-compose up -d
docker-compose exec laravel.test bash
```

### Accessing Services

Once started, you can access:

- **Laravel Application**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:3000
- **Mailpit (Email Testing)**: http://localhost:8025
- **Meilisearch**: http://localhost:7700

### Basic Commands

```bash
# Inside the container:

# Start Octane with FrankenPHP (automatic with docker-compose)
php artisan octane:start --server=frankenphp

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Clear caches
php artisan optimize:clear

# Generate API documentation
php artisan scramble:docs
```

## 👨‍💻 Development

### Frontend Development

The project uses Vite with TailwindCSS 4.0:

```bash
# Start Vite development server (inside container)
npm run dev

# Build for production
npm run build
```

### Database Management

```bash
# Create migration
php artisan make:migration create_example_table

# Create model with migration and factory
php artisan make:model Example -mf

# Run specific migration
php artisan migrate --path=/database/migrations/specific_migration.php

# Rollback migrations
php artisan migrate:rollback
```

### API Development

The starter **includes a complete authentication system ready to use**:

✅ **Fully Implemented Features:**
- User registration with validation
- User login with credential verification  
- User logout with token revocation
- Laravel Sanctum token-based authentication
- `ApiResponse` helper for consistent responses
- Database migrations for users and tokens
- Soft delete functionality for users
- Admin user support

```bash
# Create additional API controllers
php artisan make:controller Api/V1/ExampleController --api

# Create API resources
php artisan make:resource ExampleResource
```

**API Routes** are organized in:
- `routes/api_v1.php` - Version 1 API routes (authentication included)
- `routes/api.php` - General API routes

**Authentication Usage Example:**
```php
// Protected route example
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

## 📚 API Documentation

This setup uses **Scramble** for automatic API documentation generation, which is **already configured and ready to use**.

### Accessing Documentation

- **Scramble Documentation**: http://localhost:8080/docs/api
- The documentation is automatically generated from your controller docblocks and type hints
- **No manual configuration required** - just start coding your APIs!

### Generating Documentation

```bash
# Generate API docs (runs automatically in development)
php artisan scramble:docs
```

### 🔐 Authentication System (Ready to Use)

The starter **includes a complete Laravel Sanctum authentication system** with these endpoints already implemented:

- `POST /api/v1/register` - User registration
- `POST /api/v1/login` - User login  
- `POST /api/v1/logout` - User logout (requires authentication)

**AuthController is fully implemented** in `app/Http/Controllers/Api/V1/AuthController.php` with:
- Input validation
- Password hashing
- Token generation
- Error handling
- Standardized API responses

### API Response Helper

The project includes a custom `ApiResponse` helper class that provides:
- Consistent JSON response formatting
- Automatic error handling
- Success/error status codes
- Exception handling

Example usage:
```php
return ApiResponse::handle(function() {
    // Your logic here
    return ['data' => 'success'];
});
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run tests with coverage
php artisan test --coverage
```

### Database Testing

The setup includes automatic test database configuration. Tests use the `testing` database which is automatically created and migrated.

## 🐳 Services

The Docker setup includes the following services:

### Core Services

- **laravel.test**: Main Laravel application with FrankenPHP
- **mysql**: MySQL 8.0 database server
- **redis**: Redis for caching and sessions

### Development Services

- **phpmyadmin**: Web-based MySQL administration
- **mailpit**: Email testing and debugging
- **meilisearch**: Full-text search engine

### Service Health Checks

All services include health checks to ensure proper startup order and availability.

## 🚀 Deployment

### Production Considerations

1. **Environment Configuration:**
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   OCTANE_SERVER=frankenphp
   ```

2. **Database:**
   - Use managed database service
   - Configure proper backup strategies
   - Set up read replicas if needed

3. **Caching:**
   - Configure Redis for production
   - Enable OPcache
   - Use application-level caching

4. **Security:**
   - Update all secret keys
   - Configure CORS properly
   - Set up SSL/TLS certificates
   - Enable rate limiting

### Docker Production Setup

```bash
# Build production image
docker-compose -f docker-compose.production.yml build

# Deploy with production configuration
docker-compose -f docker-compose.production.yml up -d
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use meaningful commit messages

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🆘 Troubleshooting

### Common Issues

1. **Port Conflicts:**
   ```bash
   # Check if ports are in use
   lsof -i :8080
   # Modify APP_PORT in .env if needed
   ```

2. **Permission Issues:**
   ```bash
   # Fix storage permissions
   chmod -R 775 storage bootstrap/cache
   ```

3. **Database Connection:**
   ```bash
   # Check MySQL container logs
   docker-compose logs mysql
   
   # Reset database
   docker-compose down -v
   docker-compose up -d
   ```

4. **Clear All Caches:**
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

### Performance Optimization

- **Octane Memory Management**: Monitor memory usage and restart workers periodically
- **Database Indexing**: Add proper indexes for frequently queried columns
- **Caching Strategy**: Implement Redis caching for expensive operations
- **Asset Optimization**: Use Vite's build process for production assets

---

**Built with ❤️ using Laravel, Octane, FrankenPHP, and Docker** 
