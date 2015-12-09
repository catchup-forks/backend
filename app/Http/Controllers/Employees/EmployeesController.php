<?php

namespace plunner\Http\Controllers\Employees;

use plunner\Http\Controllers\Controller;
use plunner\Http\Requests;
use plunner\Employee;

class EmployeesController extends Controller
{
    /**
     * @var plunner/Employee
     */
    private $user;

    /**
     * ExampleController constructor.
     */
    public function __construct()
    {
        config(['auth.model' => \plunner\Employee::class]);
        config(['jwt.user' => \plunner\Employee::class]);
        $this->middleware('jwt.authandrefresh:mode-en');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employee = \Auth::user();
        $company = $employee->company;
        return $company->employees;
    }
}
