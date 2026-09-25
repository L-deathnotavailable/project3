# Transformez l'architecture d'une application existante

# Plot

Renote is an application that allows user to take and store notes.
In renote, a user can:
- create notes
- visualize notes
- define relationship between the notes
- define tags
- and associate a tag to a note.

## Install

1. Install Laravel's Herd:
https://laravel.com/docs/12.x/installation#installation-using-herd

This will install Php, Composer and Laravel.

2. Install node v22

Install node version manager (MVN).
On Windows you can use this distribution:
https://github.com/coreybutler/nvm-windows#readme


3. Clone this project

4. Copy `.env.example` to `.env`

5. Generate new APP_KEY with `php artisan key:generate`

6. Run `npm i` and `npm run dev`

7. Run `php artisan migrate`

8. Start Herd

9. Access to Herd link from your browser

You are setup!

## Architecture

The application follows Laravel's MVC structure:

- `app/Models`: Eloquent entities and relationships;
- `app/Http/Controllers`: web request orchestration;
- `app/Http/Requests`: reusable validation rules;
- `app/Policies`: resource authorization;
- `resources/views`: Blade presentation layer;
- `routes/web.php`: browser routes;
- `routes/api.php`: reserved entry point for versioned REST routes.

Future API controllers should be placed in `app/Http/Controllers/Api/V1` and reuse the
existing models, form requests and policies. JSON representation belongs in
`app/Http/Resources`, keeping it independent from the Blade views.

## REST API

The versioned API is available under `/api/v1` and uses Laravel Sanctum bearer tokens.
Its endpoints, response format and curl examples are documented in [`docs/API.md`](docs/API.md).
