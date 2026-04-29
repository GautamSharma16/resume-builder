# 🔑 Admin & User Credentials

## ✅ Test Accounts (Successfully Seeded)

All test accounts are ready to use for testing the role-based system.

---

## **👨‍💼 ADMIN ACCOUNT**

**Email:** `admin@resumebuilder.com`  
**Password:** `admin@123`  
**Role:** Administrator  
**Access:** Full access to admin panel at `/admin/dashboard`

**What Admin Can Do:**
- ✅ View analytics and statistics
- ✅ See website visits and purchases
- ✅ Upload and manage resume templates
- ✅ Create and publish articles/guides
- ✅ View payment history
- ✅ Manage system content

**Admin Dashboard:** `http://127.0.0.1:8000/admin/dashboard`

---

## **👤 REGULAR USER ACCOUNTS**

### User 1:
**Email:** `user@resumebuilder.com`  
**Password:** `user@123`  
**Role:** User  
**Access:** User dashboard at `/dashboard`

### User 2 (Demo):
**Email:** `demo@resumebuilder.com`  
**Password:** `demo@123`  
**Role:** User  
**Access:** User dashboard at `/dashboard`

**What Users Can Do:**
- ✅ Create and edit resumes
- ✅ Browse and select templates
- ✅ Download completed resumes
- ✅ Manage their profile
- ✅ View their resume library

**User Dashboard:** `http://127.0.0.1:8000/dashboard`

---

## **🔄 Testing Flow**

### Test 1: Register as New User
```
1. Go to http://127.0.0.1:8000/register
2. Create account with any details
3. Password will be automatically encrypted
4. New role will be set to 'user'
5. After login → redirected to /dashboard
```

### Test 2: Login as Admin
```
1. Go to http://127.0.0.1:8000/login
2. Email: admin@resumebuilder.com
3. Password: admin@123
4. After login → redirected to /admin/dashboard ✓
```

### Test 3: Login as Regular User
```
1. Go to http://127.0.0.1:8000/login
2. Email: user@resumebuilder.com
3. Password: user@123
4. After login → redirected to /dashboard ✓
```

### Test 4: Navbar Changes
```
Public Pages (No Login):
├── Shows: Home, Templates, Improve CV, Interview, Contact
├── Shows: Login & Register buttons
└── URL: http://127.0.0.1:8000/

User Pages (After Login):
├── Shows: My Dashboard, My Resumes
├── Shows: User dropdown with Profile, Settings, Logout
└── URL: http://127.0.0.1:8000/dashboard

Admin Pages (Admin Login):
├── Shows: Sidebar with all admin menu items
├── Shows: Dashboard, Analytics, Visits, Purchases, Templates, Articles, Payments
├── Shows: Admin dropdown with Logout
└── URL: http://127.0.0.1:8000/admin/dashboard
```

### Test 5: Route Protection
```
Try accessing without login or wrong role:
├── /dashboard (user route) → redirected to login page
├── /admin/dashboard (admin route) → 403 Unauthorized if not admin
├── / (home) → works for everyone ✓
```

---

## **📁 Files Created/Updated**

### Database & Auth
- ✅ Migration: `add_role_to_users_table.php`
- ✅ Seeder: `UserSeeder.php`
- ✅ Model: `User.php` (added role methods)

### Middleware
- ✅ `EnsureAdminRole.php` (for admin pages)
- ✅ `EnsureUserRole.php` (for user pages)

### Views
- ✅ `layouts/app.blade.php` (user layout)
- ✅ `layouts/guest.blade.php` (login/register layout)
- ✅ `layouts/admin.blade.php` (admin dashboard layout)
- ✅ `components/navbar-public.blade.php` (public navbar)
- ✅ `components/navbar-user.blade.php` (user navbar)
- ✅ `components/navbar-admin.blade.php` (admin navbar)

### Admin Pages
- ✅ `admin/dashboard.blade.php`
- ✅ `admin/analytics.blade.php`
- ✅ `admin/visits.blade.php`
- ✅ `admin/purchases.blade.php`
- ✅ `admin/payments.blade.php`
- ✅ `admin/templates/index.blade.php`
- ✅ `admin/templates/create.blade.php`
- ✅ `admin/templates/edit.blade.php`
- ✅ `admin/articles/index.blade.php`
- ✅ `admin/articles/create.blade.php`
- ✅ `admin/articles/edit.blade.php`

### Controllers
- ✅ Updated: `AuthenticatedSessionController.php` (role-based redirect)
- ✅ Updated: `RegisteredUserController.php` (default role assignment)

---

## **🚀 How to Test Everything**

### Step 1: Verify the application is running
```bash
php artisan serve
# Should show: http://127.0.0.1:8000
```

### Step 2: Go to login and test credentials
```
URL: http://127.0.0.1:8000/login
```

### Step 3: Test Each Account
- **Admin Test:** Login with `admin@resumebuilder.com` / `admin@123` → /admin/dashboard
- **User Test:** Login with `user@resumebuilder.com` / `user@123` → /dashboard
- **Register Test:** Create new account → automatically gets user role

### Step 4: Check Navbars
- Public pages show public navbar (Home, Templates, etc.)
- User dashboard shows user navbar (My Dashboard, My Resumes)
- Admin pages show admin sidebar with all menu items

---

## **💡 Key Features Implemented**

✅ **Role-Based Access Control (RBAC)**
- Two roles: Admin & User
- Each role has specific routes and permissions

✅ **Role-Based Navigation**
- Public navbar for visitors
- User navbar after login
- Admin sidebar for admin dashboard

✅ **Automatic Redirect**
- Admin → `/admin/dashboard`
- User → `/dashboard`
- Guest → `/login`

✅ **Protected Routes**
- Admin routes require admin role
- User routes require user role
- Public routes accessible to all

✅ **Auto-Role Assignment**
- New registrations automatically get 'user' role
- Only database seeders/commands set admin role

---

## **⚠️ Important Notes**

1. **Database Updated:** The `role` column has been added to the users table
2. **Middleware Active:** Both role middlewares are registered and active
3. **Routes Protected:** Admin and user routes are protected with middleware
4. **Test Data Ready:** Admin and 2 test users are seeded in database

---

## **🔧 Database Query to Check Users**

If you want to verify users in database:

```sql
SELECT id, name, email, role, created_at FROM users;
```

Expected output:
```
id | name       | email                      | role  | created_at
1  | Admin User | admin@resumebuilder.com    | admin | 2026-04-14...
2  | Test User  | user@resumebuilder.com     | user  | 2026-04-14...
3  | Demo User  | demo@resumebuilder.com     | user  | 2026-04-14...
```

---

## **❓ Troubleshooting**

### Issue: "Table users has no column named role"
**Solution:** Run `php artisan migrate` again

### Issue: Users not seeded
**Solution:** Run `php artisan db:seed --class=UserSeeder`

### Issue: Can't login with test credentials
**Solution:** Verify users exist in database: `SELECT * FROM users;`

### Issue: Navbar not changing
**Solution:** Clear cache: `php artisan cache:clear`

---

Happy Testing! 🎉
