# clearing cache
echo "clearing cache"
#rm -rf var/cache
php bin/console cache:clear --env=prod
php bin/console cache:clear --env=dev
composer clearcache
#php bin/console cache:clear --env=test