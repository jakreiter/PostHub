# PostHub
System for registering parcels.
It can be used in organizations that receive traditional mail
or to provide correspondence services to other businesses.


[screenshots](docs/admin.md)

## Requirements
See composer.json

## Instalation
* Checkout GIT repository

```bash
git clone https://github.com/jakreiter/PostHub.git posthub
```


* Create .env.local file and change it manually with the machine specific values

```bash
cp .env .env.local

```

* Create .htaccess file and change it manually if needed (for example redirect http to https)

```bash
cp public/.htaccess.dist public/.htaccess
```

Production values in .env.local:
APP_ENV - 'prod' for production, 'dev' for developing


```bash
composer install
```


* Clear Symfony cache.

```bash
./cc.sh
```
## Creating database structure
 ```bash
php bin/console doctrine:schema:update --dump-sql
php bin/console doctrine:schema:update --force
 ```
## Initial data hydration
### Dictionaries (only in fresh database - deletes database data)
 ```bash
php bin/console --env=dev doctrine:fixtures:load
 ```

---

## Running with Docker

### Prerequisites
- [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/) installed

### Quick Start

1. **Start the containers** (app, database, phpMyAdmin):

```bash
docker compose up -d
```

On first start the entrypoint script automatically runs `composer install` and `cache:clear`.
Wait a moment for the initial setup to complete — you can watch the logs with:

```bash
docker compose logs -f app
```

2. **Build frontend assets** (one-off):

```bash
docker compose run --rm node
```

3. **Create the database schema**:

```bash
docker compose exec app php bin/console doctrine:schema:update --force
```

4. **Seed initial data** (optional — deletes existing data):

```bash
docker compose exec app php bin/console --env=dev doctrine:fixtures:load
```

### Access

| Service    | URL                        | Credentials              |
|------------|----------------------------|--------------------------|
| Application| http://localhost:8080       | (use seeded admin user)  |
| phpMyAdmin | http://localhost:8081       | root / root              |
| MariaDB    | localhost:3307             | posthub / posthub        |

### Useful Commands

```bash
# View app logs
docker compose logs -f app

# Open a shell inside the app container
docker compose exec app bash

# Clear Symfony cache
docker compose exec app php bin/console cache:clear

# Stop all containers
docker compose down

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

