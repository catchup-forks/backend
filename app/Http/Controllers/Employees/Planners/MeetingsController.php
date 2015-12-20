<?php

namespace plunner\Http\Controllers\Employees\Planners;

use Illuminate\Http\Request;

use plunner\Http\Requests;
use plunner\Http\Requests\Employees\MeetingRequest;
use plunner\Http\Controllers\Controller;

use plunner\Meeting;
use plunner\Group;

class MeetingsController extends Controller
{
    /**
     * ExampleController constructor.
     */
    public function __construct()
    {
        config(['auth.model' => \plunner\Planner::class]);
        config(['jwt.user' => \plunner\Planner::class]);
        $this->middleware('jwt.authandrefresh:mode-en');
    }

    /**
     * Display a listing of the resource.
     *
     *  @param int $groupId
     * @return mixed
     */
    public function index($groupId)
    {
        $group = Group::findOrFail($groupId);
        $this->authorize($group);
        return $group->meetings;
        //TODO get only current meetings
    }

    /**
     * Display the specified resource.
     *
     * @param int $groupId
     * @param int $meetingId
     * @return mixed
     */
    public function show($groupId, $meetingId)
    {
        $group = Group::findOrFail($groupId);
        $this->authorize($group);
        $meeting = Meeting::findOrFail($meetingId);
        $this->authorize($meeting);
        //TODO check if the group is the same of the meeting or not?
        return $meeting;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MeetingRequest $request
     * @param int $groupId
     * @return static
     */
    public function store(MeetingRequest $request, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $this->authorize($group);
        $input = $request->all();
        $meeting = $group->meetings()->create($input);
        return $meeting;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MeetingRequest $request
     * @param int $meetingId
     * @param int $groupId
     * @return mixed
     */
    public function update(MeetingRequest $request, $groupId, $meetingId)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $this->authorize($meeting);
        $group = Group::findOrFail($groupId);
        $this->authorize($group);
        $input = $request->all();
        $meeting->update($input);
        return $meeting;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $groupId
     * @param int $meetingId
     * @return mixed
     */
    public function destroy($groupId, $meetingId)
    {
        $meeting = Meeting::findOrFail($meetingId);
        $this->authorize($meeting);
        $group = Group::findOrFail($groupId);
        $this->authorize($group);
        $meeting->delete();
        return $meeting;
    }
}