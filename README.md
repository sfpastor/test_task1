Test task based on Symfony Demo Application
========================

Requirements
------------
  * PHP 7.3 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

Installation
------------
There are a few ways how to install and run this application. 

1. 
Make sure you have already installed both [Docker Engine][6] and [Docker Compose][7]. 
Then run these commands: 
```bash
$ docker-compose build
$ docker-compose up
```
Wait until migrations will be executed.   

2. 
[Download Symfony][4] to install the `symfony` binary on your computer
[Download and install Composer][5] 

Then run these commands:
```bash
$ composer install -n
$ bin/console doc:mig:mig --no-interaction
$ bin/console --env=test doc:mig:mig --no-interaction
$ symfony server:start
```

Usage
------------
Access the application in your browser at the given URL (<http://localhost:8000> by default).
You need to go to Backend section and login as administrator. Credentials can be found on [Login][8] page. 
In Backend section you should see menu [Users list][9]. Please use this page to manage users.

Tests
-----
Execute this command to run tests:
```bash
$ cd my_project/
$ ./bin/phpunit
```

Logs
------------
The info about all changes for existing users are logging into separate file:
/var/log/custom.dev.log


[1]: https://symfony.com/doc/current/best_practices.html
[2]: https://symfony.com/doc/current/reference/requirements.html
[3]: https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html
[4]: https://symfony.com/download
[5]: https://getcomposer.org/download/
[6]: https://docs.docker.com/get-docker/
[7]: https://docs.docker.com/compose/install/
[8]: http://localhost:8000/en/login
[9]: http://localhost:8000/en/admin/user/
