# How to run the project locally?

* Use PHP 8.4 locally in command line with the latest version of Composer (to run commands before Docker will be started with Sail). Turn off local MySQL/MariaDB, Redis, and webserver to free the appropriate ports. 
* ```composer install```
* Copy ```.env.example``` to ```.env```
* ```php artisan sail:install``` - choose MySQL, Soketi, and Redis to be installed 
* ```./vendor/bin/sail up -d```
* ```./vendor/bin/sail npm install```
* ```./vendor/bin/sail artisan key:generate``` 
* ```./vendor/bin/sail artisan migrate```
* ```./vendor/bin/sail npm run dev```
* Access ```http://localhost```

# Implementation notes

* Some built-in or default functionality has been installed with Laravel, Sail, and Vue. It has not been removed and was kept as is.
* Authentication has been implemented with Breeze Vue scaffolding because it is not the primary subtask in the test and can be completely done with standard libraries.
* Default MySQL capabilities and settings for InnoDB engine allow completely to handle amount of transactions specified in the specification. That's why only minimal optimization on business logic layer has been implemented. Also, indexes have been configured correctly. All production-level optimizations should consist a) in finetuning of server settings, b) geographic sharding/partitiioning, c) archiving of old transactions OR native time-based partitioning as it is made in PostgreSQL, e) keeping all data in memory and performing hard writes in low load hours only (see geographic sharding/partitioning to understand how to apply it correctly). 
* Table with balances is separated from table with user accounts because a) table with accounts was generated automatically, b) both tables will have completely different scenarios of usages, so mixing data is not a good idea.
* User ID column in balances table is made nullable to allow GDPR compliance without affecting company's business.
* Business logic classes of different types have been placed into single subfolder of Features (but not distributed over all application) because of relatively small size of the functionality.
* UUIDs have been implemented in all main tables as public IDs for security reasons. UUIDs are generated in code because we generated UUID v7 (not supported in MySQL yet), and need to support master-slave configuration of MySQL server with identical UUIDs in both. However, full replacement of IDs with UUIDs in authentication logic has not been performed because it will require serious intrusion into built-in Laravel logic which is definitely outside of the scope of the task. So, UUIDs are implemented as security concept primarily to demonstrate understanding of this topic.
* Optimistic concurrency control is used in wallet's main method that creates transactions and updates balances.
* Cache lock with Redis has been implemented to avoid processing identical user requests in case of noticeable latency. 
* No logic has been placed into Eloquent models for maximum scalability in the future, however models by themselves are used to avoid writing raw SQL queries. Repositories have been created for the most often used operations only.
* Matching between backend and frontend DTOs is incomplete but for test task is enough. The concept of common DTOs is deomonstrated well enough.
* Structure of some DTOs could be less flat. But current is enough for test task.
* Development is performed on laptop. That's why so-called "backup" commits are used when draft semi-completed functionality is uploaded every 15-30 minutes while massive updates in code are performed.
