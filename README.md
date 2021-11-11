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
