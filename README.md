# MakedonIQ

MakedonIQ is a bilingual Macedonian learning quiz platform for families, students, schools, and community groups. It teaches Macedonian language, alphabet, history, geography, culture, food, and music through secure, database-backed quizzes.

## Features Completed

- Manual Laravel authentication: register, login, logout.
- Public Learn section with short bilingual lessons.
- Lessons connected to quiz themes so learners can read before taking a quiz.
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

The public questions endpoint returns answer IDs and text only. It does not expose `is_correct`.

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

Admin APIs require session auth plus `admin` middleware:

```text
GET /api/admin/overview
GET /api/admin/categories
POST /api/admin/categories
GET /api/admin/categories/{category}
PATCH /api/admin/categories/{category}
DELETE /api/admin/categories/{category}
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
- Quizzes with questions or attempts cannot be deleted; unpublish instead.
- Questions must have exactly four answers.
- Exactly one answer must be correct.
- Questions used in attempts are protected from destructive delete/answer replacement.
- Admin lesson CRUD is not built yet; lessons are currently seeded and read publicly when published.

## Learn Section

The Learn section creates a simple learning loop:

```text
Read lesson -> Take related quiz -> Review results -> Revisit lesson
```

Seeded lessons currently cover:

- Basic Macedonian greetings.
- Introduction to the Macedonian Cyrillic alphabet.
- Beginner-friendly Macedonia history basics.
- Macedonian geography basics.
- Culture and traditions.
- Food and music.

Each lesson belongs to a category and can be connected to one or more quizzes through `quizzes.lesson_id`. Quiz start and result pages can show a related lesson CTA when a quiz is connected.

## Security Notes

- `.env` is ignored and must not be committed.
- Database dumps and real user data should not be committed.
- Quiz scoring is performed on the backend only.
- Public quiz-taking endpoints do not expose correct answers.
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
4. Lesson detail page loads.
5. EN/MK lesson toggle works.
6. Quizzes load.
7. Category page loads.
8. Quiz start loads and shows related lesson when available.
9. Active quiz loads.
10. Public questions endpoint hides is_correct.
11. About and contact load.
12. Invalid paths show the friendly 404 page.
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
4. Admin quiz CRUD works.
5. Admin question builder validates exactly four answers and exactly one correct answer.
6. Admin question endpoint may show correctness because it is admin-only.
```

## Deployment Notes

Do not deploy with local development settings.

Production checklist:

```text
APP_ENV=production
APP_DEBUG=false
```

Then configure production database credentials and run:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Also confirm:

- Web server document root points to `public`.
- Storage and cache directories are writable by the web server.
- `public/hot` is not present in production.
- `.env`, database dumps, and backups are not publicly accessible.

## Not Built Yet

- Payments and subscriptions.
- Full site i18n.
- Admin lesson CRUD.
- Mobile app.
- Audio pronunciation.
- School accounts.
- Teacher dashboards.
- Certificates.
- Richer analytics and exports.
- Production deployment automation.
