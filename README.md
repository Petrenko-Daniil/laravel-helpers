### LaravelHelpers package lets you write your helpers using static class methods and then use them whereever you want in Laravel helpers style

#### Installation:

    composer require danilpetrenko/laravel-helpers
    php artisan vendor:publish
You can create ***Helpers*** folder inside your ***app*** folder and then start writing helpers
Note that you must always specify return type of your static method, otherwise no return will be provided
ExampleHelper:

    static function divideByFive(int $number): int  
	{  
	  return (int)$number/5;  
	}
Then you're supposed to run command

    php artisan generate-helpers-autoload

will generate following function inside ***bootstrap/helpers.php***:

    function divideByFive(int $number): int{  
      return App\Helpers\TestHelper::divideByFive($number);  
    }
Then you should add ***bootstrap/helpers.php*** into autoload section of your ***composer.json*** file:

    "autoload": {  
      "psr-4": {  
      "App\\": "app/",  
            "Database\\Factories\\": "database/factories/",  
            "Database\\Seeders\\": "database/seeders/"  
      },  
      "files": [ "bootstrap/helpers.php" ]  
    },
Now you are able to use your helper inside any class or view like Laravel's default helper

    <h3>{{divideByFive(25)}}<h3>
---
PHPDocs will be also copied to autoload file, so helper usage explanation will be available when using function.
