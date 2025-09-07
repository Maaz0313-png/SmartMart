@echo off
setlocal enabledelayedexpansion

:: SmartMart Docker Development Helper Script for Windows

:: Colors (if supported)
set "RED=[91m"
set "GREEN=[92m"
set "YELLOW=[93m"
set "BLUE=[94m"
set "NC=[0m"

:: Function to print status messages
goto main

:print_status
echo %BLUE%[INFO]%NC% %~1
goto :eof

:print_success
echo %GREEN%[SUCCESS]%NC% %~1
goto :eof

:print_warning
echo %YELLOW%[WARNING]%NC% %~1
goto :eof

:print_error
echo %RED%[ERROR]%NC% %~1
goto :eof

:check_requirements
call :print_status "Checking requirements..."

where docker >nul 2>&1
if %errorlevel% neq 0 (
    call :print_error "Docker is not installed. Please install Docker Desktop first."
    exit /b 1
)

where docker-compose >nul 2>&1
if %errorlevel% neq 0 (
    call :print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit /b 1
)

call :print_success "Docker and Docker Compose are installed."
goto :eof

:setup_env
if not exist .env (
    call :print_status "Creating environment file..."
    copy .env.docker .env >nul
    call :print_success "Environment file created. Please review and update .env with your settings."
) else (
    call :print_warning "Environment file already exists."
)
goto :eof

:start
call :print_status "Starting SmartMart development environment..."

call :print_status "Building Docker images..."
docker-compose build

call :print_status "Starting services..."
docker-compose up -d

call :print_status "Waiting for services to be ready..."
timeout /t 30 /nobreak >nul

call :print_status "Running initial application setup..."
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
docker-compose exec app php artisan storage:link

call :print_success "SmartMart is now running!"
call :print_status "Application: http://localhost:8000"
call :print_status "PHPMyAdmin: http://localhost:8080"
call :print_status "MailHog: http://localhost:8025"
call :print_status "Redis Commander: http://localhost:8081"
call :print_status "Meilisearch: http://localhost:7700"
goto :eof

:stop
call :print_status "Stopping SmartMart development environment..."
docker-compose down
call :print_success "Services stopped."
goto :eof

:restart
call :print_status "Restarting SmartMart development environment..."
docker-compose restart
call :print_success "Services restarted."
goto :eof

:logs
if "%~2"=="" (
    docker-compose logs -f
) else (
    docker-compose logs -f %~2
)
goto :eof

:artisan
shift
docker-compose exec app php artisan %*
goto :eof

:composer
shift
docker-compose exec app composer %*
goto :eof

:npm
shift
docker-compose exec node npm %*
goto :eof

:shell
docker-compose exec app bash
goto :eof

:test
call :print_status "Running tests..."
docker-compose exec app php artisan test
goto :eof

:clean
call :print_warning "This will remove all containers, images, and volumes. Are you sure? (y/N)"
set /p response="Enter your choice: "
if /i "!response!"=="y" (
    call :print_status "Cleaning up..."
    docker-compose down -v --remove-orphans
    docker system prune -af --volumes
    call :print_success "Cleanup complete."
) else (
    call :print_status "Cleanup cancelled."
)
goto :eof

:status
call :print_status "SmartMart Service Status:"
docker-compose ps
goto :eof

:update
call :print_status "Updating SmartMart application..."

if exist .git (
    call :print_status "Pulling latest code..."
    git pull
)

call :print_status "Rebuilding images..."
docker-compose build --no-cache

call :print_status "Updating dependencies..."
docker-compose exec app composer install --optimize-autoloader
docker-compose exec node npm ci

call :print_status "Running migrations..."
docker-compose exec app php artisan migrate --force

call :print_status "Clearing caches..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear

call :print_status "Restarting services..."
docker-compose restart

call :print_success "Update complete!"
goto :eof

:help
echo SmartMart Docker Development Helper for Windows
echo.
echo Usage: %0 ^<command^> [options]
echo.
echo Commands:
echo   start     - Build and start the development environment
echo   stop      - Stop all services
echo   restart   - Restart all services
echo   status    - Show service status
echo   logs      - Show logs (optionally for specific service)
echo   shell     - Access application shell
echo   artisan   - Run Laravel Artisan commands
echo   composer  - Run Composer commands
echo   npm       - Run NPM commands
echo   test      - Run application tests
echo   update    - Update application and dependencies
echo   clean     - Clean up all Docker resources
echo   help      - Show this help message
echo.
echo Examples:
echo   %0 start                          # Start the application
echo   %0 logs app                       # Show app service logs
echo   %0 artisan migrate:fresh --seed   # Run fresh migrations with seeding
echo   %0 composer require package-name  # Install a new package
echo   %0 npm run dev                    # Run npm development script
goto :eof

:main
if "%~1"=="start" (
    call :check_requirements
    call :setup_env
    call :start
) else if "%~1"=="stop" (
    call :stop
) else if "%~1"=="restart" (
    call :restart
) else if "%~1"=="status" (
    call :status
) else if "%~1"=="logs" (
    call :logs %*
) else if "%~1"=="shell" (
    call :shell
) else if "%~1"=="artisan" (
    call :artisan %*
) else if "%~1"=="composer" (
    call :composer %*
) else if "%~1"=="npm" (
    call :npm %*
) else if "%~1"=="test" (
    call :test
) else if "%~1"=="update" (
    call :update
) else if "%~1"=="clean" (
    call :clean
) else if "%~1"=="help" (
    call :help
) else if "%~1"=="--help" (
    call :help
) else if "%~1"=="-h" (
    call :help
) else if "%~1"=="" (
    call :help
) else (
    call :print_error "Unknown command: %~1"
    call :help
    exit /b 1
)