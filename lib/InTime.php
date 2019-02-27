<?php

namespace BugbirdCo\InTime;

use DateTime;
use DateInterval;
use Exception;

/**
 * Class InTime
 * Wraps DateInterval and adds some functions to get total denominations of time.
 *
 * This is helpful for when you are defining an interval, say 3 days ("P3D"), but need
 * the total value in, for example, seconds.
 *
 * This allows you to configure time intervals in a readable string expression, rather than
 * an expression of multiplications, or an integer.
 *
 * E.g.
 * Redis::expire('key', InTime::fromString('3 days')->inSeconds());
 */
class InTime extends DateInterval
{

    /**
     * Create an InTime from a DateInterval
     *
     * @param DateInterval $interval
     * @return InTime
     * @throws Exception
     */
    public static function fromDateInterval(\DateInterval $interval)
    {
        $emptyInTime = new self('PT0S');

        $emptyInTime->y = $interval->y;
        $emptyInTime->m = $interval->m;
        $emptyInTime->d = $interval->d;
        $emptyInTime->h = $interval->h;
        $emptyInTime->i = $interval->i;
        $emptyInTime->s = $interval->s;
        $emptyInTime->f = $interval->f;
        $emptyInTime->invert = $interval->invert;
        $emptyInTime->days = $interval->days;

        return $emptyInTime;
    }

    /**
     * Create a new InTime from a DateInterval Expression
     * E.g.
     * InTime::fromExpression('P3D');
     *
     * @see DateInterval::__construct
     * @param string $expression
     * @return InTime
     * @throws Exception
     */
    public static function fromExpression(string $expression)
    {
        return new self($expression);
    }

    /**
     * Create a new InTime from a human readable expression of time.
     * E.g.
     * InTime::fromString('3 days');
     *
     * @see DateInterval::createFromDateString()
     * @param string $dateString
     * @return InTime
     * @throws Exception
     */
    public static function fromString(string $dateString)
    {
        return self::fromDateInterval(self::createFromDateString($dateString));
    }

    /**
     * Make two different dates based on the interval we represent
     * The two parameters are passed by reference, and as DateTime objects hold
     * state, we return the two objects we create via reference.
     *
     * @param DateTime $from
     * @param DateTime $to
     * @throws Exception
     */
    private function resolve(DateTime &$from = null, DateTime &$to = null)
    {
        $from = new DateTime();
        $to = clone $from;
        $to->add($this);
    }

    /**
     * Decide if we should floor the value we have calculated,
     * based on the value of $abs
     *
     * @param $abs
     * @param $float
     * @return int
     */
    private function floor($abs, $float)
    {
        return $abs ? intval($float) : $float;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function inSeconds()
    {
        $this->resolve($from, $to);
        /** @var $from DateTime */
        /** @var $to DateTime */

        return $to->getTimestamp() - $from->getTimestamp();
    }

    /**
     * @param bool $abs
     * @return int
     * @throws Exception
     */
    public function inMinutes($abs = true)
    {
        return $this->floor($abs, $this->inSeconds() / 60);
    }

    /**
     * @param bool $abs
     * @return int
     * @throws Exception
     */
    public function inHours($abs = true)
    {
        return $this->floor($abs, $this->inMinutes(false) / 60);
    }

    /**
     * @param bool $abs
     * @return int
     * @throws Exception
     */
    public function inDays($abs = true)
    {
        return $this->floor($abs, $this->inHours(false) / 24);
    }

    /**
     * @param bool $abs
     * @return int
     * @throws Exception
     */
    public function inWeeks($abs = true)
    {
        return $this->floor($abs, $this->inDays(false) / 7);
    }

    /**
     * @param bool $abs
     * @return int
     * @throws Exception
     */
    public function inYears($abs = true)
    {
        return $this->floor($abs, $this->inDays(false) / 365);
    }

    /**
     * @param bool $abs
     * @return int
     * @throws Exception
     */
    public function inAverageMonths($abs = true)
    {
        return $this->floor($abs, $this->inYears(false) * 12);
    }

    /**
     * Get a new DateTime from now (or the DateTime string expression at $expression)
     * based on the expression.
     *
     * @return DateTime
     * @throws Exception
     */
    public function inDateTime($expression = 'now')
    {
        return (new DateTime($expression))->add($this);
    }

    /**
     * @return float|int
     * @throws Exception
     */
    public function inMonthsFromNow()
    {
        $this->resolve($from, $to);
        /** @var $from DateTime */
        /** @var $to DateTime */

        $interval = $from->diff($to);

        return ($interval->format('%y') * 12) + ($interval->format('%m'));
    }
}