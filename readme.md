# jaxwilko/profiler

This package aids development by allowing you to profile sections of your codebase.

## Installation

```shell
composer require "jaxwilko/profiler" --dev
```

## Usage

Firstly, you need to [install ext-xdebug](https://xdebug.org/docs/install).

Then add the following to your `php.ini`:

```ini
[xdebug]
xdebug.mode = develop,gcstats,profile,trace
xdebug.collect_return = 1
xdebug.collect_assignments = 1
xdebug.use_compression = false
```

Once this has been done, you can add the following to your codebase:

```php
use JaxWilko\Profiler\Watcher;

Watcher::start();
// execute application logic
Watcher::end(__DIR__ . '/output.html');
```
The filename passed will be used to store the resulting parsed profile. Alternatively you can call `end()`
without any params to get the profile as a variable.
```php
$profile = Watcher::end();
```
The watcher also supports returning the profile as an array, this can be done as follows:
```php
Watcher::setMode(Watcher::OUTPUT_ARR);
$profile = Watcher::end();
```
There are helpers provided to achieve the same functionality:
```php
jax_watcher_start();
// execute application logic
jax_watcher_end($outputFile);
```
