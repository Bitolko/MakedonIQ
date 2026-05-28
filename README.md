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

Quiz submission, backend scoring, saved attempts, review answers, and real result calculations are intentionally not built yet. Those should be added in the next backend step so correct answers remain protected server-side.

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
