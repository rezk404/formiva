# FORMIVA — Frontend Experience

A Laravel 12 frontend-first creative technology studio site. The current phase intentionally contains **no database, authentication, CMS, or admin dashboard**. It is designed so those pieces can be added later without rebuilding the visual layer.

## Stack
- Laravel 12 / Blade
- Vite
- Tailwind CSS 4
- Three.js
- GSAP / ScrollTrigger
- Lenis (desktop smooth scroll)

## Run locally

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
composer run dev
```

Open `http://127.0.0.1:8000`.

## Current scope

The frontend contains:
- responsive public website
- FORMIVA visual identity and mark
- clear service, work, case-study and process sections
- cinematic hero composition
- 3D world enhancement via Three.js
- GSAP scroll/reveal interactions
- reduced-motion and mobile fallbacks
- content kept in `resources/content` so it can later be replaced by Laravel CMS data

## Next phase

The same Laravel application can later receive:
- MySQL
- Eloquent models
- authentication
- `/admin` dashboard
- CMS CRUD
- media management
- dynamic website content
