<?php

namespace LambdaDigamma\MMEvents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlaceEvent extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'place_id' => 'nullable|exists:'.config('mm-events.places_table', 'mm_places').',id',
        ];
    }
}
