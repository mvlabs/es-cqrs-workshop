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