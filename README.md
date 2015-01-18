# UltraSlim
The ultra light functional slim framework, built on top of Slim Framework.

#### Why should I use Ultra with the SlimFramework?
Because with Ultra you have a full MVC application without sacrificing perfomance.

## Installation
* Download the master.zip file and alocate it in your project's folder.
* Run ```composer update``` in the project's folder.
* Go to http://localhost/project_name/public.

## How to use
First, create a route in `app/routes.php`.
```php
<?php

get('foo', 'HomeController@foo');
```
