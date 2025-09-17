# Pizza Employee Management System

A comprehensive employee onboarding and management system built with Laravel 12, Filament 3, and modern web technologies. This system streamlines the employee onboarding process with KYC verification, contract generation, and document management.

## 🚀 Features

### Core Functionality
- **Employee Onboarding**: Complete invitation workflow with secure magic links
- **KYC Verification**: Profile information collection with photo upload and verification
- **Contract Management**: Automated PDF contract generation with digital signatures
- **Document Center**: Centralized document storage and management
- **Multi-branch Support**: Organization across multiple locations with role-based access
- **Admin Interface**: Full Filament admin panel for HR management
- **Notification System**: Email and SMS invitation framework
- **Security Features**: Rate limiting, anti-automation, and attempt tracking

### Technical Features
- **Laravel 12**: Latest Laravel framework with modern PHP 8.2+ features
- **Filament 3**: Beautiful admin interface with comprehensive CRUD operations
- **Docker Support**: Complete containerized development environment
- **SMS Integration**: Support for 30+ Bangladeshi SMS providers
- **Queue System**: Background job processing for better performance
- **Multi-database Support**: MySQL, PostgreSQL, and SQLite compatibility
- **API Ready**: RESTful API endpoints for integration

## 📋 Requirements

### System Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **NPM**: 10.x or higher
- **Docker**: Stable version with Docker Compose

### Database Support
- MySQL 8.1.x (default)
- PostgreSQL 16.x
- SQLite (development)

## 🛠️ Installation & Setup

### Option 1: Docker Setup (Recommended)

#### First Time Setup
```bash
# Clone the repository
git clone <repository-url>
cd pizza

# Start the Docker environment
docker compose up -d --build

# Access the PHP container
docker compose exec php bash

# Run the setup script
composer setup
```

#### Subsequent Runs
```bash
# Start the environment
docker compose up -d
```

### Option 2: Local Development Setup

#### Prerequisites
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

#### Database Setup
```bash
# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Build frontend assets
npm run build
```

## 🚀 How to Deploy

### Development Environment
```bash
# Start all services
docker compose up -d

# Run development server with hot reload
composer dev
```

### Production Deployment

#### 1. Environment Configuration
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Configure database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

# Configure mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

# Configure SMS (optional)
SMS_DEFAULT_PROVIDER=SSL Wireless
SMS_SSL_API_TOKEN=your-token
```

#### 2. Production Setup
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Set up queue worker
php artisan queue:work --daemon
```

#### 3. Web Server Configuration

##### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/pizza/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

##### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/pizza/public

    <Directory /var/www/pizza/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/pizza_error.log
    CustomLog ${APACHE_LOG_DIR}/pizza_access.log combined
</VirtualHost>
```

## 🔧 Configuration

### Environment Variables

#### Application Settings
```env
APP_NAME="Pizza Employee Management"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost
```

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pizza_management
DB_USERNAME=root
DB_PASSWORD=
```

#### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@pizza.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### SMS Configuration
```env
SMS_DEFAULT_PROVIDER="SSL Wireless"
SMS_SSL_API_TOKEN=your-api-token
SMS_SSL_SID=your-sid
SMS_SSL_CSMS_ID=your-csms-id
```

### Filament Configuration

#### Admin Panel Access
- **URL**: `http://localhost/admin`
- **Default Admin**: Create via `php artisan make:filament-user`

#### Customization
```php
// config/filament.php
return [
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),
    'default_avatar_provider' => \Filament\AvatarProviders\UiAvatarsProvider::class,
];
```

## 📊 Database Schema

### Core Tables
- `users` - User authentication and basic info
- `employee_profiles` - Detailed employee information
- `branches` - Organization branches/locations
- `positions` - Job positions and roles
- `onboarding_invites` - Invitation management
- `onboarding_steps` - Step-by-step onboarding process
- `employment_contracts` - Contract management
- `employee_documents` - Document storage
- `kyc_verifications` - KYC verification data
- `contract_templates` - Contract templates
- `sms_settings` - SMS provider configurations

### Key Relationships
- Users belong to branches
- Employee profiles link to users, branches, and positions
- Onboarding invites create magic links for KYC access
- Contracts are generated from templates and stored with signatures

## 🔌 API Endpoints

### Authentication
```bash
# Generate magic link
POST /api/onboarding/invite
{
    "email": "employee@example.com",
    "phone": "01700000000",
    "branch_id": 1,
    "position_id": 1
}

# Access KYC form
GET /api/kyc/{token}
```

### SMS Integration
```bash
# Send SMS
POST /api/sms/send
{
    "mobile": "01700000000",
    "message": "Welcome to Pizza!"
}

# Test SMS provider
POST /api/sms/test-provider
{
    "provider": "SSL Wireless",
    "mobile": "01700000000"
}
```

### Employee Management
```bash
# Get employee profile
GET /api/employees/{id}

# Update employee profile
PUT /api/employees/{id}
{
    "first_name": "John",
    "last_name": "Doe"
}
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Code style fixing
vendor/bin/pint

# Static analysis
vendor/bin/phpstan analyse

# Code refactoring
vendor/bin/rector process
```

## 📝 Development

### Available Commands
```bash
# Development server with hot reload
composer dev

# Queue worker
php artisan queue:work

# Clear caches
php artisan optimize:clear

# Generate Filament user
php artisan make:filament-user
```

### Code Structure
```
app/
├── Filament/Resources/     # Admin panel resources
├── Http/Controllers/       # API controllers
├── Mail/                  # Email notifications
├── Models/                # Eloquent models
├── Services/              # Business logic services
└── Providers/             # Service providers

database/
├── migrations/            # Database migrations
├── seeders/              # Database seeders
└── factories/            # Model factories
```

## 🔒 Security Features

### Authentication & Authorization
- Token-based magic link authentication
- Role-based access control (RBAC)
- Rate limiting and throttling
- IP tracking and device fingerprinting
- Session management and revocation

### Data Protection
- Encrypted PII columns
- Secure file storage
- Audit logging
- CSRF protection
- SQL injection prevention

## 📱 SMS Integration

### Supported Providers
The system supports 30+ Bangladeshi SMS providers including:
- SSL Wireless
- MimSMS
- Alpha SMS
- Banglalink
- BoomCast
- And many more...

### Configuration
1. Access Filament admin panel
2. Navigate to Settings > SMS Gateways
3. Configure provider credentials
4. Set default provider
5. Test configuration

## 🐳 Docker Services

### Available Services
- **PHP**: Application server (Port 5173)
- **Nginx**: Web server (Port 80)
- **MySQL**: Database (Port 3306) - **Persistent Storage**
- **phpMyAdmin**: Database management (Port 8080)
- **Adminer**: Alternative DB tool (Port 9090)
- **Mailpit**: Email testing (Port 8025)
- **Redis**: Cache and sessions (Port 6379) - **Persistent Storage**

### Service Access
- **Application**: http://localhost
- **Admin Panel**: http://localhost/admin
- **phpMyAdmin**: http://localhost:8080
- **Mailpit**: http://localhost:8025
- **Adminer**: http://localhost:9090

### Docker Volume Management

#### View Persistent Data
```bash
# Check database data size
du -sh .docker/db/data

# Check Redis data size
du -sh .docker/redis/data

# List all Docker volumes
docker volume ls
```

#### Reset Database (⚠️ Destroys all data)
```bash
# Stop services
docker compose down

# Remove database data (DANGER: This deletes all data!)
rm -rf .docker/db/data/*

# Remove Redis data (DANGER: This deletes all cache!)
rm -rf .docker/redis/data/*

# Start services (will recreate empty database)
docker compose up -d

# Run migrations to recreate tables
docker compose exec php php artisan migrate
```

#### Clean Up Docker Resources
```bash
# Remove unused containers, networks, images
docker system prune

# Remove all stopped containers
docker container prune

# Remove unused volumes (be careful!)
docker volume prune
```

## 📚 Documentation

### Additional Documentation
- [Architecture Overview](docs/architecture.md)
- [SMS Integration Guide](docs/SMS_INTEGRATION.md)
- [API Documentation](docs/api/openapi.yaml)
- [System Requirements](docs/SRS.md)

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code quality checks
5. Submit a pull request

### Code Standards
- Follow PSR-12 coding standards
- Write comprehensive tests
- Document new features
- Use meaningful commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

### Common Issues

#### Database Connection Issues
```bash
# Check database status
docker compose ps db

# View database logs
docker compose logs db

# Reset database
docker compose down
docker compose up -d --build
```

#### Permission Issues
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Queue Issues
```bash
# Check queue status
php artisan queue:work --verbose

# Clear failed jobs
php artisan queue:flush
```

### Getting Help
- Check the [documentation](docs/)
- Review [GitHub issues](https://github.com/your-repo/issues)
- Contact the development team

## 🔄 Updates & Maintenance

### Regular Maintenance
```bash
# Update dependencies
composer update
npm update

# Clear caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Restart services
docker compose restart
```

### Persistent Storage

Your Docker setup is configured with **persistent storage** that survives container rebuilds:

#### Database Persistence
- **Data Location**: `.docker/db/data/` → `/var/lib/mysql`
- **Logs Location**: `.docker/logs/` → `/var/log/mysql`
- **Configuration**: `.docker/db/my.cnf` → `/etc/mysql/conf.d/my.cnf`

#### Redis Persistence
- **Data Location**: `.docker/redis/data/` → `/data`
- **Configuration**: AOF (Append Only File) enabled

#### What Persists
✅ **Database tables and data**  
✅ **User accounts and sessions**  
✅ **Redis cache data**  
✅ **Database logs and configuration**  
✅ **phpMyAdmin sessions**  

#### What Doesn't Persist
❌ **Container filesystem changes**  
❌ **Installed packages in containers**  
❌ **Environment variable changes**  

### Backup Strategy

#### Automated Backup Scripts
```bash
# Create a complete backup (SQL + compressed data)
./scripts/backup-database.sh

# Restore from backup
./scripts/restore-database.sh ./backups/database_20241201_143022.sql
```

#### Manual Backup Commands
```bash
# SQL dump backup
docker exec pizza-db-1 mysqldump -uburger -p6502JDjbyqv3 pizz-emp-management > backup.sql

# Compressed data backup
tar -czf db_backup.tar.gz -C .docker/db data

# Redis backup
tar -czf redis_backup.tar.gz -C .docker/redis data

# Application files backup
tar -czf app_backup.tar.gz storage/app/public
```

#### Backup Schedule (Recommended)
```bash
# Add to crontab for daily backups
0 2 * * * cd /path/to/pizza && ./scripts/backup-database.sh

# Clean old backups (keep 7 days)
0 3 * * * find /path/to/pizza/backups -name '*.sql' -mtime +7 -delete
```

#### Restore Process
```bash
# 1. Stop services
docker compose down

# 2. Restore from backup
./scripts/restore-database.sh ./backups/database_YYYYMMDD_HHMMSS.sql

# 3. Start services
docker compose up -d

# 4. Clear caches
docker compose exec php php artisan cache:clear
```

## 📋 Notes

### Development Commands
```bash
# Docker commands
docker compose build          # Build or rebuild services
docker compose up -d          # Create and start containers
docker compose down           # Stop and remove containers
docker compose restart        # Restart service containers
docker compose exec php bash  # Access PHP container

# Laravel commands
php artisan about            # Display application information
php artisan config:clear     # Clear configuration cache
php artisan cache:clear      # Clear application cache
php artisan queue:clear      # Clear queue jobs
php artisan optimize:clear   # Clear all caches

# Code quality
vendor/bin/pint              # Format code with Laravel Pint
vendor/bin/phpstan analyse   # Static analysis
vendor/bin/rector process    # Code refactoring
```

### Service URLs (Development)
- **Application**: http://localhost
- **Admin Panel**: http://localhost/admin
- **phpMyAdmin**: http://localhost:8080
- **Mailpit**: http://localhost:8025
- **Adminer**: http://localhost:9090

### Database Credentials (Docker)
- **Server**: `db`
- **Username**: `burger`
- **Password**: `6502JDjbyqv3`
- **Database**: `pizz-emp-management`

---

**Built with ❤️ using Laravel, Filament, and modern web technologies.**