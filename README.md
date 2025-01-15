# Planit

Planit is a web application to manage tasks / to-do lists
alone or with other people.
It is built with Symfony 7.2 and tailwindcss.

## Features

- Create projects and add tasks to it
- Assign tasks to users

## Development

### Requirements

- PHP 8.2 or higher
- Composer (https://getcomposer.org/download/)
- Symfony CLI (https://symfony.com/download)

1. Clone the repository

```bash
git clone https://github.com/LeTammo/planit.git
cd planit
```

2. Create a `.env.dev.local` file and set your APP_SECRET

```bash
php -r "print base64_encode(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'));"```
```

3. Install dependencies with composer

```bash
composer install
```

4. Create the database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Start the development server

```bash
symfony server:start
```

6. Start the tailwindcss watcher

```bash
php bin/console tailwind:build --watch
```