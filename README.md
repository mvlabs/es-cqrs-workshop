# Event sourcing/CQRS workshop

Source code for es/cqrs workshop

## Installation

Clone the repository with

```bash
git clone git@github.com:mvlabs/es-cqrs-workshop.git
```

and install all the required dependencies with

```bash
composer install
```

If you are using docker, you could use

```bash
docker build -t escqrs-workshop-composer ./docker/composer/   -- this is needed only the first time
bin/composer install
```

### Database

To setup the database for the event store, you will need to have access to a Postgresql instance.
At the moment the application is configured to connect on the port `5432` on the host `postgres`
to a database named `mvlabs` with username `mvlabs` and password `mvlabs`.

If you are using docker, this will be already be present, and you will be able to access it through
pgAdmin navigating to `localhost:5050`.

Once you have the database set up, you will need to create the table that will contain the event stream
running the `sql` script you can find in `vendor/prooph/pdo-event-store/scripts/postgres/01_event_streams_table.sql`

Then you will need to run the script `scripts/create_event_stream.php`. If you are using docker, you could use

```bash
docker exec -ti escqrs-workshop-php php /app/scripts/create_event_stream.php
```

after you run `docker-compose up`.

## Run the application

Run the application using

```bash
php -S localhost:8000 public/index.php
```

If you are using docker, you could use

```bash
docker-compose up
```

Adding `127.0.0.1 escqrs-workshop.local` to your hosts you will be able
to navigate to [http://escqrs-workshop.local/](http://escqrs-workshop.local/)
and see the wonderful application