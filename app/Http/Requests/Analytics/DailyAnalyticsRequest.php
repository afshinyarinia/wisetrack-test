<?php

namespace App\Http\Requests\Analytics;

use App\Modules\Analytics\ValueObjects\DateRange;
use Illuminate\Foundation\Http\FormRequest;

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

    public function dateRange(): DateRange
    {
        return DateRange::fromNullableStrings($this->query('from'), $this->query('to'));
    }
}
