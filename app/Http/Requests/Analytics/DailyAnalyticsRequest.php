<?php

namespace App\Http\Requests\Analytics;

use App\Modules\Analytics\ValueObjects\DateRange;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DailyAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:to'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $to = $this->query('to')
                    ? CarbonImmutable::parse($this->query('to'))->startOfDay()
                    : CarbonImmutable::today();
                $from = $this->query('from')
                    ? CarbonImmutable::parse($this->query('from'))->startOfDay()
                    : $to->subDays(30);

                if ($from->greaterThan($to)) {
                    $validator->errors()->add('from', 'The from field must be a date before or equal to to.');

                    return;
                }

                if (((int) $from->diffInDays($to) + 1) > 366) {
                    $validator->errors()->add('to', 'The selected date range may not be greater than 366 days.');
                }
            },
        ];
    }

    public function dateRange(): DateRange
    {
        return DateRange::fromNullableStrings($this->query('from'), $this->query('to'));
    }
}
