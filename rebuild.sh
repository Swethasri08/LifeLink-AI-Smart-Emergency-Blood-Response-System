#!/bin/bash

echo "🚀 Rebuilding Blood Donation Management System with Database..."

# Stop existing containers
echo "🛑 Stopping existing containers..."
docker-compose down

# Remove old volumes to start fresh
echo "🗑️  Cleaning up old data..."
docker volume rm bdms_db_data 2>/dev/null || true

# Build and start with database
echo "🏗️  Building and starting with database..."
docker-compose up --build -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Check if database is running
echo "🔍 Checking database status..."
docker-compose exec db mysql -uroot -prootpassword -e "SHOW DATABASES;"

# Check if tables exist
echo "📋 Checking tables..."
docker-compose exec db mysql -uroot -prootpassword bdms -e "SHOW TABLES;"

echo "✅ Deployment complete!"
echo "🌐 Application should be available at: http://localhost:8080"
echo "🗄️  Database is running on port 3306"
echo ""
echo "📝 To check logs: docker-compose logs -f"
echo "🛑 To stop: docker-compose down"
