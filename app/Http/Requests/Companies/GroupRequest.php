<?php

namespace plunner\Http\Requests\Companies;

use plunner\Http\Requests\Request;

class GroupRequest extends Request
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
            'name' => 'required|max:255|unique:groups,name,'.$this->route('groups').',id,company_id,'.$this->user()->id,
            'description' => 'required|max:255',
            'planner_id' => 'required|exists:employees,id,company_id,'.$this->user()->id,
        ];
    }
}
