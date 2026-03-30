# 🩸 Blood Donation Management System (BDMS)

A comprehensive, web-based Blood Donation Management System designed to streamline blood donation processes, manage donor information, track blood inventory, and facilitate communication between donors, blood banks, and hospitals.

## 🌟 Features

### 👥 Multi-Role System
- **Donors**: Register, schedule appointments, track donation history
- **Blood Banks**: Manage inventory, process donations, handle requests
- **Hospitals**: Request blood, track delivery status
- **Admins**: Oversee system operations and manage users

### 🏥 Core Functionality
- ✅ **Donor Registration & Management**
- ✅ **Blood Inventory Tracking** with expiry dates
- ✅ **Appointment Scheduling System**
- ✅ **Blood Request Processing**
- ✅ **Real-time Status Updates**
- ✅ **Health Condition Screening**
- ✅ **Eligibility Checking**
- ✅ **Report Generation** (PDF exports)

### 📊 Dashboard Features
- 📈 **Analytics & Statistics**
- 🩸 **Blood Stock Levels**
- 📅 **Appointment Calendars**
- 🚨 **Emergency Requests**
- 📋 **Donor Leaderboards**

## 🚀 Live Demo

Currently running locally at: `http://localhost:8000`

## 📸 Screenshots

### Login Page
![Login Page](screenshots/login.html)

### Donor Dashboard
![Donor Dashboard](screenshots/donor_dashboard.html)

### Blood Bank Management
![Blood Bank Dashboard](screenshots/bloodbank_dashboard.html)

### Hospital Requests
![Hospital Dashboard](screenshots/hospital_dashboard.html)

### Blood Inventory
![Blood Inventory](screenshots/inventory.html)

## 🛠️ Technology Stack

### Backend
- **PHP 8.1+** - Core application logic
- **MySQL/MariaDB** - Database management
- **Composer** - Dependency management

### Frontend
- **Bootstrap 5.1.3** - UI Framework
- **Chart.js** - Data visualization
- **TCPDF** - PDF generation
- **Custom CSS** - Responsive design

### Database
- **MySQL/MariaDB** with optimized schema
- **Foreign Key Constraints** for data integrity
- **Stored Procedures** for complex operations

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.1+** with extensions:
  - `mysqli`
  - `pdo_mysql`
  - `mbstring`
  - `curl`
  - `gd`
  - `openssl`
- **MySQL/MariaDB 10.4+**
- **Web Server** (Apache/Nginx or PHP built-in server)
- **Composer** (for dependency management)

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System.git
cd LifeLink-AI-Smart-Emergency-Blood-Response-System
```

### 2. Database Setup

#### Option A: Using XAMPP/WAMP/MAMP
1. Start your MySQL/MariaDB service
2. Import the database:
```bash
mysql -u root -p < database_complete.sql
```

#### Option B: Manual Setup
```sql
-- Create database
CREATE DATABASE bdms;
USE bdms;

-- Import the schema from database_complete.sql
```

### 3. Configuration

#### Database Configuration
Edit `config/database.php`:
```php
<?php
$host = "localhost";
$username = "root";
$password = ""; // Your MySQL password
$database = "bdms";

$conn = mysqli_connect($host, $username, $password, $database);
?>
```

### 4. Install Dependencies
```bash
composer install
```

### 5. Start the Application

#### Option A: Using PHP Built-in Server
```bash
php -S localhost:8000
```

#### Option B: Using Apache/Nginx
- Move the project to your web server's document root
- Configure virtual host if needed
- Access via `http://yourdomain.com`

## 🔧 Configuration

### Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@bdms.com | admin123 |
| Donor | john@example.com | donor123 |
| Blood Bank | cityblood@bdms.com | blood123 |
| Hospital | cityhospital@bdms.com | hospital123 |

### Database Schema

The system uses the following main tables:
- **donors** - Donor information and health data
- **blood_banks** - Blood bank details
- **hospitals** - Hospital information
- **blood_inventory** - Stock levels with expiry tracking
- **blood_requests** - Request management
- **appointments** - Donation scheduling
- **admins** - System administrators

## 📱 Usage Guide

### For Donors
1. **Register** with personal and medical information
2. **Check eligibility** using the health screening tool
3. **Schedule appointments** at nearby blood banks
4. **Track donation history** and upcoming appointments

### For Blood Banks
1. **Manage inventory** - Add/update blood units
2. **Process appointments** - Approve/reject donation requests
3. **Handle blood requests** from hospitals
4. **Generate reports** for management

### For Hospitals
1. **Request blood** for patients
2. **Track request status** in real-time
3. **Manage delivery logistics**
4. **View blood bank availability**

## 🔒 Security Features

- **Password Hashing** using secure algorithms
- **Session Management** with timeout protection
- **SQL Injection Prevention** with prepared statements
- **Input Validation** and sanitization
- **Role-Based Access Control**

## 📊 Reports & Analytics

Generate comprehensive reports including:
- 📈 **Donor Statistics**
- 🩸 **Blood Stock Reports**
- 📅 **Appointment Analytics**
- 🏥 **Hospital Request Trends**
- 📋 **Monthly Summaries**

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Setup
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Bootstrap** for the responsive UI framework
- **Chart.js** for data visualization
- **TCPDF** for PDF generation capabilities
- **PHP Community** for excellent documentation and support

## 📞 Support

For support and inquiries:
- 📧 Email: support@bdms.com
- 🐛 Issues: [GitHub Issues](https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System/issues)
- 📖 Documentation: [Wiki](https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System/wiki)

---

<div align="center">
  <p>Made with ❤️ for saving lives through blood donation</p>
  <p>© 2024 Blood Donation Management System. All rights reserved.</p>
</div>
