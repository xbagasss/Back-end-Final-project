# 📋 Ringkasan Eksekutif - SmartHealthy Architecture

## 🎯 Tentang SmartHealthy

**SmartHealthy** adalah aplikasi web tracking nutrisi yang membantu pengguna mencapai target kesehatan melalui:
- Perhitungan kalori otomatis (TDEE - Total Daily Energy Expenditure)
- Pencatatan makanan harian
- Rekomendasi meal plan cerdas
- Analitik visual dengan grafik
- Reminder otomatis via email

---

## 🏗 Arsitektur Sistem

### **Backend (src/)**

#### 1. **Config Layer** - Koneksi Database
- `Database.php` - MySQL connection wrapper dengan environment configuration

#### 2. **Model Layer** - Data Access
- `User.php` - User authentication & profile
- `Food.php` - Food database CRUD
- `NutritionLog.php` - Daily food logging
- `Notification.php` - Notification management

#### 3. **Service Layer** - Business Logic
- `AuthService.php` - Registration, login, OTP verification
- `MealRecommendationService.php` - TDEE calculation & meal planning
- `AnalyticsService.php` - Data aggregation & statistics
- `NotificationService.php` - Email delivery (PHPMailer)
- `ReminderService.php` - Daily reminder automation
- `EmailTemplateService.php` - Email template management
- `NutritionApiClient.php` - Edamam API integration
- `RecommendationService.php` - Food recommendation algorithm

### **Frontend (public/)**

#### User Pages
- `dashboard.php` - Main dashboard dengan Chart.js
- `analytics.php` - Advanced analytics & trends
- `meal_plan.php` - AI meal recommendations
- `search_nutrition.php` - Real-time food search
- `profile.php` - User settings & TDEE setup

#### Admin Panel (admin/)
- `dashboard.php` - Admin overview
- `users.php` - User management
- `foods.php` - Global food database
- `reports.php` - System reports

#### Automation (cron/)
- `run_reminders.php` - Daily email reminders
- `run_meal_reminder.php` - Time-based meal reminders

---

## 💡 Fitur Utama

### 1. **Sistem Rekomendasi Cerdas**
- **Algoritma**: Mifflin-St Jeor equation
- **Input**: Height, weight, age, gender, activity level, fitness goal
- **Output**: Daily calorie target (TDEE)
- **Adjustment**: 
  - Diet: TDEE - 500 kcal
  - Maintain: TDEE
  - Muscle: TDEE + 400 kcal

### 2. **Pencarian Nutrisi Real-time**
- Integrasi dengan **Edamam Nutrition API**
- Search makanan dengan auto-suggest
- Display kalori, protein, karbo, lemak per porsi

### 3. **Dashboard Analitik**
- **Line Chart**: Weekly calorie trend
- **Pie Chart**: Macronutrient distribution (Protein/Carbs/Fat)
- **Health Warnings**: Surplus kalori, high carbs, low protein

### 4. **Automation & Reminders**
- Daily email untuk user yang belum log makanan
- Meal-specific reminders (Breakfast 07:00, Lunch 12:00, Dinner 18:00)
- Cron job integration

---

## 🔧 Tech Stack

| Component | Technology |
|-----------|-----------|
| **Language** | PHP 8.0+ (OOP, Namespace) |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript |
| **Charts** | Chart.js 3.x |
| **Email** | PHPMailer (SMTP Gmail) |
| **API** | Edamam Nutrition API |
| **Package Manager** | Composer |
| **Server** | Apache / Nginx (XAMPP) |

---

## 📊 Database Schema

### **users** - User Management
- Profile data (height, weight, age, gender)
- Activity level & fitness goal
- Daily calorie target (auto-calculated)
- Role (user/admin)
- OTP verification

### **foods** - Food Database
- Name, description
- Macronutrients (calories, protein, carbs, fat)
- Creator (user/admin)

### **nutrition_logs** - Daily Food Tracking
- User's food entries
- Date & meal type (Breakfast/Lunch/Dinner/Snack)
- Macronutrient snapshot

### **weight_logs** - Weight Tracking
- Weight progression
- Used for diet/muscle goal tracking

### **notifications** - Notification System
- Email & in-app notifications
- Read/unread status

---

## 🔄 Key Workflows

### Registration Flow
1. User submits registration form
2. AuthService generates 6-digit OTP
3. User inserted to database (is_verified=0)
4. OTP sent via email (NotificationService)
5. User verifies OTP
6. Account activated (is_verified=1)

### Dashboard Load Flow
1. Check user session & profile completion
2. Calculate TDEE (MealRecommendationService)
3. Query today's logs & weekly data
4. Render Chart.js visualizations
5. Display health warnings if applicable

### Food Search & Add Flow
1. User types food name
2. Search via Edamam API (NutritionApiClient)
3. Display nutrition facts
4. User adds to log
5. Data saved to nutrition_logs table
6. Redirect to dashboard

### Daily Reminder Flow
1. Cron job triggers at 18:00
2. ReminderService queries users with no logs today
3. For each user: send reminder email
4. Log notification to database

---

## 🔐 Security

- ✅ **Password Hashing**: `password_hash()` dengan bcrypt
- ✅ **SQL Injection Prevention**: Prepared statements
- ✅ **XSS Protection**: `htmlspecialchars()` output escaping
- ✅ **CSRF Protection**: Token validation (optional, can be improved)
- ✅ **Session Management**: Secure session handling
- ✅ **Role-Based Access**: Admin vs User pages

---

## 📈 Performance

- **Database Indexes**: Optimized queries untuk user_id + date
- **Session Caching**: TDEE cached dalam session
- **CDN**: Chart.js loaded from CDN
- **Lazy Loading**: Charts loaded only when needed
- **Query Optimization**: Aggregate di database, bukan PHP loop

---

## 🚀 Deployment

### Requirements
- PHP >= 8.0
- MySQL / MariaDB
- Composer
- Web Server (Apache/Nginx)
- Cron job support (untuk reminders)

### Setup Steps
1. Clone repository
2. `composer install`
3. Copy `.env.example` to `.env`
4. Configure database & SMTP credentials
5. Import database schema
6. Setup cron jobs
7. Configure web server (document root = `public/`)

### Cron Jobs
```bash
# Daily reminder (18:00)
0 18 * * * php /path/to/public/cron/run_reminders.php

# Meal reminders
0 7 * * * php /path/to/public/cron/run_meal_reminder.php breakfast
0 12 * * * php /path/to/public/cron/run_meal_reminder.php lunch
0 18 * * * php /path/to/public/cron/run_meal_reminder.php dinner
```

---

## 📚 File Penting

### Dokumentasi Lengkap
- **[ARSITEKTUR_BACKEND_FRONTEND.md](file:///C:/Users/Zyrex/.gemini/antigravity/brain/b777d6e0-1bb3-40a9-9ff4-6eb91ff99de7/ARSITEKTUR_BACKEND_FRONTEND.md)** - Dokumentasi lengkap backend & frontend (900+ baris)

### Konfigurasi
- **[.env](file:///c:/xampp/htdocs/yourproject/.env)** - Environment configuration
- **[bootstrap.php](file:///c:/xampp/htdocs/yourproject/bootstrap.php)** - Application initialization

### Core Backend Files
- **[src/Config/Database.php](file:///c:/xampp/htdocs/yourproject/src/Config/Database.php)** - Database connection
- **[src/Services/AuthService.php](file:///c:/xampp/htdocs/yourproject/src/Services/AuthService.php)** - Authentication logic
- **[src/Services/MealRecommendationService.php](file:///c:/xampp/htdocs/yourproject/src/Services/MealRecommendationService.php)** - TDEE & meal planning

### Core Frontend Files
- **[public/dashboard.php](file:///c:/xampp/htdocs/yourproject/public/dashboard.php)** - Main dashboard (774 lines)
- **[public/analytics.php](file:///c:/xampp/htdocs/yourproject/public/analytics.php)** - Analytics page
- **[public/dashboard.css](file:///c:/xampp/htdocs/yourproject/public/dashboard.css)** - Main stylesheet

---

## 🎯 Kesimpulan

SmartHealthy adalah aplikasi nutrition tracking yang well-architected dengan:
- **Backend** yang modular (MVC + Service Layer)
- **Frontend** yang responsive & user-friendly
- **Business Logic** yang kompleks (TDEE calculation, meal recommendations)
- **Integration** dengan external services (Edamam API, Gmail SMTP)
- **Automation** via cron jobs untuk reminders
- **Security** best practices

Cocok untuk:
- ✅ Personal nutrition tracking
- ✅ Diet & fitness goal management
- ✅ Learning PHP OOP architecture
- ✅ Understanding full-stack web development
