<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## MakedonIQ Local MySQL Setup

This project uses Laravel's default users table for authentication. For local development, create a MySQL database and local user before running migrations.

In MySQL, run:

```sql
CREATE DATABASE makedoniq CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'makedoniq_user'@'127.0.0.1' IDENTIFIED BY 'replace_with_a_local_password';
GRANT ALL PRIVILEGES ON makedoniq.* TO 'makedoniq_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

Then configure Laravel:

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env` and fill in your local MySQL password:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=makedoniq
DB_USERNAME=makedoniq_user
DB_PASSWORD=your_local_password
```

Then run:

```bash
php artisan config:clear
php artisan migrate --seed
```

To rebuild the local database from scratch with the starter quiz content:

```bash
php artisan migrate:fresh --seed
```

## MakedonIQ Quiz Database

The core quiz schema includes:

- `categories`: bilingual quiz categories such as language, alphabet, history, geography, culture, and food/music.
- `quizzes`: bilingual quizzes that belong to a category.
- `questions`: bilingual questions that belong to a quiz.
- `answers`: bilingual answer options that belong to a question.
- `quiz_attempts`: score summaries for a user taking a quiz.
- `quiz_attempt_answers`: selected answers and points awarded inside an attempt.

Each seeded question has exactly four answers and exactly one correct answer. A portable database-level constraint for "only one correct answer per question" is intentionally not added yet because partial unique indexes differ across database engines; this should be enforced in the future admin/application layer.

## Seeded Starter Content

Running `php artisan migrate:fresh --seed` creates:

- Macedonian Language: Basic Macedonian Greetings
- Macedonian Alphabet: Cyrillic Alphabet Basics
- History of Macedonia: Macedonia History Basics
- Geography: Macedonian Geography Basics
- Culture and Traditions: Macedonian Culture Basics
- Food and Music: Macedonian Food and Music Basics

Each starter quiz includes at least five beginner-friendly bilingual questions.

## Read-Only API Endpoints

These public JSON endpoints are available for future frontend integration:

```text
GET /api/categories
GET /api/categories/{slug}
GET /api/categories/{slug}/quizzes
GET /api/quizzes/{slug}
GET /api/quizzes/{slug}/questions
```

The public questions endpoint returns published questions and answer choices without exposing `is_correct`.

## Quiz Submission API

Quiz scoring is handled on the backend. The active quiz page sends selected answer IDs only; Laravel checks correctness using the private `answers.is_correct` value.

Authenticated endpoints:

```text
POST /api/quizzes/{quizSlug}/attempts
GET /api/quiz-attempts/{attemptId}
GET /api/me/dashboard
GET /api/me/progress
```

Submission payload:

```json
{
  "answers": [
    {
      "question_id": 1,
      "answer_id": 4
    }
  ]
}
```

The submission endpoint validates that every submitted question belongs to the quiz and every selected answer belongs to its question. It creates a `quiz_attempt`, creates matching `quiz_attempt_answers`, calculates score, percentage, points, and pass/fail, then returns a result URL.

Attempt results are authenticated and owner-only. Correct answers and explanations are returned only from the completed-attempt endpoint, after the current user has been authorized to view that attempt.

## Dashboard And Progress API

Dashboard and progress analytics now use Laravel session/cookie authentication. Vue requests include `Accept: application/json` and `credentials: same-origin`; unsafe requests continue to include the CSRF token. Token auth, Breeze, and Inertia are not used.

Authenticated analytics endpoints:

```text
GET /api/me/dashboard
GET /api/me/progress
```

Current dashboard analytics:

- User summary with name and email.
- Summary stats for total points, completed quiz count, total attempts, passed attempts, average score, best score, and a simple completed-day streak.
- Latest five completed attempts with bilingual quiz/category labels and result URLs.
- Recommended published quizzes the user has not completed yet, with latest published quizzes as a fallback.
- Category progress across published categories and published quizzes, including completed quiz count, best score, and points.

Current progress analytics:

- Overall points, attempts, completed quizzes, average score, best score, passed attempts, and current streak.
- Category progress with completed quiz counts, best category score, and points.
- Latest 20 completed quiz attempts with bilingual quiz/category labels.
- Derived achievements: First Quiz Completed, Passed First Quiz, Perfect Score, Completed 3 Quizzes, Macedonian Explorer, and Dedicated Learner.
- Recent score trend percentages. No chart library is used.

Dashboard and progress stats are generated from `quiz_attempts`, `quiz_attempt_answers`, `quizzes`, and `categories`. Analytics are scoped to the authenticated user with `quiz_attempts.user_id`; users cannot fetch another user's dashboard, progress, or attempt result data through these endpoints.

## Admin Access

Admin pages are protected with Laravel session auth plus a simple `users.is_admin` boolean. Normal registered users are not admins by default.

Protected admin web routes:

```text
/admin
/admin/quizzes
/admin/questions
```

Admin category CRUD endpoints:

```text
GET /api/admin/categories
POST /api/admin/categories
GET /api/admin/categories/{category}
PATCH /api/admin/categories/{category}
DELETE /api/admin/categories/{category}
```

Admin quiz CRUD endpoints:

```text
GET /api/admin/quizzes
POST /api/admin/quizzes
GET /api/admin/quizzes/{quiz}
PATCH /api/admin/quizzes/{quiz}
DELETE /api/admin/quizzes/{quiz}
```

Admin read-only reporting endpoints:

```text
GET /api/admin/overview
GET /api/admin/questions
GET /api/admin/attempts
```

All admin API endpoints require an authenticated admin user. Category and quiz slugs are generated from the English name/title when left blank, with numeric suffixes added when needed for uniqueness. Deleting categories is blocked when they contain quizzes. Deleting quizzes is blocked when they contain questions or attempts, so unpublishing is the safer recommended action. Question and answer CRUD is still coming soon.

To make a local user an admin, run:

```bash
php artisan tinker
```

Then update the user by email:

```php
App\Models\User::where('email', 'your-email@example.com')->update(['is_admin' => true]);
```

After changing admin status, log out and log back in so the frontend auth payload includes the latest `is_admin` value. Admin category and quiz CRUD are available; question/answer CRUD and advanced content management are intentionally not built yet.

Local dashboard/progress test flow:

```text
1. Register a new user or log in with test@example.com / password.
2. Complete at least one quiz.
3. Visit /dashboard and confirm points, attempt counts, category progress, recommendations, and recent results are real.
4. Visit /progress and confirm quiz history, achievements, and score trends are real.
5. Log out and confirm /dashboard, /progress, /api/me/dashboard, and /api/me/progress require authentication.
6. Log in as a second user and confirm the second user does not see the first user's attempts.
```

Local test flow:

```text
1. Register or log in.
2. Open /quizzes/history-of-macedonia/macedonia-history-basics/active.
3. Answer every question.
4. Submit the quiz.
5. Confirm the browser opens /quizzes/history-of-macedonia/macedonia-history-basics/results/{attemptId}.
6. Review the real score and answer breakdown.
```

## Public Quiz Routes

The quiz frontend now loads public quiz data from the API/database.

Examples:

```text
/quizzes
/quizzes/history-of-macedonia
/quizzes/history-of-macedonia/macedonia-history-basics/start
/quizzes/history-of-macedonia/macedonia-history-basics/active
/quizzes/history-of-macedonia/macedonia-history-basics/results
```

The older history URLs still work as aliases:

```text
/quizzes/history
/quizzes/history/start
/quizzes/history/active
/quizzes/history/results
```

Backend scoring, saved attempts, real result display, and authenticated dashboard/progress analytics are now built. Admin CRUD, roles, payments, subscriptions, and advanced analytics are intentionally not built yet.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
