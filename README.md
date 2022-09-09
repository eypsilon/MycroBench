# MycroBench | Lightweight time measure tool for PHP

This Package provides a method to get the time, a page or a script took to execute in microseconds. It uses the `$_SERVER['REQUEST_TIME_FLOAT']` variable as starting time and `microtime()` as end time, and calculates the resulting difference. In order to provide correct 'start' times for subsequential requests, it also provides a method, that returns the 'ended' timestamp from the last request.

See [./tests/mykrobench](./tests/mykrobench/index.php) for examples.

```terminal
composer require eypsilon/MycroBench
```

```php
use Many\MycroBench;

/**
 * @var array
 */
(new MycroBench)->get()

// Array
// (
//     [start] => 2022-09-08 10:20:20.9107
//     [ended] => 2022-09-08 10:20:21.1141
//     [took] => 00.2034
//     [mem_usage] => 364.03 KB
//     [mem_peak] => 32.36 MB
//     [included_files_total] => 7
// )
```

## Subsequential requests

Execute subsequential requests with the correct 'start' time using the 'ended' time from the previous request.

```php
(new MycroBench)->getBenchDiff(MycroBench::getLastDate())

// [start] => 2022-09-08 10:20:20.9107
// [ended] => 2022-09-08 10:20:20.9788
// [took] => 00.0681
// [last] => 1662625220.9788

(new MycroBench)->getBenchDiff(MycroBench::getLastDate())

// [start] => 2022-09-08 10:20:20.9788
// [ended] => 2022-09-08 10:20:21.0531
// [took] => 00.0743
// [last] => 1662625221.0531

(new MycroBench)->getBenchDiff(MycroBench::getLastDate())

// [start] => 2022-09-08 10:20:21.0531
// [ended] => 2022-09-08 10:20:21.1140
// [took] => 00.0609
// [last] => 1662625221.1140
```

### Methods

```php
/**
 * Get timestamp with microseconds from the last request or null on first request.
 * Only available and updated, when (new MycroBench)->getBenchDiff() gets called
 *
 * @return string|null '1662623500.7135'
 */
MycroBench::getLastDate()

/**
 * Datetime with microseconds
 *
 * @return string '2022-09-01 17:14:48.5000'
 */
(new MycroBench)->getDate('1662045288.5000') # 'U.u', 'Y-m-d H:i:s.u'

/**
 * Formats datetime to timestamp with microseconds
 *
 * @return string '1662045288.5000'
 */
(new MycroBench)->getDateToMicro('2022-09-01 17:14:48.5000') # 'Y-m-d H:i:s.u', 'U.u'

/**
 * Readable Bytes
 *
 * @return string '379.67 KB'
 */
MycroBench::readableBytes(memory_get_usage())
```
