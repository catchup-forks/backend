<?php

namespace plunner\Http\Controllers\Employees\Calendars;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request; //TODO fix this
use it\thecsea\caldav_client_adapter\simple_caldav_client\SimpleCaldavAdapter;
use plunner\Calendar;
use plunner\Http\Controllers\Controller;
use plunner\Http\Requests\Employees\CalendarRequest;


class CalendarsController extends Controller
{
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
        //
        /**
         * @var $employee Employee
         */
        $employee = \Auth::user();
        return $employee->calendars;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CalendarRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CalendarRequest $request)
    {
        //
        $employee = \Auth::user();
        $input = $request->all();
        $calendar = $employee->calendars()->create($input);
        return $calendar;
    }

    /**
     * Store a newly created resource in storage with caldav.
     *
     * @param  CalendarRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCaldav(CalendarRequest $request)
    {
        //
        $employee = \Auth::user();
        $input = $request->all();
        $calendar = $employee->calendars()->create($input);
        $calendar->caldav()->create($input);
        //TODO test
        //TODO validator
        //TODO return caldav info
        //TODO supprot function to create simple calendar
        return $calendar;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $calendar = Calendar::with('caldav')->findOrFail($id);
        $this->authorize($calendar);
        return $calendar;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CalendarRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CalendarRequest $request, $id)
    {
        //
        $calendar = Calendar::findOrFail($id);
        $this->authorize($calendar);
        $input = $request->all();
        $calendar->update($input);
        //TODO test
        //TODO validator
        $calendar->caldav()->update($input);
        return $calendar;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $calendar = Calendar::findOrFail($id);
        $this->authorize($calendar);
        $calendar->delete();
        return $calendar;
    }

    /**
     * Return a list of calendars name of a specif caldav calendar
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCalendars(Request $request)
    {
        //TODO VALIDATE
        //TODO test this
        try {
            $caldavClient = new SimpleCaldavAdapter();
            $caldavClient->connect($request->input('url'), $request->input('username'), $request->input('password'));
            $calendars = $caldavClient->findCalendars();
            return array_keys($calendars);
        }catch (\it\thecsea\caldav_client_adapter\CaldavException $e)
        {
            return Response::json(['error' => $e->getMessage()],422);
        }
    }
}