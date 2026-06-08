# MakedonIQ

MakedonIQ is a bilingual Macedonian learning quiz platform for families, students, schools, and community groups. It teaches Macedonian language, alphabet, history, geography, culture, food, and music through secure, database-backed quizzes.

## Features Completed

- Manual Laravel authentication: register, login, logout.
- Public Learn section with rich structured bilingual lessons.
- Lessons connected to quiz themes so learners can read before taking a quiz.
- Expanded original lesson and quiz content across language, alphabet, geography, history, culture, food, and music.
- Macedonia Map Challenge, a lightweight geography quiz mode with four small Geography map quizzes, a custom local 3D Macedonia map, and dynamic quiz markers.
- Picture quizzes using `question_type = picture_choice`, optional image metadata, and polished placeholders while final images are prepared.
- Folklore song lessons with placeholder-friendly `sound_choice` quizzes and future original-audio metadata.
- Bilingual public quiz categories, quizzes, questions, and answers.
- Published-only public content for categories, quizzes, and questions.
- Guest demo access: logged-out users can try selected demo lessons and quizzes, while the full published learning path requires an account.
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

## Demo Access Model

Quizzes and lessons have an `is_demo` boolean flag. Published content stays published, and `is_demo` controls whether logged-out users may open it.

Guest users can browse Learn and quiz category pages. Public list APIs return published content with `is_demo` and `is_locked` so the frontend can show demo cards, locked cards, and account CTAs. Guests can open only demo lesson details and demo quiz detail/question APIs. Direct API requests for locked quiz questions return `403` with `Create a free account to unlock this quiz.` Direct API requests for locked lesson details return `403` with `Create a free account to unlock this lesson.`

Logged-in users can access all published lessons and quizzes, submit quiz attempts, save scores, and see progress/results as before. Backend scoring remains server-side, and public quiz question responses still omit `is_correct`.

Admins can toggle `is_demo` in Admin Lessons and Admin Quizzes. The seeded demo set is:

```text
Demo quizzes:
- Basic Macedonian Greetings
- Cyrillic Alphabet Basics
- Macedonia Map Challenge Demo
- Jovano, Jovanke Sound Quiz

Demo lessons:
- Basic Greetings and Everyday Phrases
- Macedonian Cyrillic Alphabet Basics
- Cities, Lakes, and Mountains
- Jovano, Jovanke
```

Guest users can preview one demo `sound_choice` quiz per session. Logged-in users can access all published sound quizzes.

## Lesson Content

The seeded lesson library uses original MakedonIQ-written bilingual content with clear plain-text sections: introduction, learning goals, main explanation, key vocabulary or facts, examples, practice, remember notes, and related quiz connection. The current library balances language, alphabet, geography, history, culture, food, and music so each category has multiple beginner-friendly lessons.

Demo access stays intentionally limited to a few preview lessons for guests. All other published lessons remain available after login, and future lesson text can be reviewed or updated through Admin Lessons.

Do not seed copied textbook passages, copyrighted school-book text, copyrighted song lyrics, or unlicensed images/audio. Future audio should use original recordings or properly licensed short clips.

## Making a User Admin

Register normally first, then use Tinker:

```bash
php artisan tinker
```

```php
App\Models\User::where('email', 'your-email@example.com')->update(['is_admin' => true]);
```

Log out and log back in after changing admin status so the frontend receives the latest auth payload.

## Demo Guide

Seed demo content with:

```bash
php artisan migrate:fresh --seed
```

Create an admin by registering a normal user, then promoting that account with Tinker:

```php
App\Models\User::where('email', 'your-email@example.com')->update(['is_admin' => true]);
```

Suggested demo flow:

```text
1. Open Home and browse Learn.
2. Open a polished lesson, switch EN/MK, and use the related quiz CTA.
3. Complete a quiz and review the result page.
4. Open Dashboard and Progress to show saved attempts.
5. Open Map Challenge and complete the beginner map quiz.
6. Login as admin and show category, lesson, quiz, and question management.
```

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

Folklore song lessons are normal Learn records linked to normal quizzes through `quizzes.lesson_id`. Seeded sound quizzes use `question_type = sound_choice` with safe public metadata:

```json
{
  "audio_path": "/audio/lessons/song_001.mp3"
}
```

Audio files belong in:

```text
public/audio/lessons/
```

For now, `audio_path` can be null and the quiz UI shows an “Audio coming soon” placeholder instead of a broken player. Future original audio files should go in `public/audio/quizzes/` and use neutral MP3 filenames such as `song_001.mp3`, `song_002.mp3`, and `song_003.mp3`. Keep recordings short and optimized for web playback, around 10-20 seconds each. Public quiz-taking responses expose safe sound metadata such as `audio_path`, alt text, and audio type; they do not expose `is_correct`, `correct_answer`, or admin-only answer-key data.

Geography includes four small map quizzes with five `map_guess` questions each. `/map-challenge` is the dedicated hub for all four map quizzes, and the same quizzes also remain listed under `/quizzes/geography`. The first quiz, `Macedonia Map Challenge Demo`, is the only demo map quiz for guests. The other map quizzes require a free account for logged-out visitors and are available to logged-in users.

Map challenge questions use `question_type = map_guess` with `questions.metadata` for local map positioning. The frontend renders a custom 3D Macedonia map asset from `public/images` and overlays the pin dynamically from `map_x` and `map_y`. Public responses only expose safe marker metadata such as `map_x`, `map_y`, and `target_type`; admin-only target keys, target labels, answer keys, and correct-answer flags are not returned publicly. No external map API, Google Maps, Mapbox, Leaflet, or paid mapping service is used.

Picture quiz questions use `question_type = picture_choice` with optional `questions.metadata`:

```json
{
  "image_path": null,
  "image_alt_en": "Image clue coming soon",
  "image_alt_mk": "Слика за прашањето ќе биде додадена наскоро",
  "image_credit": "Placeholder. Original or properly licensed MakedonIQ image to be added later.",
  "image_type": "placeholder",
  "image_key": "food_tavce_gravce_001"
}
```

The seeded image quizzes currently leave `image_path` null. When it is blank, null, missing, or fails to load, the quiz UI shows a polished placeholder card instead of a broken image. Future final images should go in `public/images/quizzes/` and use neutral paths such as `/images/quizzes/img_001.webp` or a stable mapped filename based on the internal key.

Four locked, non-demo placeholder image quizzes are seeded: `guess-the-macedonian-food`, `macedonian-culture-picture-quiz`, `macedonian-places-picture-quiz`, and `macedonian-history-picture-quiz`. Public quiz-taking responses expose only safe picture metadata: `image_path`, `image_alt_en`, `image_alt_mk`, and `image_type`; they do not expose `is_correct`, `correct_answer`, answer keys, or descriptive internal `image_key` values. Admins can store image credit/source notes and internal image keys for later asset mapping. Use only original, public-domain, or properly licensed images; do not use copied textbook, schoolbook, or copyrighted images.

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

The map challenge hub and demo quiz are available at:

```text
/map-challenge
/quizzes/geography/macedonia-map-challenge-demo/start
/quizzes/geography/macedonia-map-challenge-demo/active
```

The full Geography map quiz set is:

```text
Macedonia Map Challenge Demo: 5 questions, demo
Cities of Macedonia Map Quiz: 5 questions, account required for guests
Lakes and Nature Map Quiz: 5 questions, account required for guests
Landmarks and Regions Map Quiz: 5 questions, account required for guests
```

Legacy public quiz API reads for `macedonia-map-challenge` resolve to the new demo quiz. The map quizzes use the normal authenticated quiz attempt endpoint for saved scoring, so backend scoring remains the source of truth.

The map challenge includes city, lake, nature, landmark, and region prompts for places such as Skopje, Ohrid, Bitola, Tetovo, Prilep, Kumanovo, Strumica, Veles, Stip, Gostivar, Lake Ohrid, Lake Prespa, Lake Dojran, Pelister, Mavrovo, Matka Canyon, Krusevo, Heraclea Lyncestis, Kokino, and Old Bazaar Skopje.

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
POST /api/admin/questions/{question}
DELETE /api/admin/questions/{question}
GET /api/admin/attempts
```

`POST /api/admin/questions/{question}` accepts multipart updates with `_method=PATCH` for MP3 replacement uploads from the admin question builder.

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
- Picture choice questions can store optional image path, alt text, image type, and image credit/source note metadata.
- Picture quiz images should be original, public domain, or properly licensed. Do not use copied textbook or schoolbook images.
- Sound choice questions can store optional `/audio/quizzes/song_###.mp3` metadata. Blank audio paths are valid while original recordings are being prepared.
- Sound quiz uploads are saved to `public/audio/lessons/` with neutral `song_###.mp3` filenames.
- Questions used in attempts are protected from destructive delete/answer replacement.
- Unpublished lessons do not appear on public Learn pages.

## Learn Section

The Learn section creates a simple learning loop:

```text
Read lesson -> Take related quiz -> Review results -> Revisit lesson
```

Seeded lessons currently cover beginner-friendly language, alphabet, geography, history, culture, food, and music topics. Content is original MakedonIQ-written material for this project and is not copied from Macedonian Bukvar, school textbooks, scans, maps, or textbook exercises.

Each lesson belongs to a category and can be connected to one or more quizzes through `quizzes.lesson_id`. Quiz start and result pages can show a related lesson CTA when a quiz is connected.

Folklore song lessons seed through `Database\Seeders\FolkloreMusicSeeder`, which is called by the main database seeder. To recreate the demo sound quizzes locally:

```bash
php artisan migrate:fresh --seed
```

Then place or upload optimized MP3 files at the seeded paths in `public/audio/lessons/`.

## Security Notes

- `.env` is ignored and must not be committed.
- Database dumps and real user data should not be committed.
- Quiz scoring is performed on the backend only.
- Public quiz-taking endpoints do not expose correct answers.
- Public map metadata exposes marker position/type only, not admin target keys or target labels.
- Public picture metadata exposes neutral image path, alt text, and image type only, and seeded picture quizzes use null paths until real images are added.
- Public sound metadata exposes only safe audio metadata: neutral `audio_path`, alt text, and audio type.
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
11. Macedonia Map Challenge loads all four map quiz cards and shows a marker without revealing the answer.
12. Public questions endpoint hides is_correct.
13. Public map metadata does not expose answer-revealing target labels.
14. Picture quizzes show an image when available or a placeholder when `image_path` is blank.
15. Public picture metadata handles `image_path: null` without a broken image.
16. Sound quizzes show an audio player when `audio_path` points to an MP3.
17. Public sound quiz metadata does not expose `is_correct` or `correct_answer`.
18. About and contact load.
19. Invalid paths show the friendly 404 page.
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
8. Admin question builder can create or edit `picture_choice` metadata with a blank image path.
9. Admin question builder can create or edit `sound_choice` metadata with a blank or neutral future audio path.
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
2. Confirm all four map quizzes appear.
3. Start the Macedonia Map Challenge Demo.
4. Confirm the other Geography map quizzes are visible but locked for guests.
5. View results.
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
