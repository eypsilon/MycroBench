<?php error_reporting(E_ALL);

use Many\MycroBench;

/**
 * To use this example, copy the './tests/mykrobench'
 * directory to where composers  './vendor' directory is
 *
 * For demo purposes only
 *
 * $ ~/terminal/in/./tests/mykrobench
 * php -S localhost:8000
 * http://localhost:8000
 */

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

function print_pre($v) {
    printf('<pre>%s</pre>', htmlentities(print_r($v, true)));
}

/**
 * @param int $xTimes
 * @return string Exhausting function
 */
function do_some_exhausting_stuff(int $xTimes)
{
    do {
        range(0, $do[] = rand(1e3, 5e4));
    } while (--$xTimes);

    $do = array_map(function($each) {
        return number_format($each, 0, ',', '.');
    }, $do);

    return sprintf('ranged: %s', implode(', ', $do));
}


/**
 * @var int Run Benchmarks x times
 */
$doBenchys = 5;

/**
 * @var mixed Run Benchmarks
 */
if ($doBenchys) {
    foreach(range(1, $doBenchys) as $i) {
        // do some exhausting stuff
        $runBenchys['done_some_exhausting_stuff'][$i] = do_some_exhausting_stuff(rand(5, 10));

        try {
            $runBenchys['benchmarks'][$i] = (new MycroBench)->getBenchDiff();
        } catch(Exception $e) {
            $runBenchys['exception'][$i] = $e->getMessage();
        }
    }
}


/**
 * Template Engin © 1992 eypsilon
 */
?><!DOCTYPE html>
<html><head><meta charset="utf-8" />
<title>Many/MycroBench | local-dev-many-title</title>
<meta name="description" content="Many/MycroBench Example Page" />
<style>header, footer {text-align: center}</style>
</head>
<body>
<header>
    <h1>Many/MycroBench</h1>
</header>
<hr />
<main>
    <pre><?php
        print_r($runBenchys ?? null);

        try {
            print_r((new MycroBench)->get(false, realpath('../../..')));
        } catch(Exception $e) {
            print $e->getMessage();
        }
    ?></pre>
</main>
<hr />
<footer>
    <p>© Many/MycroBench</p>
</footer>
</body></html>
