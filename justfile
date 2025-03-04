set shell := ["powershell.exe", "-Command"]

MESSENGER_VERBOSITY := '-v'
PHP := 'php bin/console'

docker_up: docker_build
    docker-compose up -d

docker_down:
    docker-compose down

docker_build:
    docker-compose build

docker_logs:
    docker-compose logs -f

composer_install:
    composer install

composer_update:
    composer update

messenger_consume: docker_up
    {{PHP}} messenger:consume --all {{MESSENGER_VERBOSITY}}

messenger_debug:
    {{PHP}} debug:messenger

messenger_stop:
    {{PHP}} messenger:stop-workers

scheduler_debug:
    {{PHP}} debug:scheduler

spotify_tracker_start: messenger_consume

cache_clear:
    {{PHP}} cache:clear

cache_warmup: cache_clear
    {{PHP}} cache:warmup

migration_new:
    {{PHP}} doctrine:migrations:generate

migration_make:
    {{PHP}} make:migration

migration_list:
    {{PHP}} doctrine:migrations:status

migration_apply: docker_up
    {{PHP}} doctrine:migrations:migrate --no-interaction

server_start:
    symfony server:start --allow-all-ip

server_stop:
    symfony server:stop

server_logs:
    symfony server:log
