# PostHub — Agent Guidelines

Correspondence registration system. See [README.md](README.md) for quick-start and local installation instructions.

## Architecture

| Layer            | Technology                                      |
|------------------|-------------------------------------------------|
| Backend          | PHP 8.3, Symfony 6.4                            |
| Database         | MariaDB 10.11, Doctrine ORM + Migrations        |
| Frontend         | Webpack Encore, Bootstrap 5, jQuery, Stimulus   |
| Web server       | Nginx (Alpine)                                  |
| Containerization | Docker, Docker Compose                          |

## src/ Structure

```
src/
  Command/          # Symfony console commands
  Controller/       # HTTP controllers — naming convention: {Subject}{Admin|User}Controller
  DataFixtures/     # Doctrine fixtures (dev/test data)
  Entity/           # Doctrine entities: User, Organization, Location, Letter, LetterStatus,
                    #   ScanPlan, Notification, PassResetEmail, PassSetter
  Form/             # Symfony form types + Filter/ subdirectory
  Model/            # Plain PHP models (not persisted)
  Repository/       # Doctrine repositories
  Security/         # Voters, authenticators
  Service/          # Business logic services
```

Controller naming split: `*AdminController` → `/admin/*` routes; `*UserController` → regular user routes.

## Security & Role Hierarchy

```
ROLE_ADMIN
  └─ ROLE_LOCATION_ADMIN
       ├─ ROLE_LOCATION_MODERATOR
       └─ ROLE_USER
  └─ ROLE_ALLOWED_TO_SWITCH   (impersonation)
```

- `/admin/*` and `/kadmin/*` require `ROLE_ADMIN`
- Authentication: form login (email-based), switch_user enabled

## Build & Assets

Assets are compiled with Webpack Encore on the **host** (Node.js), not inside the container.

```bash
yarn dev          # one-off dev build
yarn watch        # dev build with watch mode
yarn build        # production build
```

Built files land in `public/build/`. If the directory is owned by root (created inside Docker), fix with:

```bash
sudo chown -R $USER:$USER public/build
```

**Important:** `tablesorter` must be loaded via `require()` (not `import`), and should not be wrapped in a nested `$(function(){})` inside a `rebind()` call that is already running inside a ready handler. Additionally, `tablesorter@2.32+` declares a peer-style dependency on `jquery@4`, which yarn installs as a nested copy under `node_modules/tablesorter/node_modules/jquery`. The plugin then registers `$.fn.tablesorter` on that nested jQuery, while the app uses `jquery@3.7.1`, producing `$(...).tablesorter is not a function`. This is solved in [webpack.config.js](webpack.config.js) with `addAliases({ jquery: require.resolve('jquery') })`, which forces every `require('jquery')` to resolve to a single instance — keep that alias in place.

## Database

```bash
# Run pending migrations
docker compose exec php php bin/console doctrine:migrations:migrate

# Load dev fixtures (DELETES existing data)
docker compose exec php php bin/console --env=dev doctrine:fixtures:load --no-interaction
```

New migrations are generated with:

```bash
docker compose exec php php bin/console doctrine:migrations:diff
```

## Tests

PHPUnit via Symfony bridge. Config: `phpunit.xml.dist`, bootstrap: `tests/bootstrap.php`, env: `APP_ENV=test`.

```bash
docker compose exec php php bin/phpunit
```

## Docker Services

All `docker compose` commands require `--env-file .env.local`.

| Service     | Container           | Default port |
|-------------|---------------------|--------------|
| php (FPM)   | `posthub_php`       | —            |
| nginx        | `posthub_nginx`     | `APP_PORT` (7678) |
| mariadb      | `posthub_mariadb`   | `DB_PORT` (3767)  |
| phpmyadmin   | `posthub_phpmyadmin`| `PMA_PORT` (7679) |

Rebuild PHP image after `docker/php/Dockerfile` changes:

```bash
docker compose --env-file .env.local build php
docker compose --env-file .env.local up -d --no-deps php
```

## Conventions

- PSR-4 autoload: `App\` → `src/`; all services use autowiring and autoconfiguration
- Translations: `translations/messages.{pl,en}.yaml`, `translations/validators.{pl,en}.yaml`
- Environment files: `.env` (committed defaults), `.env.local` (local overrides, not committed)
- Cache clear: `docker compose exec php php bin/console cache:clear` (or `bash cc.sh` inside the container)
