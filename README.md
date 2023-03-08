# MycroBench | Lightweight time measure tool for PHP

This Package uses `$_SERVER['REQUEST_TIME_FLOAT']` and `microtime()` to calculate the difference that has passed between both times, when instantiated. It also provides a basic implementation of `hrtime()`, which allows measuring processing times with higher precision.

See [./public/index.php](./public/index.php) for examples or check the livedemo on [Vercel](https://mycro-bench.vercel.app/).

```terminal
composer require eypsilon/MycroBench
```

```php
use Many\MycroBench;

/**
 * @return array basic usage
 */
MycroBench::get()

// Array
// (
//     [start] => 2022-10-10 16:07:00.172129
//     [ended] => 2022-10-10 16:07:00.302257
//     [took] => 00.130128
//     [mem_usage] => 376.21 KB
//     [mem_peak] => 16.37 MB
//     [included_files_total] => 37
// )
```

## Consecutive requests

Get the processing time that has passed between consecutive requests with internally staid `start` times.

The first request will use `$_SERVER['REQUEST_TIME_FLOAT']` as `start` time, subsequent requests will use the `ended` time from previous requests.

Set the initial start time for benches manually to *now*. This is optional, but the first high resolution time will be wrong, if it's not instantiated.

```php
/** Sets the start time for @method getBench() and @method hrBench() to now */
MycroBench::initBench()
```

```php
/** @param bool true returns additional high resolution times */
MycroBench::getBench(true)

// [start] => 2022-10-10 16:07:00.176997
// [ended] => 2022-10-10 16:07:00.217398
// [took] => 00.040401
// [h_res] => 0.040415356

MycroBench::getBench(true)

// [start] => 2022-10-10 16:07:00.217398
// [ended] => 2022-10-10 16:07:00.239846
// [took] => 00.022448
// [h_res] => 0.022439554

MycroBench::getBench(true)

// [start] => 2022-10-10 16:07:00.239846
// [ended] => 2022-10-10 16:07:00.265409
// [took] => 00.025563
// [h_res] => 0.025560282
```


## High resolution times

Get `microtimes` with the system's high resolution time using [hrtime()](https://www.php.net/manual/de/function.hrtime.php).

Microsec: `00.019045`

High Reso: `0.019044332`

```php
// start bench
MycroBench::hrStart()

// do stuff here

// end bench, set first param true to get the result in return
MycroBench::hrEnd(false)

/** @return float|null '0.019044332' */
MycroBench::hrGet()
```

Get high resolution times with internally staid start times.

```php
/** @return float|null '0.019044332' */
MycroBench::hrBench()
```


#### Misc

```php
/**
 * microtimestamp to datetime
 *
 * @return string '2022-09-01 17:14:48.5000'
 */
(new MycroBench)->getDate('1662045288.5000') # 'U.u', 'Y-m-d H:i:s.u'

/**
 * datetime to microtimestamp
 *
 * @return string '1662045288.5000'
 */
(new MycroBench)->getDateToMicro('2022-09-01 17:14:48.5000') #'Y-m-d H:i:s.u', 'U.u'

/**
 * Get microtimestamp for the last request. Only filled
 * when @method getBench() has been called
 *
 * @return string|null '1662623500.7135'
 */
MycroBench::getLastDate()

/**
 * Readable bytes
 *
 * @return string '379.67 KB'
 */
MycroBench::readableBytes(memory_get_usage())

/**
 * Default getter
 *
 * start: $_SERVER['REQUEST_TIME_FLOAT']
 * ended: microtime(true)
 *
 * @static alias MycroBench::get()
 *
 * @param bool   enable (array) get_included_files()
 * @param string set a needle to shorten the paths returned in get_included_files()
 * @return array
 */
(new MycroBench)->getAll(true, '/remove/from/included_files/paths')

/**
 * Consecutive getter
 *
 * @static alias MycroBench::getBench()
 *
 * @param bool get additionally higher resolution times
 * @return array
 */
(new MycroBench)->getBenchDiff(true)
```
