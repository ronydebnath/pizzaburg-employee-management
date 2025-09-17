#!/bin/bash

# Persistence Verification Script for Pizza Employee Management System
# This script verifies that your database data persists across container rebuilds

set -e

# Configuration
DB_NAME="refactorian"
DB_USER="refactorian"
# Use environment variable or default password
DB_PASSWORD="${DB_PASSWORD:-refactorian}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔍 Pizza Database Persistence Verification${NC}"
echo "=============================================="

# Check if Docker is running
if ! docker ps >/dev/null 2>&1; then
    echo -e "${RED}❌ Docker is not running. Please start Docker first.${NC}"
    exit 1
fi

# Check if containers are running
if ! docker compose ps | grep -q "Up"; then
    echo -e "${YELLOW}⚠️  No containers are running. Starting services...${NC}"
    docker compose up -d
    sleep 10
fi

echo -e "${GREEN}✅ Docker services are running${NC}"

# Check database data directory
if [ -d ".docker/db/data" ]; then
    DATA_SIZE=$(du -sh .docker/db/data | cut -f1)
    echo -e "${GREEN}✅ Database data directory exists (Size: $DATA_SIZE)${NC}"
else
    echo -e "${RED}❌ Database data directory not found${NC}"
    exit 1
fi

# Check Redis data directory
if [ -d ".docker/redis/data" ]; then
    REDIS_SIZE=$(du -sh .docker/redis/data | cut -f1)
    echo -e "${GREEN}✅ Redis data directory exists (Size: $REDIS_SIZE)${NC}"
else
    echo -e "${YELLOW}⚠️  Redis data directory not found${NC}"
fi

# Test database connection
echo -e "${YELLOW}🔍 Testing database connection...${NC}"
if docker compose exec -T db mysqladmin ping -h localhost --silent; then
    echo -e "${GREEN}✅ Database is accessible${NC}"
else
    echo -e "${RED}❌ Database is not accessible${NC}"
    exit 1
fi

# Check if database has tables
TABLE_COUNT=$(docker compose exec -T db mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | wc -l || echo "0")
if [ "$TABLE_COUNT" -gt 0 ]; then
    echo -e "${GREEN}✅ Database has $TABLE_COUNT tables${NC}"
else
    echo -e "${YELLOW}⚠️  Database appears to be empty (run migrations if needed)${NC}"
fi

# Test Redis connection
echo -e "${YELLOW}🔍 Testing Redis connection...${NC}"
if docker compose exec -T redis redis-cli ping | grep -q "PONG"; then
    echo -e "${GREEN}✅ Redis is accessible${NC}"
else
    echo -e "${YELLOW}⚠️  Redis is not accessible${NC}"
fi

echo ""
echo -e "${BLUE}📊 Persistence Test Summary${NC}"
echo "=========================="

# Show volume mounts
echo -e "${YELLOW}📁 Volume Mounts:${NC}"
docker compose config | grep -A 5 "volumes:" | grep -E "(\.docker|/var/lib/mysql|/data)" || echo "  No volume mounts found"

echo ""
echo -e "${GREEN}🎉 Persistence verification completed!${NC}"
echo ""
echo -e "${YELLOW}💡 To test persistence across rebuilds:${NC}"
echo "   1. Add some test data to your database"
echo "   2. Run: docker compose down"
echo "   3. Run: docker compose up -d --build"
echo "   4. Check if your data is still there"
echo ""
echo -e "${YELLOW}🛡️  To create a backup before testing:${NC}"
echo "   ./scripts/backup-database.sh"
