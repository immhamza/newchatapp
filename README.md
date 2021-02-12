after cloning and  opening the project directory

use the following steps and commands to run your app
`composer install`

`cp .env.example .env`

`php artisan key:generate`

**open .env file and add your database name username and password**

`php artisan migrate`

`php artisan serve` in one terminal

`php artisan websockets:serve` in other terminal

**If there is error in websocket just go to URL http://127.0.0.1:8000/admin/websocket and hit on connect button and it will connect to port 6001**



