<?php
/**
 * @author Sergey Buchchenko <sergey.buchchenko@gmail.com>
 * Date: 05.08.16
 * Time: 23:18
 */

namespace Lunches\Model;

use Lunches\Exception\ValidationException;

class DateRange
{
    /** @var \DateTimeImmutable  */
    protected $start;
    
    /** @var \DateTimeImmutable  */
    protected $end;

    public function __construct($start, $end)
    {
        if (!$start || !$end) {
            throw ValidationException::invalidDate('Both startDate and endDate are required');
        }
        try {
            $start = $start instanceof \DateTime ? $start : new \DateTime($start);
            $end = $end instanceof \DateTime ? $end : new \DateTime($end);
        } catch (\Exception $e) {
            throw ValidationException::invalidDate('Invalid startDate or endDate provided');
        }
        if ($start > $end) {
            throw ValidationException::invalidDate('startDate must be less than endDate');
        }
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }
}