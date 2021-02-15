# Introduce

Privileges RPC Service

# Required

 1. mysql-server
 2. php-swoole
 3. php-thrift
 4. packagist-composer-idiorm
 5. ...

# How to start

```bash
git clone git@github.com:Shies/privileges.git
cd privileges
composer install
cp -r config.develop config
php bin/server.php
```

# Project origin

Project start from swoole/thrift-rpc-server for Microservices
