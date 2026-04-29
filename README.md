# Resume Builder

A Laravel-based web application to create professional resumes.

## Features
- Resume Maker
- Templates
- Cover Letter
- Interview Tips

## Tech Stack
- Laravel
- PHP
- MySQL
- Tailwind CSS

## Setup

```bash
git clone https://github.com/gauravfrontendproject/Resume_Builder.git
cd Resume_Builder

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan serve