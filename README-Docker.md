# SmartMart Docker Development Environment

This Docker setup provides a complete development environment for the SmartMart e-commerce platform with all required services.

## 🚀 Quick Start

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) (20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (2.0 or higher)
- Git (for version control)

### Setup

1. **Clone the repository** (if not already done):
   ```bash
   git clone <repository-url>
   cd SmartMart
   ```

2. **Set up environment file**:
   ```bash
   cp .env.docker .env
   ```
   Edit `.env` with your preferred settings.

3. **Start the development environment**:
   
   **Linux/macOS:**
   ```bash
   chmod +x docker-dev.sh
   ./docker-dev.sh start
   ```
   
   **Windows:**
   ```cmd
   docker-dev.bat start
   ```

4. **Access the application**:
   - **Main Application**: http://localhost:8000
   - **PHPMyAdmin**: http://localhost:8080
   - **MailHog**: http://localhost:8025
   - **Redis Commander**: http://localhost:8081
   - **Meilisearch**: http://localhost:7700

## 📦 Services Included

| Service | Container Name | Port | Description |
|---------|---------------|------|-------------|
| **App** | smartmart_app | 9000 | PHP-FPM application server |
| **Web Server** | smartmart_webserver | 8000, 443 | Nginx web server |
| **Database** | smartmart_database | 3306 | MySQL 8.0 database |
| **Redis** | smartmart_redis | 6379 | Redis cache and session store |
| **Meilisearch** | smartmart_meilisearch | 7700 | Search engine |
| **Queue Worker** | smartmart_queue | - | Laravel queue worker |
| **Scheduler** | smartmart_scheduler | - | Laravel task scheduler |
| **Node.js** | smartmart_node | 5173 | Frontend development server |
| **MailHog** | smartmart_mailhog | 1025, 8025 | Email testing tool |
| **PHPMyAdmin** | smartmart_phpmyadmin | 8080 | Database management |
| **Redis Commander** | smartmart_redis_commander | 8081 | Redis management |

## 🛠️ Development Commands

### Using Helper Scripts

The helper scripts provide convenient commands for common development tasks:

**Linux/macOS:**
```bash
./docker-dev.sh <command>
```

**Windows:**
```cmd
docker-dev.bat <command>
```

### Available Commands

| Command | Description |
|---------|-------------|
| `start` | Build and start the development environment |
| `stop` | Stop all services |
| `restart` | Restart all services |
| `status` | Show service status |
| `logs [service]` | Show logs (optionally for specific service) |
| `shell` | Access application shell |
| `artisan <command>` | Run Laravel Artisan commands |
| `composer <command>` | Run Composer commands |
| `npm <command>` | Run NPM commands |
| `test` | Run application tests |
| `update` | Update application and dependencies |
| `clean` | Clean up all Docker resources |
| `help` | Show help message |

### Examples

```bash
# Start the development environment
./docker-dev.sh start

# Run database migrations
./docker-dev.sh artisan migrate

# Install a new package
./docker-dev.sh composer require vendor/package

# Run frontend development server
./docker-dev.sh npm run dev

# Run tests
./docker-dev.sh test

# View application logs
./docker-dev.sh logs app

# Access application shell
./docker-dev.sh shell
```

## 🔧 Manual Docker Commands

If you prefer using Docker Compose directly:

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f

# Execute commands in containers
docker-compose exec app php artisan migrate
docker-compose exec app composer install
docker-compose exec node npm install

# Access container shell
docker-compose exec app bash
```

## 📁 Directory Structure

```
SmartMart/
├── docker/
│   ├── nginx/
│   │   └── default.conf          # Nginx configuration
│   ├── php/
│   │   └── local.ini             # PHP configuration
│   ├── mysql/
│   │   └── my.cnf               # MySQL configuration
│   ├── supervisor/
│   │   └── supervisord.conf     # Supervisor configuration
│   └── entrypoint.sh            # Application entrypoint script
├── docker-compose.yml           # Docker Compose configuration
├── Dockerfile                   # Application Docker image
├── .env.docker                  # Docker environment template
├── docker-dev.sh               # Linux/macOS helper script
├── docker-dev.bat              # Windows helper script
└── README-Docker.md            # This file
```

## 🗄️ Database Management

### Accessing the Database

1. **PHPMyAdmin**: http://localhost:8080
   - Server: `database`
   - Username: `smartmart` (or your configured username)
   - Password: `smartmart_password` (or your configured password)

2. **Command Line**:
   ```bash
   ./docker-dev.sh shell
   mysql -h database -u smartmart -p smartmart
   ```

### Database Operations

```bash
# Run migrations
./docker-dev.sh artisan migrate

# Seed database
./docker-dev.sh artisan db:seed

# Fresh migration with seeding
./docker-dev.sh artisan migrate:fresh --seed

# Create backup
./docker-dev.sh artisan backup:run
```

## 🔍 Search Engine (Meilisearch)

Meilisearch is configured for product search functionality.

### Management

- **Dashboard**: http://localhost:7700
- **Master Key**: Set in `.env` as `MEILISEARCH_KEY`

### Index Products

```bash
# Import products to search index
./docker-dev.sh artisan scout:import "App\Models\Product"

# Flush and reimport
./docker-dev.sh artisan scout:flush "App\Models\Product"
./docker-dev.sh artisan scout:import "App\Models\Product"
```

## 📧 Email Testing (MailHog)

MailHog captures all outgoing emails in development.

- **Web Interface**: http://localhost:8025
- **SMTP Server**: `mailhog:1025`

All emails sent by the application will appear in the MailHog web interface.

## 🔄 Cache Management (Redis)

Redis is used for caching, sessions, and queues.

### Redis Commander

Access Redis Commander at http://localhost:8081 to manage Redis data.

### Cache Commands

```bash
# Clear application cache
./docker-dev.sh artisan cache:clear

# Clear configuration cache
./docker-dev.sh artisan config:clear

# Clear route cache
./docker-dev.sh artisan route:clear

# Clear view cache
./docker-dev.sh artisan view:clear
```

## 🏃‍♂️ Queue Management

Background jobs are processed by the queue worker service.

### Queue Commands

```bash
# View queue status
./docker-dev.sh artisan queue:work --verbose

# Process specific queue
./docker-dev.sh artisan queue:work --queue=high,default

# Clear failed jobs
./docker-dev.sh artisan queue:clear

# Restart queue workers
./docker-dev.sh artisan queue:restart
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
./docker-dev.sh test

# Run specific test suite
./docker-dev.sh artisan test --testsuite=Feature

# Run tests with coverage
./docker-dev.sh artisan test --coverage
```

### Test Database

Tests use a separate SQLite database in memory for isolation.

## 🔒 Security Considerations

### Development Security

- Default passwords are used for development
- Debug mode is enabled
- HTTPS is not configured by default
- All services are accessible on localhost

### Production Preparation

Before deploying to production:

1. Change all default passwords
2. Enable HTTPS with proper certificates
3. Configure firewall rules
4. Set `APP_DEBUG=false`
5. Use production-grade secret keys
6. Configure proper backup strategies

## 🚨 Troubleshooting

### Common Issues

1. **Port Conflicts**:
   ```bash
   # Check what's using a port
   netstat -tulpn | grep :8000
   
   # Stop conflicting services
   sudo systemctl stop apache2  # Ubuntu/Debian
   sudo systemctl stop httpd    # CentOS/RHEL
   ```

2. **Permission Issues**:
   ```bash
   # Fix storage permissions
   ./docker-dev.sh shell
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

3. **Database Connection Issues**:
   ```bash
   # Restart database service
   docker-compose restart database
   
   # Check database logs
   ./docker-dev.sh logs database
   ```

4. **Memory Issues**:
   ```bash
   # Increase Docker memory allocation
   # Docker Desktop -> Settings -> Resources -> Memory
   ```

### Logs and Debugging

```bash
# View all logs
./docker-dev.sh logs

# View specific service logs
./docker-dev.sh logs app
./docker-dev.sh logs database
./docker-dev.sh logs redis

# Follow logs in real-time
docker-compose logs -f app
```

### Rebuilding Images

```bash
# Rebuild specific service
docker-compose build app

# Rebuild all services
docker-compose build --no-cache

# Pull latest base images
docker-compose pull
```

## 🔄 Updates and Maintenance

### Updating the Application

```bash
# Update application and dependencies
./docker-dev.sh update
```

This will:
- Pull latest code changes
- Rebuild Docker images
- Update Composer dependencies
- Update NPM dependencies
- Run database migrations
- Clear caches
- Restart services

### Cleaning Up

```bash
# Clean up Docker resources
./docker-dev.sh clean
```

**Warning**: This will remove all containers, images, and volumes.

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Redis Documentation](https://redis.io/documentation)
- [Meilisearch Documentation](https://docs.meilisearch.com/)

## 🤝 Support

If you encounter any issues with the Docker setup:

1. Check the troubleshooting section above
2. Review service logs: `./docker-dev.sh logs [service]`
3. Ensure all prerequisites are installed
4. Try rebuilding images: `docker-compose build --no-cache`
5. Clean up and start fresh: `./docker-dev.sh clean && ./docker-dev.sh start`

For application-specific issues, refer to the main application documentation.