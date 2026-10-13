# Local MySQL with Docker

Docker is optional. Laravel runs on the Windows host; only MySQL runs in this container. Collaborators using native MySQL do not need Docker or application-code changes.

## Start

Run from the project root:

    docker compose -f compose.mysql.yml up -d --wait

Connection settings for the ignored local .env:

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3307
    DB_DATABASE=seminar_ta
    DB_USERNAME=seminar
    DB_PASSWORD=
    DB_URL=

Port 3307 avoids the existing Jurnal Kelas MySQL on 3306. Native MySQL users can keep port 3306 from Azka's .env.example. Keep these machine-specific differences in .env, not in shared application code.

The container initializes seminar_ta and seminar_ta_test and limits the seminar user's grants to those databases. This local empty-password setup is bound only to host loopback. Persistent data is in the enrolify_enrolify_mysql_data Docker volume.

## Database initialization

On a new empty database only:

    php artisan config:clear
    php artisan migrate
    php artisan db:seed

Initial setup created four seeded accounts and three rooms. Existing SQLite data was preserved, not copied into MySQL. Do not run migrate:fresh or delete the volume to start the app.

## Stop and resume

    docker compose -f compose.mysql.yml stop
    docker compose -f compose.mysql.yml start

Do not use down -v unless intentionally deleting the database.

## Tests

Azka's phpunit.xml uses MySQL on port 3306; when testing this Docker instance, override DB_PORT to 3307 and use seminar_ta_test, never seminar_ta. Frontend's phpunit.xml defaults to isolated SQLite. To explicitly test Docker MySQL from bash:

    DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3307 DB_DATABASE=seminar_ta_test DB_USERNAME=seminar DB_PASSWORD='' DB_URL='' php artisan test

On this Windows machine, call C:/Users/Tenma/.config/herd-lite/bin/php.exe directly if the shell PHP wrapper fails. PHP must have pdo_mysql enabled.
