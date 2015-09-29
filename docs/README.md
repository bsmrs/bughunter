# Configure environment using composer
----

## To development environment
composer global require --dev install
composer install --no-dev

## To production environment
composer install --no-dev

## Update libraries
composer update [--no-dev | --dev]
