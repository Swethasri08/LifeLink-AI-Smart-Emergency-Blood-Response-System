# 🚀 Deploy Your Actual PHP Blood Donation Management System

This guide will help you deploy your **exact PHP application** (the same as localhost:8000) to a live server.

## 🎯 **Goal: Deploy Your Localhost Application**

Your current application runs at: `http://localhost:8000`
We want to deploy the **exact same application** to a live URL.

## 🌐 **Deployment Options**

### **Option 1: Render.com (Recommended - Free PHP Hosting)**
✅ **Native PHP Support**  
✅ **Free MySQL Database**  
✅ **Easy Setup**  
✅ **Custom Domain**  

### **Option 2: 000webhost.com (Free PHP Hosting)**
✅ **Free PHP Hosting**  
✅ **MySQL Database**  
✅ **File Manager**  
✅ **No Credit Card Required**

### **Option 3: Heroku (Paid PHP Support)**
✅ **Reliable Hosting**  
✅ **Add-on MySQL**  
✅ **Custom Domain**  
❌ **Paid Database Required**

---

## 🚀 **Option 1: Render.com Deployment (Best Choice)**

### **Step 1: Create Render Account**
1. Go to [render.com](https://render.com)
2. Sign up with GitHub
3. Connect your repository: `https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System`

### **Step 2: Create Web Service**
1. Click "New" → "Web Service"
2. Select your repository
3. Configure:
   - **Name**: `blood-donation-management-system`
   - **Runtime**: `PHP`
   - **Build Command**: `echo "Build complete"`
   - **Start Command**: `php -S 0.0.0.0:10000`
   - **Plan**: Free

### **Step 3: Create Database**
1. Click "New" → "PostgreSQL" (or MySQL if available)
2. **Name**: `bdms-database`
3. **Database Name**: `bdms`
4. **User**: `bdms_user`
5. **Plan**: Free

### **Step 4: Environment Variables**
Add these environment variables:
```
APP_ENV=production
DB_HOST=your-database-host
DB_USERNAME=bdms_user
DB_PASSWORD=your-database-password
DB_NAME=bdms
```

### **Step 5: Import Database**
1. Connect to your database using DBeaver or similar
2. Import your `database_complete.sql` file
3. Verify all tables are created

### **Step 6: Deploy**
1. Push your changes to GitHub
2. Render will auto-deploy
3. Your app will be live at: `https://blood-donation-management-system.onrender.com`

---

## 🚀 **Option 2: 000webhost.com Deployment (Free Alternative)**

### **Step 1: Create Account**
1. Go to [000webhost.com](https://www.000webhost.com)
2. Sign up for free account
3. Verify email

### **Step 2: Create Website**
1. Click "Create Website"
2. Choose "Upload Your Own Website"
3. Select free subdomain

### **Step 3: Upload Files**
1. Go to File Manager
2. Upload all your PHP files to `public_html`
3. Upload your `images` and `screenshots` folders

### **Step 4: Create Database**
1. Go to Database Manager
2. Create new MySQL database
3. Note database credentials

### **Step 5: Import Database**
1. Go to phpMyAdmin
2. Import your `database_complete.sql` file
3. Verify tables are created

### **Step 6: Configure Database**
Update `config/database.php` with your hosting database credentials:
```php
$host = "localhost"; // Your hosting database host
$username = "your_username";
$password = "your_password";
$database = "your_database_name";
```

---

## 🚀 **Option 3: Heroku Deployment (Professional)**

### **Step 1: Install Heroku CLI**
```bash
# Windows
npm install -g heroku

# Login
heroku login
```

### **Step 2: Create App**
```bash
heroku create your-app-name
```

### **Step 3: Add MySQL Add-on**
```bash
heroku addons:create jawsdb:kitefin
```

### **Step 4: Environment Variables**
```bash
heroku config:set APP_ENV=production
heroku config:set DB_HOST=$(heroku config:get JAWSDB_URL | cut -d '@' -f 2 | cut -d '/' -f 1)
heroku config:set DB_USERNAME=$(heroku config:get JAWSDB_URL | cut -d ':' -f 2 | cut -d '@' -f 1)
heroku config:set DB_PASSWORD=$(heroku config:get JAWSDB_URL | cut -d ':' -f 3 | cut -d '@' -f 1)
heroku config:set DB_NAME=$(heroku config:get JAWSDB_URL | cut -d '/' -f 4)
```

### **Step 5: Deploy**
```bash
git add .
git commit -m "Deploy to Heroku"
git push heroku master
```

---

## 📁 **Files to Deploy**

### **Required PHP Files:**
- ✅ `index.php`
- ✅ `login.php`
- ✅ `register.php`
- ✅ `donor_dashboard.php`
- ✅ `bloodbank_dashboard.php`
- ✅ `hospital_dashboard.php`
- ✅ All other `.php` files

### **Required Folders:**
- ✅ `config/` (database configuration)
- ✅ `images/` (application images)
- ✅ `screenshots/` (application screenshots)
- ✅ Any CSS/JS folders

### **Database Files:**
- ✅ `database_complete.sql` (full schema and data)
- ✅ `database.sql` (initial schema)

---

## 🔧 **Database Setup**

### **Import Your Database:**
1. Get your hosting database credentials
2. Connect using phpMyAdmin or similar tool
3. Import `database_complete.sql`
4. Verify all tables exist:
   - `donors`
   - `blood_banks`
   - `hospitals`
   - `blood_inventory`
   - `blood_requests`
   - `appointments`
   - `admins`

### **Test Database Connection:**
Update `config/database.php`:
```php
<?php
$host = "your_database_host";
$username = "your_username";
$password = "your_password";
$database = "bdms";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

---

## 🎯 **Verification Checklist**

### **After Deployment, Test:**
- [ ] Login page loads correctly
- [ ] Registration form works
- [ ] Dashboard pages load
- [ ] Database connection works
- [ ] Images display properly
- [ ] Mobile responsive design
- [ ] All PHP forms submit correctly

### **Default Login Credentials:**
- **Donor**: `john@example.com` / `donor123`
- **Blood Bank**: `cityblood@bdms.com` / `blood123`
- **Hospital**: `cityhospital@bdms.com` / `hospital123`
- **Admin**: `admin@bdms.com` / `admin123`

---

## 🚨 **Troubleshooting**

### **Common Issues:**

#### **1. Database Connection Failed**
- Check database credentials
- Verify database host
- Ensure database exists
- Check user permissions

#### **2. 404 Errors**
- Check file permissions
- Verify .htaccess rules
- Check file paths
- Ensure index.php exists

#### **3. Images Not Loading**
- Check image paths
- Verify folder permissions
- Check image file names
- Ensure images uploaded

#### **4. PHP Errors**
- Check PHP version compatibility
- Verify syntax errors
- Check error logs
- Ensure all dependencies exist

---

## 🎉 **Success Indicators**

### **Your Application is Live When:**
- ✅ Login page loads at your URL
- ✅ All forms submit correctly
- ✅ Database operations work
- ✅ Images and CSS load properly
- ✅ Mobile responsive design works
- ✅ All dashboard pages accessible

### **Share Your Live URL:**
Once deployed, you'll have:
- **Primary URL**: `https://your-app-name.platform.com`
- **Custom Domain**: Optional setup
- **GitHub Integration**: Auto-deploys on push

---

## 🚀 **Quick Start - Recommended Path**

### **Fastest Deployment (5 minutes):**
1. **Sign up for Render.com**
2. **Connect your GitHub repository**
3. **Create PHP Web Service**
4. **Create MySQL Database**
5. **Import database_complete.sql**
6. **Set environment variables**
7. **Deploy!**

### **Result:**
Your **exact localhost application** will be live at a professional URL with full functionality!

---

**🎯 Your Blood Donation Management System will be exactly the same as localhost:8000 but accessible to the world!**
