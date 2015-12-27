<?php

namespace Employees;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Support\testing\ActingAs;

class PlannersMeetingsTest extends \TestCase
{
    use DatabaseTransactions, ActingAs;

    private $company, $group, $employee, $planner, $data;

    public function setUp()
    {
        parent::setUp();
        config(['auth.model' => \plunner\Planner::class]);
        config(['jwt.user' => \plunner\Planner::class]);

        $this->company = \plunner\Company::findOrFail(1);
        $this->employee = $this->company->employees()->has('groups')->with('groups')->firstOrFail();
        $this->group = $this->employee->groups->first();
        $this->planner = $this->group->planner;

        $this->data= [
            'title' => 'Test meeting',
            'description' => 'Errare humanum est!',
            'duration' => 120
        ];
    }

    private function getNonExistingMeetingId()
    {
        $test_meeting = \plunner\Meeting::orderBy('id', 'desc')->first();
        $non_existing_meeting_id = $test_meeting->id + 1;
        return $non_existing_meeting_id;
    }

    public function testCreateMeeting()
    {
        $response = $this->actingAs($this->planner)
            ->json('POST', 'employees/planners/groups/'.$this->group->id.'/meetings', $this->data);

        $response->assertResponseOk();
        $response->seeJson($this->data);
    }

    public function testIndexAllMeetings()
    {
        $response = $this->actingAs($this->planner)
            ->json('GET', 'employees/planners/groups/'.$this->group->id.'/meetings');

        $response->assertResponseOk();
        $response->seeJsonEquals($this->group->meetings->toArray());
    }

    public function testErrorIndexNoMeetings()
    {
        $response = $this->json('GET', 'employees/planners/groups/'.$this->group->id.'/meetings');

        $response->seeStatusCode(401);
    }

    public function testShowMeeting()
    {
        $this->actingAs($this->planner)
            ->json('POST', 'employees/planners/groups/'.$this->group->id.'/meetings', $this->data);
        $meeting_id = $this->group->meetings->first()->id;

        $response = $this->actingAs($this->planner)
            ->json('GET', 'employees/planners/groups/'.$this->group->id.'/meetings/'.$meeting_id);
        $response->assertResponseOk();
        $response->seeJsonEquals($this->group->meetings()->with('group')->first()->toArray());
    }

    public function testShowNonExistingMeeting()
    {
        $non_existing_meeting_id = $this->getNonExistingMeetingId();

        $response = $this->actingAs($this->planner)
            ->json('GET', 'employees/planners/groups/'.$this->group->id.'/meetings/'.$non_existing_meeting_id);
        $response->seeStatusCode(404);
    }

    public function testShowOtherGroupsMeeting()
    {
        $other_group = \plunner\Group::where('planner_id', '<>', $this->planner->id)->first();
        $other_groups_meeting_id = $other_group->meetings()->first()->id;

        $response = $this->actingAs($this->planner)
            ->json('GET', 'employees/planners/groups/'.$other_group->id.'/meetings/'.$other_groups_meeting_id);
        $response->seeStatusCode(403);
    }

    public function testPlannerDeleteMeeting()
    {
        $this->actingAs($this->planner)
            ->json('POST', 'employees/planners/groups/'.$this->group->id.'/meetings', $this->data);
        $meeting_id = $this->group->meetings()->first()->id;

        $response = $this->actingAs($this->planner)
            ->json('DELETE', 'employees/planners/groups/'.$this->group->id.'/meetings/'.$meeting_id);
        $response->assertResponseOk();
    }

    public function testEmployeeDeleteMeeting()
    {
        list($test_group, $test_employee) = $this->getNonPlannerInAGroup();

        $meeting_id = $test_group->meetings()->first()->id;

        $response = $this->actingAs($test_employee)
            ->json('DELETE', 'employees/planners/groups/'.$test_group->id.'/meetings/'.$meeting_id);
        $response->seeStatusCode(403);
    }

    private function getNonPlannerInAGroup()
    {
        $group = \plunner\Group::has('employees', '>=', '2')->firstOrFail();
        $employee = $group->employees()->where('id', '<>', $group->planner_id)->firstOrFail();
        return [$group, $employee];
    }

    public function testDeleteNonExistingMeeting()
    {
        $non_existing_meeting_id = $this->getNonExistingMeetingId();

        $response = $this->actingAs($this->planner)
            ->json('DELETE', 'employees/planners/groups/'.$this->group->id.'/meetings/'.$non_existing_meeting_id);
        $response->seeStatusCode(404);
    }

    public function testDeleteOtherGroupsMeeting()
    {
        $other_group = \plunner\Group::where('planner_id', '<>', $this->planner->id)->first();
        $other_groups_meeting_id = $other_group->meetings()->first()->id;

        $response = $this->actingAs($this->planner)
            ->json('DELETE', 'employees/planners/groups/'.$other_group->id.'/meetings/'.$other_groups_meeting_id);
        $response->seeStatusCode(403);
    }

    public function testUpdateExistingMeeting()
    {
        $this->actingAs($this->planner)
            ->json('POST', 'employees/planners/groups/'.$this->group->id.'/meetings', $this->data);
        $meeting = $this->group->meetings()->first();

        $test_data = $this->getUpdateData();

        $response = $this->actingAs($this->planner)
            ->json('PUT', 'employees/planners/groups/'.$this->group->id.'/meetings/'.$meeting->id, $test_data);
        $response->assertResponseOk();
        $response->seeJson($test_data);
    }

    public function testEmployeeUpdateExistingMeeting()
    {
        list($test_group, $test_employee) = $this->getNonPlannerInAGroup();

        $meeting = $test_group->meetings()->first();

        $test_data = $this->getUpdateData();

        $response = $this->actingAs($test_employee)
            ->json('PUT', 'employees/planners/groups/'.$test_group->id.'/meetings/'.$meeting->id, $test_data);
        $response->seeStatusCode(403);
    }

    public function testUpdateNonExistingMeeting()
    {
        $non_existing_meeting_id = $this->getNonExistingMeetingId();

        $test_data = $this->getUpdateData();

        $response = $this->actingAs($this->planner)
            ->json('PUT', 'employees/planners/groups/'.$this->group->id.'/meetings/'.$non_existing_meeting_id, $test_data);
        $response->seeStatusCode(404);
    }

    public function testUpdateOtherGroupsMeeting()
    {
        $other_group = \plunner\Group::where('planner_id', '<>', $this->planner->id)->first();
        $other_groups_meeting_id = $other_group->meetings()->first()->id;

        $test_data = $this->getUpdateData();

        $response = $this->actingAs($this->planner)
            ->json('PUT', 'employees/planners/groups/'.$other_group->id.'/meetings/'.$other_groups_meeting_id, $test_data);
        $response->seeStatusCode(403);
    }

    private function getUpdateData()
    {
        return [
            'title' => 'Different title',
            'description' => 'Different description!',
            'duration' => 60
        ];
    }
}
