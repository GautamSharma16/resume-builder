# Resume Builder - Role-Based Setup Guide

## ✅ Completed Setup

The following has been implemented:

### 1. **Database Migration**
- Added `role` field to users table with enum values: `user`, `admin`
- Migration file: `database/migrations/2026_04_14_000000_add_role_to_users_table.php`

### 2. **User Model Updates**
- Added `role` to fillable attributes
- Added helper methods: `isAdmin()` and `isUser()`

### 3. **Middleware for Role-Based Access**
- Created `EnsureAdminRole` middleware → restricts access to admin pages
- Created `EnsureUserRole` middleware → restricts access to user pages
- Registered middleware aliases in `bootstrap/app.php`

### 4. **Route Structure**
```
PUBLIC ROUTES (No Auth Required)
├── / (Home)
├── /improve-cv
├── /cover-letter
├── /templates
├── /interview
├── /contact

USER ROUTES (Auth + User Role)
├── /dashboard
├── /resume (all resume operations)
├── /profile

ADMIN ROUTES (Auth + Admin Role)
├── /admin/dashboard
├── /admin/analytics
├── /admin/visits
├── /admin/purchases
├── /admin/templates (CRUD)
├── /admin/articles (CRUD)
├── /admin/payments
```

### 5. **Admin Dashboard Views**
- Admin layout with sidebar navigation
- Dashboard with stats
- Templates management (index, create, edit)
- Articles management (index, create, edit)
- Analytics, visits, purchases, payments views

### 6. **Auto-Redirect After Login**
- Admins → `/admin/dashboard`
- Users → `/dashboard`
- New registrations default to `user` role

---

## 🚀 Next Steps You Need to Do

### 1. **Run Migrations**
```bash
php artisan migrate
```

### 2. **Create Test Admin User** (Optional)
Run the following command or create via database:
```bash
php artisan tinker
User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin'])
```

### 3. **Create Controllers for Each Feature** (Important!)

You need to create controllers for:

```php
// app/Http/Controllers/ResumeController.php
- index() → /resume
- create() → /resume/create
- store() → save resume
- edit() → /resume/edit/{id}
- update() → update resume
- show() → /resume/{id}/preview
- download() → /resume/{id}/download

// app/Http/Controllers/Admin/TemplateController.php
- index() → /admin/templates
- create() → /admin/templates/create
- store() → save template
- edit() → /admin/templates/{id}/edit
- update() → update template
- delete() → delete template

// app/Http/Controllers/Admin/ArticleController.php
- index() → /admin/articles
- create() → /admin/articles/create
- store() → save article
- edit() → /admin/articles/{id}/edit
- update() → update article
- delete() → delete article

// Similar for payments, analytics, etc.
```

### 4. **Create Models**
You'll need models like:
```php
- Resume
- Template
- Article
- Payment
- Visit (for analytics)
```

### 5. **Create Database Tables** (migrations)
```php
- resumes
- templates
- articles
- payments
- visits
```

### 6. **Update Routes** (optional - for cleaner code)
Consider using resource routes:
```php
Route::middleware(['auth', 'user'])->group(function () {
    Route::resource('resume', ResumeController::class);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('templates', Admin\TemplateController::class);
        Route::resource('articles', Admin\ArticleController::class);
    });
});
```

### 7. **Test the Flow**
1. Register as a new user (will b.0e assigned `user` role)
2. You should be redirected to `/dashboard`
3. Create an admin user in database with `role = 'admin'`
4. Login as admin → should see `/admin/dashboard`

---

## 📁 File Structure Created

```
resources/views/
├── admin/
│   ├── dashboard.blade.php
│   ├── analytics.blade.php
│   ├── visits.blade.php
│   ├── purchases.blade.php
│   ├── payments.blade.php
│   ├── templates/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── edit.blade.php
│   └── articles/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
└── layouts/
    └── admin.blade.php

app/Http/
├── Middleware/
│   ├── EnsureAdminRole.php
│   └── EnsureUserRole.php
└── Controllers/Auth/
    ├── AuthenticatedSessionController.php (UPDATED)
    └── RegisteredUserController.php (UPDATED)

database/migrations/
└── 2026_04_14_000000_add_role_to_users_table.php
```

---

## 🔒 Security Notes

✅ Protected routes require authentication  
✅ Protected routes check for user role  
✅ Unauthorized users receive 403 error  
✅ Middleware prevents direct URL access  

---

## 💡 Quick Commands Reference

```bash
# Run migrations
php artisan migrate

# Create controller
php artisan make:controller ResumeController --resource

# Create model with migration
php artisan make:model Resume -m

# Clear cache
php artisan cache:clear

# Create admin user via tinker
php artisan tinker
```

---

Let me know if you need help implementing any of the controllers or models!
