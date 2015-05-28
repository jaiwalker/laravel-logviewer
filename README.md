# Laravel 5 Log Viewer

## Description

This package allows you to keep track of each one of your logs stored under `storage_path().'/logs'`. Minimal configuration required. This project was inspired by [this project](https://github.com/rap2hpoutre/laravel-log-viewer)

## Features

* Groups dated logs.
* Play well with both dated and common logs.
* `cat` and `tail` methods are supported. 
* When using `tail` method, log entries will be updated every 30 seconds (Interval can be configured)
* Download the full log.
* Even works with big logs!!

## Installation

Install with composer
```
composer require javobyte/laravel-logviewer
```

Add the service provider in your `config/app.php` file
```php
'JavoByte\Logviewer\LogviewerServiceProvider'
```

Publish the assets
```
php artisan vendor:publish --tag=assets --force
```

Add a route in `app\Http\routes.php` to the provided Controller
```
Route::resource('logs', '\JavoByte\Logviewer\LogsController', ['only' => ['index', 'show']]);
```

Edit the file `public/javobyte/js/javobyte.logviewer.config.json` to use the created route
```javascript
var logsPath = '/logs/';
```

And that's it, go to the defined route and start watching your logs.

## Configuration

All the log reading is done, obviusly in the server side, but it is all shown with javascript, so the configuration is done with javascript in the file `public/javobyte/js/javobyte.logviewer.config.json`.

These are the available options:

* `logsPath` : The path/route where the controller is.
* `refreshInterval` : Only used when the fetch method is `tail`. This is the interval in miliseconds where the log will be refreshed. Set 0 to disable refreshing.
* `columnsOrder` : The order of the columns to be shown. Possible values are : `'channel', 'date', 'time', 'level', 'content'`. To prevent a column to show, delete it from this list.
* `disableDateWhenIncluded` : Max size of the response. This is not strict. This means, the response size can be greater if when the limit was reached, a stacktrace was being loaded.
* `levelIcons` : Icons for each level. Change this if you want to use another. Icons can only be those provided by FontAwesome 4.3.0

## License

Released under the [MIT License](http://opensource.org/licenses/MIT)