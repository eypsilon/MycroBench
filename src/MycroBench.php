<?php declare(strict_types=1);

namespace Many;

use DateTime;
use DateTimeZone;
use Exception;
use function array_filter;
use function array_map;
use function array_merge;
use function call_user_func;
use function count;
use function date_default_timezone_get;
use function floor;
use function func_get_args;
use function get_included_files;
use function is_callable;
use function is_iterable;
use function is_string;
use function log;
use function memory_get_peak_usage;
use function memory_get_usage;
use function microtime;
use function natsort;
use function pow;
use function print_r;
use function round;
use function sprintf;
use function str_replace;
use function substr;

/**
 * Measure run time in microseconds
 *
 * @author Engin Ypsilon <engin.ypsilon@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class MycroBench
{

    /**
     * @var string default date format (2022-09-08 10:25:32.7448) */
    const BENCH_DATE_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * @var string default microtime format (1662625532.7448) */
    const BENCH_MICRO_DATE_FORMAT = 'U.u';

    /**
     * @var string default diff format (00.0000) */
    const BENCH_MICRO_DIFF_FORMAT = '%S.%F';

    /**
     * @var string From Format */
    private string $useFromFormat = self::BENCH_MICRO_DATE_FORMAT;

    /**
     * @var string To Format */
    private string $useToFormat = self::BENCH_DATE_FORMAT;

    /**
     * @var string Diff Format */
    private string $useDiffFormat = self::BENCH_MICRO_DIFF_FORMAT;

    /**
     * @var string|null Sets the 'ended' time of the last request for subsequential requests */
    private static ?string $lastDate = null;


    /**
     * Get all
     *
     * @param bool $returnFiles get included files in an array
     * @param string $rmFromPath remove from each file path of included files, eg: realpath('../../../..')
     * @param array define a list of callables to get additionally data, eg: ['get_declared_classes']
     * @return array
     */
    function get(): array
    {
        $args = func_get_args();

        $r = array_merge(
            $this->getDateDiffToNow(),
            static::getMemUsage(true),
            static::getIncludedFiles($args[0] ?? false, $args[1] ?? null)
        );

        if (($args[2] ?? null) AND is_iterable($args[2]))
            foreach($args[2] as $add)
                if (is_callable($add))
                    $r['added'][$add] = call_user_func($add);

        return array_filter($r);
    }

    /**
     * Get datetime with microseconds
     *
     * @param string|null $d date, default microtime(true)
     * @param string $from format, eg: 'U.u'           # microtime
     * @param string $to format, eg:   'Y-m-d H:i:s.u' # date
     * @param int $substr
     * @return string|null
     */
    function getDate(string $date=null, string $from=null, string $to=null, int $substr=-2): ?string
    {
        if ($from)
            $this->useFromFormat = $from;
        if ($to)
            $this->useToFormat = $to;

        return $this->getFormattedDate((string) ($date ?? microtime(true)), null, null, $substr);
    }

    /**
     * Formats datetime with microseconds to microtime()
     *
     * @param string $date (2022-09-08 10:25:32.7448)
     * @param string|null $from format 'U.u'
     * @param string|null $to format 'Y-m-d H:i:s.u'
     * @param integer $substr
     * @return string|null
     */
    function getDateToMicro(string $date, string $from=null, string $to=null, int $substr=-2): ?string
    {
        $this->useFromFormat = $from ? $from : self::BENCH_DATE_FORMAT;
        $this->useToFormat = $to ? $to : self::BENCH_MICRO_DATE_FORMAT;

        return $this->getFormattedDate($date, null, null, $substr);
    }

    /**
     * Get formatted date. Default formats from microtime to datetime
     *
     * @param string $d date | microtime
     * @param string|null $ff from Format
     * @param string|null $tf to Format
     * @param integer $substr
     * @return ?string|null
     * @throws Exception
     */
    function getFormattedDate(string $d, string $ff=null, string $tf=null, int $substr=-2)
    {
        try {
            if ($createFrom = DateTime::createFromFormat($ff ? $ff : $this->useFromFormat, (string) $d)) {
                return substr($createFrom
                    ->setTimezone(new DateTimeZone(date_default_timezone_get()))
                    ->format($tf ? $tf : $this->useToFormat), 0, $substr);
            }
        } catch(Exception $e) {}
        throw new Exception(sprintf('Failed to create date object from: %s', print_r($d, true)));
    }

    /**
     * Returns the difference between two Dates with microseconds
     *
     * @param string|float $date1 date start
     * @param string|float $date2 date end
     * @param string $f format
     * @param int $s substr
     * @return string
     */
    function getDateDiff($d1, $d2, string $f=null, int $s=-2): string
    {
        try {
            return substr((new DateTime($d1))
                ->diff(new DateTime($d2))
                ->format(is_string($f) ? $f : $this->useDiffFormat), 0, $s);
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get the time difference of multiple calls with corrected start time for each new call
     *
     * @param string|null $time
     * @param array $r
     * @return array
     */
    function getBenchDiff(string $time=null, array $r=[]): array
    {
        $r = $this->getDateDiffToNow($time ?? self::$lastDate);
        self::$lastDate = $this->getDateToMicro($r['ended']);
        return $r;
    }

    /**
     * Get timestamp from last request
     *
     * @return string|null
     */
    static function getLastDate(): ?string
    {
        return self::$lastDate;
    }

    /**
     * Extended dateDiff, default uses $_SERVER['REQUEST_TIME_FLOAT'] as start time & microseconds(true) for ended
     *
     * @param string|float $startTime
     * @param string|null $fromFormat
     * @param string|null $toFormat
     * @param string|null $diffFormat
     * @return array
     */
    function getDateDiffToNow(
        $startTime = null,
        string $fromFormat = null,
        string $toFormat = null,
        string $diffFormat = null
    ): array {
        $startTime = $startTime ? $startTime : $_SERVER['REQUEST_TIME_FLOAT'];

        if ($fromFormat)
            $this->useFromFormat = $fromFormat;
        if ($toFormat)
            $this->useToFormat = $toFormat;
        if ($diffFormat)
            $this->useDiffFormat = $diffFormat;

        return [
            'start' => $start = $this->getDate((string) $startTime),
            'ended' => $current = $this->getDate(),
            'took' => $this->getDateDiff($start, $current),
        ];
    }

    /**
     * Readable Bytes
     *
     * @param int $b bytes
     * @param int $n number format decimal, numbers after comma
     * @param array $u unit file sizes
     * @return string|int
     */
    static function readableBytes(int $b, int $n=2, array $u=['B', 'KB', 'MB', 'GB', 'TB', 'PB'])
    {
        return $b > 0 ? round($b/pow(1024, ($i=floor(log($b, 1024)))), $n) . ' ' . $u[$i] : 0;
    }

    /**
     * Get memory_usage with peak_usage in a readable format
     *
     * @param bool return readable or plain
     * @return array
     */
    static function getMemUsage(bool $readable=false): array
    {
        $mem = memory_get_usage();
        $peak = memory_get_peak_usage();

        return [
            'mem_usage' => $readable ? static::readableBytes($mem) : $mem,
            'mem_peak' => $readable ? static::readableBytes($peak) : $peak,
        ];
    }

    /**
     * Get included files
     *
     * @param bool $returnFiles
     * @param string $rmFromPath removes given string from each file path, eg: realpath('../../..')
     * @return array
     */
    static function getIncludedFiles(?bool $returnFiles=false, ?string $rmFromPath=null): array
    {
        $gi = get_included_files();
        if ($returnFiles) {
            if ($rmFromPath) {
                $gi = array_map(function($each) use($rmFromPath) {
                    return str_replace($rmFromPath, '', $each);
                }, $gi);
            }
            natsort($gi);
        }

        return [
            'included_files_total' => count($gi),
            'included_files_list' => $returnFiles ? $gi : null,
        ];
    }

}
