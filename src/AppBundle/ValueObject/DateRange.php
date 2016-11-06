<?php

namespace AppBundle\ValueObject;

use AppBundle\Exception\ValidationException;

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
            $start = $start instanceof \DateTimeImmutable ? $start : new \DateTimeImmutable($start);
            $end = $end instanceof \DateTimeImmutable ? $end : new \DateTimeImmutable($end);
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