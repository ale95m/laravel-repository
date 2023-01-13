### About Easy
Easy is a package that seeks to speed up and simplify API development with Laravel.

### Installation
To install Easy using the Composer package manager
```
composer require ale95m/easy
```

When you install Easy, *EasyServiceProvider* registers its own database migration directory, so you should migrate your database after installing the package.
```
php artisan migrate
```
### Configuration
To publish the Easy configuration, you only have to execute the command
```
php artisan vendor:publish --tag=easy-config
```

Later we can find the configuration file in "config/easy.php"
