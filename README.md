Silex Skeleton
=============

## Requirements
   
- PHP >= 7.1
- PostgreSQL >= 9.5
- Docker
- Docker Compose
- Docker Machine
- GitFlow

## Development
### Fix Doctrine ProxyGenerator

```patch runtime/vendor/doctrine/common/lib/Doctrine/Common/Proxy/ProxyGenerator.php  < proxy_generator.patch```

### Docker Environment Setup

You must add env GIT_OAUTH_TOKEN (It's github oauth token) to fpm service.
``` yaml
fpm:
  environment:
    GIT_OAUTH_TOKEN: "your_token"
```

Set env for PostgreSQL credentials:
 - {APP_ENV}_DB_NAME
 - {APP_ENV}_DB_HOST
 - {APP_ENV}_DB_USER
 - {APP_ENV}_DB_PASSWORD

Set env for Redis credentials:
 - {APP_ENV}_REDIS_HOST
 - {APP_ENV}_REDIS_PORT
 - {APP_ENV}_REDIS_AUTH

Copy local dev config

```
cp config/config.local.php.dist config/config.local.php
```

Build docker images and start containers:

```
docker login -u <your_login> -p <your_password> https://dore.devim.team
docker-compose build --pull
docker-compose up -d
```

Setup PHP vendors and tests:

```
docker-compose exec fpm composer install --prefer-dist && runtime/vendor/bin/codecept build
```

If you use VMware Fusion:

```
docker-compose exec fpm chmod 777 -R runtime/
```

If you use VirtualBox: 

```
chmod 777 -R runtime/
```

### Prepare Database

```
docker-compose exec db createdb project_dev
docker-compose exec fpm bin/console orm:schema-tool:create
```

### Run Test

```
docker-compose exec fpm runtime/vendor/bin/codecept run
```

## XDebug

Set env variable XDEBUG_REMOTE_HOST (please provide your host (local machine IP) where PhpStorm is running)
in docker composer for fpm service.

Add Remote Debug Configuration.

Create server, Languages & Frameworks -> PHP -> Servers:

  - Name `docker`
  - Host `docker.dev`
  - Port `8089`
  - Debugger `XDebug`

Setup debug port, Languages & Frameworks -> PHP -> Debug: Debug port `9090`

Create application configuration:
  - Name `Debug`
  - Ide key `PHPSTORM`
  - Server `docker`
  
## Helpful commands

Rebuild database

```
$ docker-compose exec fpm bin/console app:rebuild-database
```
