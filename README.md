# MycroBench | Lightweight time measure tool for PHP

This class uses `$_SERVER['REQUEST_TIME_FLOAT']` and current `microtime()` to calculate the difference that has passed between the two timestamps in microseconds.

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
//     [start] => 2022-09-12 18:34:24.4330
//     [ended] => 2022-09-12 18:34:24.4368
//     [took] => 00.0038
//     [mem_usage] => 358.49 KB
//     [mem_peak] => 1.34 MB
//     [included_files_total] => 7
// )
```

## Subsequent requests

Get the difference of multiple calls with corrected start times for each new call.

```php
(new MycroBench)->getBenchDiff()

// [start] => 2022-09-12 18:34:24.4330
// [ended] => 2022-09-12 18:34:24.4356
// [took] => 00.0026

(new MycroBench)->getBenchDiff()

// [start] => 2022-09-12 18:34:24.4356
// [ended] => 2022-09-12 18:34:24.4362
// [took] => 00.0006

(new MycroBench)->getBenchDiff()

// [start] => 2022-09-12 18:34:24.4362
// [ended] => 2022-09-12 18:34:24.4367
// [took] => 00.0005
```

### Methods

```php
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
 * Get timestamp with microseconds from the last request or null on first request.
 * Only available and updated, when (new MycroBench)->getBenchDiff() gets called
 *
 * @return string|null '1662623500.7135'
 */
MycroBench::getLastDate()

/**
 * Readable Bytes
 *
 * @return string '379.67 KB'
 */
MycroBench::readableBytes(memory_get_usage())
```
