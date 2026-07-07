<?php

namespace App\Modules\Analytics\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class DateRange
{
    public function __construct(
        public CarbonImmutable $from,
        public CarbonImmutable $to,
    ) {
        if ($from->greaterThan($to)) {
            throw new InvalidArgumentException('From date must be before or equal to to date.');
        }

        if ($from->diffInDays($to) > 366) {
            throw new InvalidArgumentException('Date range cannot exceed 366 days.');
        }
    }

    public static function fromNullableStrings(?string $from, ?string $to): self
    {
        $toDate = $to ? CarbonImmutable::parse($to)->startOfDay() : CarbonImmutable::today();
        $fromDate = $from ? CarbonImmutable::parse($from)->startOfDay() : $toDate->subDays(30);

        return new self($fromDate, $toDate);
    }

    public function daysCount(): int
    {
        return (int) $this->from->diffInDays($this->to) + 1;
    }
}
