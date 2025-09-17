#!/bin/bash

# Database Backup Script for Pizza Employee Management System
# This script creates backups of your persistent database data

set -e

# Configuration
BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DB_NAME="pizz-emp-management"
DB_USER="burger"
DB_PASSWORD=""  # Change this to your actual database password
CONTAINER_NAME="pizza-db-1"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🗄️  Pizza Database Backup Script${NC}"
echo "=================================="

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Check if Docker container is running
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo -e "${RED}❌ Database container is not running. Please start it first:${NC}"
    echo "   docker compose up -d db"
    exit 1
fi

echo -e "${YELLOW}📦 Creating database backup...${NC}"

# Create SQL dump backup
SQL_BACKUP_FILE="$BACKUP_DIR/database_$TIMESTAMP.sql"
docker exec "$CONTAINER_NAME" mysqldump -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$SQL_BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ SQL dump created: $SQL_BACKUP_FILE${NC}"
else
    echo -e "${RED}❌ Failed to create SQL dump${NC}"
    exit 1
fi

# Create compressed backup
COMPRESSED_BACKUP="$BACKUP_DIR/database_$TIMESTAMP.tar.gz"
tar -czf "$COMPRESSED_BACKUP" -C .docker/db data

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Compressed backup created: $COMPRESSED_BACKUP${NC}"
else
    echo -e "${RED}❌ Failed to create compressed backup${NC}"
    exit 1
fi

# Create Redis backup
REDIS_BACKUP="$BACKUP_DIR/redis_$TIMESTAMP.tar.gz"
tar -czf "$REDIS_BACKUP" -C .docker/redis data

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Redis backup created: $REDIS_BACKUP${NC}"
else
    echo -e "${YELLOW}⚠️  Redis backup failed (Redis might not be running)${NC}"
fi

# Show backup sizes
echo ""
echo -e "${GREEN}📊 Backup Summary:${NC}"
echo "=================="
ls -lh "$BACKUP_DIR"/*_$TIMESTAMP.* 2>/dev/null | awk '{print "  " $9 " (" $5 ")"}'

echo ""
echo -e "${GREEN}🎉 Backup completed successfully!${NC}"
echo ""
echo -e "${YELLOW}💡 To restore from backup, use:${NC}"
echo "   ./scripts/restore-database.sh $SQL_BACKUP_FILE"
echo ""
echo -e "${YELLOW}🗑️  To clean old backups (keep last 7 days):${NC}"
echo "   find $BACKUP_DIR -name '*.sql' -mtime +7 -delete"
echo "   find $BACKUP_DIR -name '*.tar.gz' -mtime +7 -delete"
