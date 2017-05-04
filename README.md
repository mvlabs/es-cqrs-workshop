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
bin/composer install
```

## Run the application

Run the application using

```bash
php -S localhost:8000 public/index.php
```

If you are using docker, you could use

```bash
bin/server
```