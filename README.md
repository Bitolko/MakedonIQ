# MakedonIQ

MakedonIQ is a bilingual Macedonian learning quiz platform for families, students, schools, and community groups. It teaches Macedonian language, alphabet, history, geography, culture, food, and music through secure, database-backed quizzes.

## Features Completed

- Manual Laravel authentication: register, login, logout.
- Public Learn section with short bilingual lessons.
- Lessons connected to quiz themes so learners can read before taking a quiz.
- Expanded original lesson and quiz content across language, alphabet, geography, history, culture, food, and music.
- Macedonia Map Challenge, a lightweight geography quiz mode with local SVG-style map markers.
- Bilingual public quiz categories, quizzes, questions, and answers.
- Published-only public content for categories, quizzes, and questions.
- Secure backend quiz scoring; the frontend never receives correct-answer flags during quiz taking.
- Saved quiz attempts and attempt-answer review.
- Authenticated result pages with owner-only attempt access.
- Dashboard analytics from real user attempts.
- Progress tracking with category progress, quiz history, achievements, and score trends.
- Profile page with name, email, password, and preferred language settings.
- Preferred language defaults for bilingual quiz display where supported.
- Admin role protection using `users.is_admin`.
- Admin read-only reporting.
- Admin category management.
- Admin lesson management.
- Admin quiz management.
- Admin question builder with exactly four answers and exactly one correct answer.
- Friendly 403 and 404 pages.
- Basic SEO metadata and favicon wiring.

## Tech Stack

- Laravel
- Vue
- Tailwind CSS
- MySQL
- Vite

## Local Setup

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Configure MySQL in `.env`, then run:

```bash
php artisan migrate --seed
php artisan serve
npm run dev
```

The local app normally runs at:

```text
http://127.0.0.1:8000
```

## MySQL Setup Example

Use your own local password. Do not commit `.env` or real credentials.

```sql
CREATE DATABASE makedoniq CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'makedoniq_user'@'127.0.0.1' IDENTIFIED BY 'replace_with_a_local_password';
GRANT ALL PRIVILEGES ON makedoniq.* TO 'makedoniq_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Example `.env` database values:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=makedoniq
DB_USERNAME=makedoniq_user
DB_PASSWORD=your_local_password
```

## Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan route:list
php artisan makedoniq:health-check
npm run build
php artisan config:clear
```

## Making a User Admin

Register normally first, then use Tinker:

```bash
php artisan tinker
```

```php
App\Models\User::where('email', 'your-email@example.com')->update(['is_admin' => true]);
```

Log out and log back in after changing admin status so the frontend receives the latest auth payload.

## Main Routes

Public:

```text
/
/learn
/learn/{categorySlug}
/learn/{categorySlug}/{lessonSlug}
/map-challenge
/quizzes
/quizzes/{categorySlug}
/quizzes/{categorySlug}/{quizSlug}/start
/quizzes/{categorySlug}/{quizSlug}/active
/quizzes/{categorySlug}/{quizSlug}/results
/about
/contact
```

Auth:

```text
/login
/register
/dashboard
/progress
/profile
```

Admin:

```text
/admin
/admin/categories
/admin/lessons
/admin/quizzes
/admin/questions
```

Legacy history quiz aliases are still available for compatibility:

```text
/quizzes/history
/quizzes/history/start
/quizzes/history/active
/quizzes/history/results
```

## API Overview

Public category and quiz APIs:

```text
GET /api/lessons
GET /api/lessons/{lesson}
GET /api/categories/{category}/lessons
GET /api/categories
GET /api/categories/{slug}
GET /api/categories/{slug}/quizzes
GET /api/quizzes/{slug}
GET /api/quizzes/{slug}/questions
```

Learn APIs return published lessons only, and only when the related category is published. Lesson details can include related quizzes with start URLs.

The current starter content includes 30 original bilingual lessons across:

```text
Macedonian Language
Macedonian Alphabet
History of Macedonia
Geography
Culture and Traditions
Food and Music
```

The public questions endpoint returns answer IDs and text only. It does not expose `is_correct`.

Map challenge questions use `question_type = map_guess` with `questions.metadata` for local map positioning. Public responses only expose safe marker metadata such as `map_x`, `map_y`, and `target_type`; admin-only target keys and labels are not returned publicly. No external map API, Google Maps, Mapbox, Leaflet, or paid mapping service is used.

Authenticated quiz and learner APIs:

```text
POST /api/quizzes/{quizSlug}/attempts
GET /api/quiz-attempts/{attemptId}
GET /api/me
PATCH /api/me/profile
PATCH /api/me/password
GET /api/me/dashboard
GET /api/me/progress
```

Dashboard and progress stats are generated from:

```text
quiz_attempts
quiz_attempt_answers
quizzes
categories
```

Lessons are generated from:

```text
lessons
categories
quizzes.lesson_id
```

The Macedonia Map Challenge is seeded as a published Geography quiz at:

```text
/map-challenge
/quizzes/geography/macedonia-map-challenge/start
/quizzes/geography/macedonia-map-challenge/active
```

It uses the normal authenticated quiz attempt endpoint for saved scoring, so backend scoring remains the source of truth.

The map challenge includes city, lake, and landmark prompts for places such as Skopje, Ohrid, Bitola, Prilep, Tetovo, Kumanovo, Strumica, Veles, Stip, Gostivar, Struga, Kicevo, Kavadarci, Gevgelija, Kocani, Lake Ohrid, Lake Prespa, Matka Canyon, Vodno, Mavrovo, and Pelister.

Admin APIs require session auth plus `admin` middleware:

```text
GET /api/admin/overview
GET /api/admin/categories
POST /api/admin/categories
GET /api/admin/categories/{category}
PATCH /api/admin/categories/{category}
DELETE /api/admin/categories/{category}
GET /api/admin/lessons
POST /api/admin/lessons
GET /api/admin/lessons/{lesson}
PATCH /api/admin/lessons/{lesson}
DELETE /api/admin/lessons/{lesson}
GET /api/admin/quizzes
POST /api/admin/quizzes
GET /api/admin/quizzes/{quiz}
PATCH /api/admin/quizzes/{quiz}
DELETE /api/admin/quizzes/{quiz}
GET /api/admin/questions
GET /api/admin/quizzes/{quiz}/questions
POST /api/admin/quizzes/{quiz}/questions
GET /api/admin/questions/{question}
PATCH /api/admin/questions/{question}
DELETE /api/admin/questions/{question}
GET /api/admin/attempts
```

## Admin Content Rules

- Category and quiz slugs can be auto-generated from English names/titles.
- Duplicate generated slugs receive numeric suffixes.
- Categories with quizzes cannot be deleted; unpublish instead.
- Lesson slugs can be auto-generated from English titles.
- Lessons linked to quizzes cannot be deleted; unpublish or unlink the quiz first.
- Lessons can be connected to quizzes from the admin quiz form.
- Quizzes with questions or attempts cannot be deleted; unpublish instead.
- Questions must have exactly four answers.
- Exactly one answer must be correct.
- Map guess questions can store marker metadata for the local illustrated map.
- Questions used in attempts are protected from destructive delete/answer replacement.
- Unpublished lessons do not appear on public Learn pages.

## Learn Section

The Learn section creates a simple learning loop:

```text
Read lesson -> Take related quiz -> Review results -> Revisit lesson
```

Seeded lessons currently cover beginner-friendly language, alphabet, geography, history, culture, food, and music topics. Content is original MakedonIQ-written material for this project and is not copied from Macedonian Bukvar, school textbooks, scans, maps, or textbook exercises.

Each lesson belongs to a category and can be connected to one or more quizzes through `quizzes.lesson_id`. Quiz start and result pages can show a related lesson CTA when a quiz is connected.

## Security Notes

- `.env` is ignored and must not be committed.
- Database dumps and real user data should not be committed.
- Quiz scoring is performed on the backend only.
- Public quiz-taking endpoints do not expose correct answers.
- Public map metadata exposes marker position/type only, not admin target keys or target labels.
- Attempt results require authentication and ownership.
- Profile endpoints cannot update `is_admin`.
- Password updates require the current password and store only a hash.
- Admin pages and admin APIs require authenticated admin access.
- The app uses Laravel session/cookie auth with CSRF protection, not token auth, Breeze, or Inertia.

## Manual Testing Checklist

Public:

```text
1. Home loads.
2. Learn loads.
3. Learn category page loads.
4. Each Learn category shows multiple lessons.
5. Lesson detail page loads.
6. EN/MK lesson toggle works.
7. Quizzes load with expanded quiz content.
8. Category page loads.
9. Quiz start loads and shows related lesson when available.
10. Active quiz loads.
11. Macedonia Map Challenge loads and shows a marker without revealing the answer.
12. Public questions endpoint hides is_correct.
13. Public map metadata does not expose answer-revealing target labels.
14. About and contact load.
15. Invalid paths show the friendly 404 page.
```

Auth/user:

```text
1. Register, login, and logout work.
2. Dashboard loads real user stats.
3. Progress loads real quiz history.
4. Profile updates name, email, password, and preferred language.
5. Quiz submission and backend scoring work.
6. Results page loads for the attempt owner.
```

Admin:

```text
1. Normal users cannot access admin pages or APIs.
2. Admin users can access admin pages and APIs.
3. Admin category CRUD works.
4. Admin lesson CRUD works.
5. Admin quiz CRUD works, including related lesson assignment.
6. Admin question builder validates exactly four answers and exactly one correct answer.
7. Admin question endpoint may show correctness because it is admin-only.
```

## Deployment Notes

This repository is ready for a standard Laravel deployment, but deployment itself is intentionally manual for now. Do not deploy with local development settings, and do not commit `.env`, database dumps, backups, or real credentials.

Production `.env` checklist:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-generated-production-key

DB_CONNECTION=mysql
DB_HOST=your-production-host
DB_PORT=3306
DB_DATABASE=your-production-database
DB_USERNAME=your-production-user
DB_PASSWORD=your-production-password

SESSION_DRIVER=database or file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file or database
QUEUE_CONNECTION=sync
```

Use `SESSION_SECURE_COOKIE=true` only when the site is served over HTTPS. The MVP does not currently require queues, so `QUEUE_CONNECTION=sync` is acceptable until background jobs are introduced.

Recommended deployment commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

Run `php artisan storage:link` only if the deployed environment needs public storage links for uploaded files. The current MVP does not depend on user-uploaded public assets.

Route and config cache:

- Web routes are controller-backed, so `php artisan route:cache` is intended to be safe.
- Configuration uses Laravel config files for environment reads, so `php artisan config:cache` is intended to be safe.
- If deployment changes route or config files, clear and rebuild the caches.

Build output:

- The app shell uses Laravel Vite via `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- `public/build` is ignored in Git and should be generated during deployment with `npm run build`.
- `public/hot` is ignored and should not exist in production.

Database and seeding strategy:

- Local/dev reset: `php artisan migrate:fresh --seed`.
- Production migration: `php artisan migrate --force`.
- Do not run `migrate:fresh` in production.
- The seeders create starter MakedonIQ categories, lessons, quizzes, map questions, and answers. Run production seeders only intentionally and only after backing up the database.

Production safety checklist:

- Web server document root points to `public`.
- Storage and cache directories are writable by the web server.
- `public/hot` is not present in production.
- `.env`, database dumps, and backups are not publicly accessible.
- `APP_DEBUG=false`.
- Production database is backed up before migrations.
- A previous release or rollback path is available.

## Deployment Health Check

Run this before demo or deployment:

```bash
php artisan makedoniq:health-check
```

The command checks core content counts, question answer integrity, map question coordinates, public quiz controller response safety, admin-user presence, and `APP_DEBUG` state. Warnings should be reviewed; failed checks should be fixed before production.

## Admin Setup for Deployment

No default weak admin account is seeded. Register a normal user first, then promote that account:

```bash
php artisan tinker
```

```php
App\Models\User::where('email', 'your-email@example.com')->update(['is_admin' => true]);
```

Log out and log back in, then visit `/admin`. The `is_admin` field is not mass assignable, and admin APIs require session authentication plus the `admin` middleware.

## Demo Checklist

User journey:

```text
1. Register.
2. Login.
3. Browse Learn.
4. Read a lesson.
5. Start a related quiz.
6. Complete the quiz.
7. View results.
8. View dashboard.
9. View progress.
10. Update profile and preferred language.
```

Admin journey:

```text
1. Login as admin.
2. Open the admin dashboard.
3. Create or update a category.
4. Create or update a lesson.
5. Create or update a quiz.
6. Create a question with exactly 4 answers and exactly 1 correct answer.
7. Publish/unpublish content.
8. Confirm public visibility changes.
```

Map Challenge:

```text
1. Open /map-challenge.
2. Start the Macedonia Map Challenge.
3. Complete the map quiz.
4. View results.
```

Rollback note:

- Back up the database before running production migrations.
- Keep the previous deployed release available when possible.
- If a migration or build fails, restore the previous release and database backup rather than running destructive refresh commands.

## Not Built Yet

- Payments and subscriptions.
- Full site i18n.
- Complex clickable map coordinates or external map integrations.
- Mobile app.
- Audio pronunciation.
- School accounts.
- Teacher dashboards.
- Certificates.
- Richer analytics and exports.
- Production deployment automation.
