<?php error_reporting(E_ALL);

use Many\MycroBench;

/**
 * For demo purposes only
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param int $xTimes
 * @return string report
 */
function do_rand_stuff(int $xTimes)
{
    do {
        range(0, $do[] = rand(1e3, 5e5));
    } while (--$xTimes);

    return sprintf('ranged: %s', implode(', ',
        array_map(function($e) {
            return number_format($e, 0, ',', '.');
        }, $do)
    ));
}

/**
 * @var int Run Benchmarks x times
 */
$doBenchys = 25;

/**
 * Run Benchmarks
 */
if ($doBenchys)
{
    // Init bench, sets start time for microbench and high resolution bench to now.
    // This is optional, if it's not called, class will use $_SERVER['REQUEST_TIME_FLOAT']
    // as start time. The first high resolution time though will be wrong without this, but proceed anyway
    MycroBench::initBench();

    foreach(range(1, $doBenchys) as $i)
    {
        $i = str_pad($i, strlen((string) $doBenchys), '0', STR_PAD_LEFT);

        $runBenchys['task_list'][$i] = do_rand_stuff(rand(4, 9));

        try {
            // get micro bench with internally staid start times
            // set first param to true to get high resolution times
            $runBenchys['benchmarks'][$i] = MycroBench::getBench(true);

            // Get high resolution time only. The benches below will seem
            // "faster", that's because their start time is just 5 lines ago.
            // Since nothing happened in between, it takes practically no time.
            $runBenchys['hr_bench'][$i] = MycroBench::hrBench();
        } catch(Exception $e) {
            $runBenchys['exception'][$i] = $e->getMessage();
        }
    }
}

/**
 * @Template\Engin © 1992 eypsilon
 */
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= $cName = MycroBench::class ?> | local-dev-many-title</title>
<meta name="description" content="<?= $cName ?> Example Page">
<style>header, footer {text-align: center}</style>
</head>
<body>
<header>
    <h1><?= $cName ?></h1>
</header>
<hr />
<main>
    <pre><?php
        print_r($runBenchys ?? null);

        try {
            print_r(MycroBench::get(false, realpath('./../..')));
        } catch(Exception $e) {
            print '<hr /><h2>' . $e->getMessage() . '</h2>';
        }
    ?></pre>
</main>
<hr />
<footer>
    <p>© <?= date('Y') ?> <?= $cName ?></p>
</footer>
</body>
</html>
