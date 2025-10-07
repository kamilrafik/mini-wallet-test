# How to run the project locally?

* Use PHP 8.4 locally in command line with the latest version of Composer. Turn off MySQL/MariaDB to free 3306 port. 
* ```composer install```
* Copy ```.env.example``` to ```.env```
* ```php artisan sail:install``` - choose MySQL and Soketi to be installed 
* ```./vendor/bin/sail up -d```
* ```./vendor/bin/sail npm install```
* ```./vendor/bin/sail artisan key:generate``` 
* ```./vendor/bin/sail artisan migrate```
* ```./vendor/bin/sail artisan serve```
* ```./vendor/bin/sail npm run dev```
* Access ```http://localhost```

# Implementation notes

* Some built-in or default functionality has been installed with Laravel, Sail, and Vue. It has not been removed and was kept as is.
* Authentication has been implemented with Breeze Vue scaffolding because it is not the primary subtask in the test and can be completely done with standard libraries.
