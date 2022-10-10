<?php declare(strict_types=1);

namespace Many;

use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use function array_filter;
use function array_map;
use function array_merge;
use function array_values;
use function count;
use function date_default_timezone_get;
use function floatval;
use function floor;
use function func_get_args;
use function get_included_files;
use function hrtime;
use function is_string;
use function log;
use function memory_get_peak_usage;
use function memory_get_usage;
use function microtime;
use function natsort;
use function number_format;
use function pow;
use function print_r;
use function round;
use function sprintf;
use function str_replace;

/**
 * Measure run time in microseconds and high resolution times
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
     * @var float|null Keeps the 'ended' time of the last request for subsequent requests */
    private static $lastDate = null;

    /**
     * @var array temp hrtime storage */
    private static array $hrTime = [];

    /**
     * Initialise MycroBench, sets start time for microbench and high resolution bench to now
     *
     * @return void
     */
    public static function initBench(): void
    {
        self::$lastDate = static::microNumFormat(microtime(true));
        static::hrStart();
        return;
    }

    /**
     * Get hrtime()
     *
     * @return bool|float|int
     */
    public static function getHrTime()
    {
        return hrtime(true);
    }

    /**
     * Start high resolution bench
     *
     * @return void
     */
    public static function hrStart(): void
    {
        self::$hrTime = ['start' => static::getHrTime()];
        return;
    }

    /**
     * End high resolution bench
     *
     * @param bool get result in return
     * @return string|null
     * @throws InvalidArgumentException
     */
    public static function hrEnd(bool $r=false): ?string
    {
        if (empty(self::$hrTime['start'])) {
            throw new InvalidArgumentException('Missing start time');
        }

        self::$hrTime['ended'] = static::getHrTime();

        return $r ? static::hrGet() : null;
    }

    /**
     * Get high resolution time
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public static function hrGet(): string
    {
        if (empty(self::$hrTime['ended']) OR empty(self::$hrTime['start'])) {
            throw new InvalidArgumentException('Missing start/ended time for high resolution bench');
        }

        $diff = self::$hrTime['ended'] - self::$hrTime['start'];
        static::hrStart();

        return number_format($diff/1e+9, 9);
    }

    /**
     * Get high resolution time with internally staid start times
     *
     * @return string
     */
    public static function hrBench(): string
    {
        if (empty(self::$hrTime['start']))
            static::hrStart();

        return static::hrEnd(true);
    }

    /**
     * Alias for getAll()
     *
     * @param bool return included files
     * @param string shortens the path of included files by removing matching part
     * @return array
     */
    public static function get(): array
    {
        return (new self)->getAll(...func_get_args());
    }

    /**
     * Default get
     *
     * @param bool enable (array) get_included_files()
     * @param string set a needle to shorten the paths returned in get_included_files(), eg: realpath('../../../..')
     * @return array
     */
    public function getAll(): array
    {
        $args = func_get_args();

        $r = array_merge(
            $this->getDateDiffToNow(),
            static::getMemUsage(true),
            static::getIncludedFiles($args[0] ?? false, $args[1] ?? null)
        );

        return array_filter($r);
    }

    /**
     * Alias for getBenchDiff()
     *
     * @param bool high resolution times
     * @return array
     */
    public static function getBench(): array
    {
        return (new self)->getBenchDiff(...func_get_args());
    }

    /**
     * Get bench times for consecutive requests with internally staid start times
     *
     * @param bool get additionally high resolution times
     * @return array
     */
    public function getBenchDiff(bool $hrb=false): array
    {
        $r = $this->getDateDiffToNow(self::$lastDate);

        if ($hrb)
            $r['h_res'] = static::hrBench();

        self::$lastDate = $this->getDateToMicro($r['ended']);

        return $r;
    }

    /**
     * Get datetime with microseconds
     *
     * @param string|null date, default: microtime(true)
     * @param string|null from format, eg: 'U.u'           # microtime
     * @param string|null to format, eg:   'Y-m-d H:i:s.u' # date
     * @return string|null
     */
    public function getDate(string $date=null, string $from=null, string $to=null): ?string
    {
        if ($from)
            $this->useFromFormat = $from;

        if ($to)
            $this->useToFormat = $to;

        // if microtime hits ".000000", it returns int timestamp instead of float
        if (!$date)
            $date = static::microNumFormat(microtime(true));

        return $this->getFormattedDate((string) $date);
    }

    /**
     * Formats datetime with microseconds to microtime()
     *
     * @param string date '2022-09-08 10:25:32.7448'
     * @param string|null from format, default 'U.u'
     * @param string|null to format,   default 'Y-m-d H:i:s.u'
     * @return string|null
     */
    public function getDateToMicro(string $date, string $from=null, string $to=null): ?string
    {
        $this->useFromFormat = $from ? $from : self::BENCH_DATE_FORMAT;
        $this->useToFormat = $to ? $to : self::BENCH_MICRO_DATE_FORMAT;

        return $this->getFormattedDate($date);
    }

    /**
     * Returns the difference between two Dates with microseconds
     *
     * @param string|float date start
     * @param string|float date end
     * @param string return format
     * @return string
     * @throws Exception
     */
    public function getDateDiff($d1, $d2, string $f=null): string
    {
        try {
            return (new DateTime($d1))
                ->diff(new DateTime($d2))
                ->format(is_string($f) ? $f : $this->useDiffFormat);
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Readable Bytes
     *
     * @param int bytes
     * @param int number format decimal, numbers after comma
     * @param int unit file size index
     * @param array unit file sizes
     * @return string|int
     */
    public static function readableBytes(int $b, int $n=2, int $i=0, array $u=['B', 'KB', 'MB', 'GB', 'TB', 'PB'])
    {
        return $b > 0 ? round($b/pow(1024, ($i=floor(log($b, 1024)))), $n) . ' ' . $u[$i] : 0;
    }

    /**
     * Get memory_usage with peak_usage
     *
     * @param bool return readable or plain
     * @return array
     */
    public static function getMemUsage(bool $readable=false): array
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
     * @param bool return Files in an array
     * @param string removes given string from each file path, eg: realpath('../../..')
     * @return array
     */
    public static function getIncludedFiles(bool $returnFiles=false, string $rmFromPath=null): array
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
            'included_files_list' => $returnFiles ? array_values($gi) : null,
        ];
    }

    /**
     * Format microtime to float
     *
     * @param float|int|string microtime
     * @return string
     */
    protected static function microNumFormat($mtime): string
    {
        return number_format(floatval($mtime), 6, '.', '');
    }

    /**
     * Get formatted date. Default formats from microtime to datetime
     *
     * @param string date | microtime
     * @param string|null from Format
     * @param string|null to Format
     * @return string|null
     * @throws Exception
     */
    protected function getFormattedDate(string $d, string $ff=null, string $tf=null)
    {
        try {
            $ff = $ff ? $ff : $this->useFromFormat;
            if ($createFrom = DateTime::createFromFormat($ff, (string) $d)) {
                return $createFrom
                    ->setTimezone(new DateTimeZone(date_default_timezone_get()))
                    ->format($tf ? $tf : $this->useToFormat);
            }
        } catch(Exception $e) {}

        throw new Exception(sprintf('Failed to create date object from: %s', print_r($d, true)));
    }

    /**
     * Extended dateDiff
     *
     * @param string|float start Time
     * @param string|null from Format
     * @param string|null to Format
     * @param string|null diff Format
     * @return array
     */
    protected function getDateDiffToNow(
        $startTime = null,
        string $fromFormat = null,
        string $toFormat = null,
        string $diffFormat = null
    ): array {

        if ($fromFormat)
            $this->useFromFormat = $fromFormat;
        if ($toFormat)
            $this->useToFormat = $toFormat;
        if ($diffFormat)
            $this->useDiffFormat = $diffFormat;

        if (!$startTime)
            $startTime = static::microNumFormat($_SERVER['REQUEST_TIME_FLOAT']);

        return [
            'start' => $start   = $this->getDate((string) $startTime),
            'ended' => $current = $this->getDate(),
            'took'  => $this->getDateDiff($start, $current),
        ];
    }

}
