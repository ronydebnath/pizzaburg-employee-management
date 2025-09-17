#!/bin/bash

# Database Restore Script for Pizza Employee Management System
# This script restores your database from a backup

set -e

# Configuration
DB_NAME="pizz-emp-management"
DB_USER="burger"
DB_PASSWORD=""  # Change this to your actual database password
CONTAINER_NAME="pizza-db-1"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🔄 Pizza Database Restore Script${NC}"
echo "=================================="

# Check if backup file is provided
if [ $# -eq 0 ]; then
    echo -e "${RED}❌ Please provide a backup file path${NC}"
    echo ""
    echo "Usage: $0 <backup_file.sql>"
    echo ""
    echo "Available backups:"
    ls -la ./backups/*.sql 2>/dev/null || echo "  No backups found in ./backups/"
    exit 1
fi

BACKUP_FILE="$1"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}❌ Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 Restoring from: $BACKUP_FILE${NC}"

# Check if Docker container is running
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo -e "${YELLOW}⚠️  Database container is not running. Starting it...${NC}"
    docker compose up -d db
    
    # Wait for database to be ready
    echo -e "${YELLOW}⏳ Waiting for database to be ready...${NC}"
    sleep 10
fi

# Wait for database to be ready
echo -e "${YELLOW}⏳ Waiting for database to be ready...${NC}"
until docker exec "$CONTAINER_NAME" mysqladmin ping -h localhost --silent; do
    echo "   Waiting for database..."
    sleep 2
done

echo -e "${GREEN}✅ Database is ready${NC}"

# Create a backup before restoring (safety measure)
SAFETY_BACKUP="./backups/safety_backup_$(date +"%Y%m%d_%H%M%S").sql"
echo -e "${YELLOW}🛡️  Creating safety backup before restore...${NC}"
docker exec "$CONTAINER_NAME" mysqldump -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$SAFETY_BACKUP"
echo -e "${GREEN}✅ Safety backup created: $SAFETY_BACKUP${NC}"

# Restore the database
echo -e "${YELLOW}🔄 Restoring database...${NC}"
docker exec -i "$CONTAINER_NAME" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database restored successfully!${NC}"
else
    echo -e "${RED}❌ Database restore failed${NC}"
    echo -e "${YELLOW}💡 You can restore from the safety backup:${NC}"
    echo "   $0 $SAFETY_BACKUP"
    exit 1
fi

# Verify the restore
echo -e "${YELLOW}🔍 Verifying restore...${NC}"
TABLE_COUNT=$(docker exec "$CONTAINER_NAME" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SHOW TABLES;" | wc -l)
echo -e "${GREEN}✅ Restore verified: $TABLE_COUNT tables found${NC}"

echo ""
echo -e "${GREEN}🎉 Database restore completed successfully!${NC}"
echo ""
echo -e "${YELLOW}💡 Next steps:${NC}"
echo "   1. Restart your application: docker compose restart php nginx"
echo "   2. Clear Laravel caches: docker compose exec php php artisan cache:clear"
echo "   3. Run migrations if needed: docker compose exec php php artisan migrate"
