# Cvbliss Resume & Cover Letter Builder

A premium, modern web application for building high-fidelity resumes and cover letters. Built with a robust backend using Laravel 12 and a highly responsive, glass-morphic frontend.

## 🚀 Tech Stack

1. Backend Framework: Laravel 12.x
2. Backend Language: PHP 8.2+
3. Database: MySQL / PostgreSQL
4. ORM & Migrations: Laravel Eloquent
5. Authentication: Laravel Auth, Laravel Socialite, Google OAuth
6. Frontend Templating: Blade Templates
7. Styling: Tailwind CSS, Tailwind Forms
8. Frontend JavaScript: Alpine.js, Vanilla JavaScript
9. HTTP Client: Axios
10. Build Tool: Vite 7, Laravel Vite Plugin
11. Rich Text Editor: TinyMCE
12. Payment Gateway: Razorpay
13. PDF Generation: DomPDF, barryvdh/laravel-dompdf, Puppeteer
14. Document Parsing: Smalot PDFParser, PHPWord
15. AI Integration: Google Gemini API

---

## 🛠 Deployment Requirements

To successfully deploy and run this application in a production environment, ensure your server meets the following requirements:

### **1. System Requirements**
- **OS:** Linux (Ubuntu 20.04+ recommended)
- **Web Server:** Nginx or Apache
- **PHP:** Version >= 8.2
  - *Extensions:* OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, GD/Imagick
- **Node.js:** Version >= 18.x (Required for Vite build and Puppeteer)
- **Composer:** Latest v2.x

### **2. Puppeteer Dependencies (Linux)**
Since the app relies on Puppeteer (headless Chrome) for high-fidelity PDF rendering, the server must have required Linux GUI libraries installed. For Ubuntu/Debian:
```bash
sudo apt-get install -y libnss3 libxss1 libasound2 libatk-bridge2.0-0 libgtk-3-0 libgbm-dev
```

### **3. Required Environment Variables (`.env`)**
Your `.env` file must be properly configured with the following key services:
- **Database Credentials** (`DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
- **App URL:** Set `APP_URL` to your production domain (crucial for PDF CSS/Image absolute paths).
- **Razorpay Keys:** (`RAZORPAY_KEY`, `RAZORPAY_SECRET`, `RAZORPAY_WEBHOOK_SECRET`)
- **OAuth Keys:** Google Client ID & Secret
- **TinyMCE Key:** `TINYMCE_API_KEY`
- **AI Provider Key:** OpenAI or Gemini API Keys for resume generation.

---

## 💻 Installation & Setup

1. **Clone the repository & install PHP dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. **Install Node.js dependencies & build assets:**
   ```bash
   npm install
   npm run build
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit `.env` and fill in your database and API credentials.*

4. **Run Migrations & Storage Link:**
   ```bash
   php artisan migrate --force
   php artisan storage:link
   ```

5. **Set Directory Permissions:**
   Ensure your web server has write permissions to `storage` and `bootstrap/cache`:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. **Start Horizon / Queue Worker:**
   If using queues for emails or heavy PDF jobs:
   ```bash
   php artisan queue:work --timeout=0
   ```

## 🏗 Directory Structure
- `app/` - Core PHP/Laravel logic (Controllers, Models, Services)
- `resources/views/` - Blade templates (Dashboard, Resume Builder, Cover Letter Builder)
- `resources/views/templates/` - HTML blueprints for the resume/cover letter designs
- `routes/web.php` - Application routing
- `public/` - Compiled CSS/JS assets and user uploads
