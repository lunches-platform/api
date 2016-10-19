# Lunches REST API 

[![Code Climate](https://codeclimate.com/github/lunches-platform/api/badges/gpa.svg)](https://codeclimate.com/github/lunches-platform/api)
[![Test Coverage](https://codeclimate.com/github/lunches-platform/api/badges/coverage.svg)](https://codeclimate.com/github/lunches-platform/api/coverage)

## Requirements

- PHP 5.5+
- MySQL 5.5+
- Apache
- composer

## Installation

- clone git repo
- `$ cd lunches-api`
- `$ mkdir var`
- `$ chmod 777 var`
- `$ cp app/config/parameters.yml.dist app/config/paramters.yml`
- edit `app/config/parameters.yml`
- composer install
- create your web server's virtual host, document root needs to be pointed to `/web` directory

