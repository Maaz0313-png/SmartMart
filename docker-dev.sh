#!/bin/bash

# SmartMart Docker Development Helper Script

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker and Docker Compose are installed
check_requirements() {
    print_status "Checking requirements..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    print_success "Docker and Docker Compose are installed."
}

# Setup environment file
setup_env() {
    if [ ! -f .env ]; then
        print_status "Creating environment file..."
        cp .env.docker .env
        print_success "Environment file created. Please review and update .env with your settings."
    else
        print_warning "Environment file already exists."
    fi
}

# Build and start the application
start() {
    print_status "Starting SmartMart development environment..."
    
    # Build images
    print_status "Building Docker images..."
    docker-compose build
    
    # Start services
    print_status "Starting services..."
    docker-compose up -d
    
    # Wait for services to be ready
    print_status "Waiting for services to be ready..."
    sleep 30
    
    # Run initial setup
    print_status "Running initial application setup..."
    docker-compose exec app php artisan key:generate
    docker-compose exec app php artisan migrate --force
    docker-compose exec app php artisan db:seed --force
    docker-compose exec app php artisan storage:link
    
    print_success "SmartMart is now running!"
    print_status "Application: http://localhost:8000"
    print_status "PHPMyAdmin: http://localhost:8080"
    print_status "MailHog: http://localhost:8025"
    print_status "Redis Commander: http://localhost:8081"
    print_status "Meilisearch: http://localhost:7700"
}

# Stop the application
stop() {
    print_status "Stopping SmartMart development environment..."
    docker-compose down
    print_success "Services stopped."
}

# Restart the application
restart() {
    print_status "Restarting SmartMart development environment..."
    docker-compose restart
    print_success "Services restarted."
}

# View logs
logs() {
    if [ -z "$2" ]; then
        docker-compose logs -f
    else
        docker-compose logs -f "$2"
    fi
}

# Execute artisan commands
artisan() {
    shift
    docker-compose exec app php artisan "$@"
}

# Execute composer commands
composer() {
    shift
    docker-compose exec app composer "$@"
}

# Execute npm commands
npm() {
    shift
    docker-compose exec node npm "$@"
}

# Access application shell
shell() {
    docker-compose exec app bash
}

# Run tests
test() {
    print_status "Running tests..."
    docker-compose exec app php artisan test
}

# Clean up everything
clean() {
    print_warning "This will remove all containers, images, and volumes. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_status "Cleaning up..."
        docker-compose down -v --remove-orphans
        docker system prune -af --volumes
        print_success "Cleanup complete."
    else
        print_status "Cleanup cancelled."
    fi
}

# Show application status
status() {
    print_status "SmartMart Service Status:"
    docker-compose ps
}

# Update application
update() {
    print_status "Updating SmartMart application..."
    
    # Pull latest changes (if in git repo)
    if [ -d ".git" ]; then
        print_status "Pulling latest code..."
        git pull
    fi
    
    # Rebuild images
    print_status "Rebuilding images..."
    docker-compose build --no-cache
    
    # Update dependencies
    print_status "Updating dependencies..."
    docker-compose exec app composer install --optimize-autoloader
    docker-compose exec node npm ci
    
    # Run migrations
    print_status "Running migrations..."
    docker-compose exec app php artisan migrate --force
    
    # Clear caches
    print_status "Clearing caches..."
    docker-compose exec app php artisan config:clear
    docker-compose exec app php artisan cache:clear
    docker-compose exec app php artisan view:clear
    docker-compose exec app php artisan route:clear
    
    # Restart services
    print_status "Restarting services..."
    docker-compose restart
    
    print_success "Update complete!"
}

# Show help
help() {
    echo "SmartMart Docker Development Helper"
    echo ""
    echo "Usage: $0 <command> [options]"
    echo ""
    echo "Commands:"
    echo "  start     - Build and start the development environment"
    echo "  stop      - Stop all services"
    echo "  restart   - Restart all services"
    echo "  status    - Show service status"
    echo "  logs      - Show logs (optionally for specific service)"
    echo "  shell     - Access application shell"
    echo "  artisan   - Run Laravel Artisan commands"
    echo "  composer  - Run Composer commands"
    echo "  npm       - Run NPM commands"
    echo "  test      - Run application tests"
    echo "  update    - Update application and dependencies"
    echo "  clean     - Clean up all Docker resources"
    echo "  help      - Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 start                          # Start the application"
    echo "  $0 logs app                       # Show app service logs"
    echo "  $0 artisan migrate:fresh --seed   # Run fresh migrations with seeding"
    echo "  $0 composer require package-name  # Install a new package"
    echo "  $0 npm run dev                    # Run npm development script"
}

# Main script logic
case "$1" in
    start)
        check_requirements
        setup_env
        start
        ;;
    stop)
        stop
        ;;
    restart)
        restart
        ;;
    status)
        status
        ;;
    logs)
        logs "$@"
        ;;
    shell)
        shell
        ;;
    artisan)
        artisan "$@"
        ;;
    composer)
        composer "$@"
        ;;
    npm)
        npm "$@"
        ;;
    test)
        test
        ;;
    update)
        update
        ;;
    clean)
        clean
        ;;
    help|--help|-h)
        help
        ;;
    *)
        print_error "Unknown command: $1"
        help
        exit 1
        ;;
esac