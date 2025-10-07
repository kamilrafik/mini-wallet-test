# How to run the project locally?

* Copy ```.env.example``` to ```.env```
* ```./vendor/bin/sail up -d```
* ```./vendor/bin/sail artisan key:generate``` 
* ```./vendor/bin/sail php artisan migrate```
* ```./vendor/bin/sail php artisan serve```
* ```./vendor/bin/sail npm run dev```
* Access ```http://localhost```

# Implementation notes

* Some built-in or default functionality has been installed with Laravel, Sail, and Vue. It has not been removed and was kept as is.
* MariaDB has been used although in specification MySQL is required. It was made accidentally, and it is not critical for the project, so it was kept as is.
