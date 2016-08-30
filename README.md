# Lunches REST API 

[![Code Climate](https://codeclimate.com/github/lunches-platform/api/badges/gpa.svg)](https://codeclimate.com/github/lunches-platform/api)
[![Test Coverage](https://codeclimate.com/github/lunches-platform/api/badges/coverage.svg)](https://codeclimate.com/github/lunches-platform/api/coverage)

## Requirements

- PHP 5.5+
- MySQL 5.5+
- Apache
- composer

## Installation

- create virtual host, document root needs to be pointed to `public` directory
- `$ mkdir -p shared/apache`
- `$ chmod -R 777 shared`
- `$ composer install`
- `$ cp public/.htaccess.dist public .htaccess`
- update .htaccess
- create config file based on `config/env.php.dist` and update config options which relates to your environment
- create database
- run `$ vendor/bin/doctrine orm:schema-tool:update --force` from project root to create database schema
- run application in browser 

