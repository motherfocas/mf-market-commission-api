# MotherFocas Market Commission API

## Prerequisites

- MySQL / MariaDB
- PHP
- [Composer](https://getcomposer.org/)
- A web server

## Configuration

Install all dependencies:

```bash
composer install
```

Create tables:

```bash
bin/console orm:schema-tool:create
```

Configure Silex with web server:

[https://silex.symfony.com/doc/2.0/web_servers.html](https://silex.symfony.com/doc/2.0/web_servers.html)
