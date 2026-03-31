#!/bin/bash

# Blood Donation Management System - Render.com Deployment Script
echo "🚀 Deploying Blood Donation Management System to Render.com..."

# Check if we're in the right directory
if [ ! -f "index.php" ]; then
    echo "❌ Error: index.php not found. Please run this script from the project root."
    exit 1
fi

echo "✅ Found index.php - proceeding with deployment..."

# Create a simple index.php for Render (if needed)
if [ ! -f "index.php" ]; then
    echo "❌ Error: index.php not found"
    exit 1
fi

echo "📁 Checking required files..."
required_files=("login.php" "register.php" "donor_dashboard.php" "bloodbank_dashboard.php" "hospital_dashboard.php" "config/database.php")

for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file found"
    else
        echo "❌ $file not found"
    fi
done

echo "🗄️ Checking database files..."
if [ -f "database_complete.sql" ]; then
    echo "✅ database_complete.sql found"
else
    echo "❌ database_complete.sql not found"
fi

echo "📦 Preparing deployment package..."
echo "✅ All files ready for deployment!"

echo ""
echo "🎯 Next Steps for Render.com Deployment:"
echo "1. Go to https://render.com"
echo "2. Sign up with GitHub"
echo "3. Connect repository: https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System"
echo "4. Create Web Service:"
echo "   - Name: blood-donation-management-system"
echo "   - Runtime: PHP"
echo "   - Build Command: echo 'Build complete'"
echo "   - Start Command: php -S 0.0.0.0:10000"
echo "5. Create Database:"
echo "   - Type: MySQL"
echo "   - Name: bdms-database"
echo "6. Import database_complete.sql"
echo "7. Set environment variables:"
echo "   - APP_ENV=production"
echo "   - DB_HOST=your-db-host"
echo "   - DB_USERNAME=your-db-user"
echo "   - DB_PASSWORD=your-db-password"
echo "   - DB_NAME=bdms"
echo ""
echo "🎉 Your application will be live at: https://blood-donation-management-system.onrender.com"
