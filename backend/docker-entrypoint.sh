#!/bin/sh
set -e

# Wait for database with timeout
echo "Waiting for database at $MYSQL_HOST:$MYSQL_PORT..."
timeout=30
counter=0

while ! nc -z "$MYSQL_HOST" "$MYSQL_PORT"; do
    counter=$((counter + 1))
    if [ $counter -gt $timeout ]; then
        echo "Error: Database connection timeout after ${timeout} seconds"
        exit 1
    fi
    echo "Waiting for database to be ready... ($counter/$timeout)"
    sleep 1
done

echo "Database is ready!"

# Reset the cache
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

echo "Starting messenger consumer"
exec php bin/console messenger:consume --all -v