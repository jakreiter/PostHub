# PostHub

System for registering correspondence. Designed for organizations that receive traditional mail and for businesses providing mail handling services to other entities.

[Screenshots](docs/admin.md)

---

## Tech Stack

| Layer          | Technology                          |
|----------------|-------------------------------------|
| Backend        | PHP 8.3, Symfony 6.4                |
| Database       | MariaDB 10.11                       |
| Frontend       | Webpack Encore, Bootstrap 5, jQuery |
| Web server     | Nginx (Alpine)                      |
| Containerization | Docker, Docker Compose            |

---

## Running with Docker

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/)

### Quick Start

**1. Copy and configure the environment file:**

```bash
cp .env .env.local
```

Key variables in `.env.local`:

```dotenv
APP_ENV=dev           # dev or prod
APP_PORT=7678         # application port on the host
PMA_PORT=7679         # phpMyAdmin port on the host
DB_PORT=3767          # MariaDB port on the host
DB_NAME=posthub
DB_USER=posthub
DB_PASSWORD=posthub
DB_ROOT_PASSWORD=root
DATABASE_URL="mysql://posthub:posthub@mariadb:3306/posthub?serverVersion=mariadb-10.11"
```

**2. Start the containers:**

```bash
docker compose --env-file .env.local up -d
```

**3. Install PHP dependencies:**

```bash
docker compose exec php composer install
```

**4. Install JS dependencies and build assets:**

```bash
docker compose exec php yarn install
docker compose exec php yarn encore dev
```

**5. Create the database schema:**

```bash
docker compose exec php php bin/console doctrine:schema:update --force
```

**6. Load initial data** (optional — deletes existing data):

```bash
docker compose exec php php bin/console --env=dev doctrine:fixtures:load
```

### Service URLs

Ports depend on the values in `.env.local` (defaults shown below):

| Service     | URL                         | Credentials          |
|-------------|-----------------------------|----------------------|
| Application | http://localhost:7678       | (admin from fixtures)|
| phpMyAdmin  | http://localhost:7679       | root / root          |
| MariaDB     | localhost:3767              | posthub / posthub    |

---

## Useful Commands

```bash
# Open a shell in the PHP container
docker compose exec php bash

# Clear Symfony cache
docker compose exec php php bin/console cache:clear

# Run database migrations
docker compose exec php php bin/console doctrine:migrations:migrate

# Build assets with watch mode
docker compose exec php yarn encore dev --watch

# View logs
docker compose logs -f

# Stop all containers
docker compose --env-file .env.local down
```

### Rebuild the PHP image (after Dockerfile changes)

```bash
docker compose --env-file .env.local build php
docker compose --env-file .env.local up -d --no-deps php
```

---

## Local Installation (without Docker)

### Requirements

- PHP >= 8.2 with extensions: `ctype`, `curl`, `iconv`, `openssl`, `sysvsem`, `pdo_mysql`
- Composer 2
- Node.js + Yarn
- MariaDB / MySQL

### Steps

```bash
git clone https://github.com/jakreiter/PostHub.git posthub
cd posthub
cp .env .env.local        # set DATABASE_URL and other variables
composer install
yarn install
yarn encore dev
php bin/console doctrine:schema:update --force
php bin/console --env=dev doctrine:fixtures:load   # optional
```

---

## Xdebug

Xdebug is installed in the PHP container. To enable step debugging, set in `.env.local`:

```dotenv
XDEBUG_MODE=debug
```

Then restart the PHP container:

```bash
docker compose --env-file .env.local up -d --no-deps php
```

# Stop and remove all data (database, vendor volume)
docker compose down -v
```

### Configuration

Environment variables are set directly in `docker-compose.yml`. The file `.env.docker` is provided
as a reference of all Docker-safe defaults — you can copy it to `.env.local` if you prefer
file-based configuration:

```bash
cp .env.docker .env.local
```

### Upgrading to PHP 8.2

Change the build argument in `docker-compose.yml`:

```yaml
    build:
      args:
        PHP_VERSION: "8.2"
```

Then rebuild:

```bash
docker compose build --no-cache
docker compose up -d
```

