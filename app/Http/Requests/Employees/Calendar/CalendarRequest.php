<?php

namespace plunner\Http\Requests\Employees\Calendar;

use plunner\Http\Requests\Request;

class CalendarRequest extends Request
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
            'name' => 'required|max:255',
            'enabled' => 'required|boolean',
        ];
    }
}
