
Project for subject IS project - Point of sale system
=================

Nette Web Project
-------

- Web Project for Nette 3.1 requires PHP 8.1 and- MySQL 8.*

Installation
------------

Make directories `var/temp/` and `var/log/` writable. (chmod -R 777)

It is possible to run docker in .docker folder:
    - run:   docker compose -p pos up -d --build --force-recreate


```bash
$ mv config/local.neon.dist config/local.neon (copy file and insert connection config to DB)
$ composer install (inside docker: docker exec pos composer install)
$ php bin/console doctrine:database:create # will take database name from config file (in docker is created)
$ php bin/console migrations:migrate

Make directories `temp/` and `log/` writable. (chmod 776)
```

Local Development
--------------


Web Server Setup
----------------

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you
should be ready to go.

**It is CRITICAL that whole `app/`, `config/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).**
