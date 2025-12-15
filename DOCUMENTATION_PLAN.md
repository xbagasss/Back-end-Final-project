# Project Documentation Plan

## Files Already Documented
- ✅ `public/dashboard.php` - Main dashboard with TDEE calculation and health warnings

## High Priority Files (Core Functionality)

### Configuration & Bootstrap
1. `bootstrap.php` - Application initialization
2. `src/Config/Database.php` - Database connection
3. `.env` - Environment variables

### Authentication & User Management
4. `public/login.php` - User login
5. `public/register.php` - User registration
6. `public/logout.php` - Session termination
7. `src/Services/AuthService.php` - Authentication logic

### Main Features
8. `public/search_nutrition.php` - Edamam API integration
9. `public/analytics.php` - Data visualization
10. `public/profile.php` - User profile management
11. `public/meal_plan.php` - Meal planning
12. `public/weight_tracker.php` - Weight tracking

### Admin Panel
13. `public/admin/dashboard.php` - Admin overview
14. `public/admin/foods.php` - Food management
15. `public/admin/food_form.php` - Add/edit food
16. `public/admin/users.php` - User management
17. `public/admin/api_logs.php` - API logging
18. `public/admin/reports.php` - Analytics reports

### Services
19. `src/Services/NutritionApiClient.php` - API client
20. `src/Services/MealRecommendationService.php` - Meal recommendations

## Medium Priority (Supporting Files)
- Food CRUD operations (`public/foods/*.php`)
- Calendar features (`public/calendar*.php`)
- Utility scripts (`setup_database.php`, `create_admin.php`)

## Low Priority (Debug/Test Files)
- `check_*.php` - Various debug scripts
- `debug_*.php` - Debugging utilities
