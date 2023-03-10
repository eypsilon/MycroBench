<?php error_reporting(E_ALL);

use Many\MycroBench;

/**
 * For demo purposes only
 *
 * php -S localhost:8000
 * http://localhost:8000
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
 * @var ?int Run Benchmarks variable times
 */
$doXtimes = $_GET['x-times'] ?? null;

if ($doXtimes AND is_numeric($doXtimes) AND $doXtimes >= 1 AND $doXtimes <= 250)
{
    $doBenchys = $doXtimes;
} else {
    $doBenchys = 10;
}

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
            $runBenchys['benchmarks'][$i]['task'] = $runBenchys['task_list'][$i];
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
<style>
header, footer {text-align: center;}
header h1 {margin: 0;}
header p {margin: 5px 0;}
fieldset {margin: 10px 0; padding: 5px; display: flex; background: #f5f5f5; border-color: #fff;}
input, select {width: 100%; border-width: 1px 0;  background: #fff;}
[type=button] {pointer-events: none; color: #999;}
select, button {display: block; padding: 6px 5px; white-space: nowrap;}
pre {margin: 1em 0; white-space: pre-wrap;}
hr {margin: 1em 0;}
</style>
</head>
<body>
<header>
    <h1><a href="/"><?= $cName ?></a></h1>
</header>
<form action="" method="get">
    <fieldset>
        <button type="button">x-times</button>
        <select name="x-times" onchange="this.form.submit()">
            <?php
                $options = array_unique([
                    $doBenchys, ...range(10, 40, 10), ...range(100, 250, 50)
                ]);
                asort($options);

                foreach($options as $i)
                {
                    printf('<option value="%1$s"%2$s>%1$s</option>' . PHP_EOL
                        , $i
                        , $i == $doBenchys ? ' selected="selected"' : null
                    );
                }
            ?>
        </select>
        <button type="submit">Set</button>
    </fieldset>
</form>
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
