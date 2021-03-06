<?php

namespace Tests\Feature;

use App\Column;
use App\Dashboard;
use App\DashboardUser;
use App\Task;
use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{

    use DatabaseMigrations;

    private $user;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->seed();

        $this->user = User::find(2);
    }


    public function testCreateSuccessfulTask()
    {
        $dashboard = Dashboard::where('owner_id', $this->user->id)->first();
        $column = Column::where('dashboard_id', $dashboard->id)->first();

        $form_data = [
            'column_id'    => $column->id,
            'title'        => 'some text',
            'description'  => 'some text',
            'dashboard_id' => $dashboard->id
        ];

        $this->actingAs($this->user, 'api')
            ->postJson(route('tasks.store'), $form_data)
            ->assertCreated()
            ->assertSee($form_data['title']);
    }


    public function testCreateUnsuccessfulTask()
    {
        $dashboard = Dashboard::where('owner_id', $this->user->id)->first();
        $column = Column::where('dashboard_id', $dashboard->id)->first();

        $form_data = [
            'description'  => 'some text',
            'dashboard_id' => $dashboard->id
        ];

        $this->actingAs($this->user, 'api')
            ->postJson(route('tasks.store'), $form_data)
            ->assertJsonValidationErrors(['title', 'column_id']);
    }


    public function testUserCanUpdateTask()
    {
        $dashboard = Dashboard::where('owner_id', $this->user->id)->firstOrFail();
        $task = Task::where('dashboard_id', $dashboard->id)->firstOrFail();

        $form_data = $task->toArray();
        $form_data['title'] = 'some text';

        $query_params = ['task' => $task->id];

        $this->actingAs($this->user, 'api')
            ->postJson(route('tasks.update', $query_params), $form_data)
            ->assertStatus(200)
            ->assertSee($form_data['title']);
    }


    public function testUserCanNotUpdateTask()
    {
        $task = Task::whereNotIn('dashboard_id', function ($query) {
            return $this->getDashboardIDForUser($query);
        })->first();

        $form_data = $task->toArray();
        $form_data['title'] = 'some text';

        $query_params = ['task' => $task->id];

        $this->actingAs($this->user, 'api')
            ->postJson(route('tasks.update', $query_params), $form_data)
            ->assertForbidden();
    }


    public function testUserCanDeleteTask()
    {
        $task = Task::whereIn('dashboard_id', function ($query) {
            return $this->getDashboardIDForUser($query);
        })->first();

        $query_params = ['task' => $task->id];

        $this->actingAs($this->user, 'api')
            ->deleteJson(route('tasks.destroy', $query_params))
            ->assertStatus(200);
    }


    public function testUserCanNotDeleteTask()
    {
        $task = Task::whereNotIn('dashboard_id', function ($query) {
            return $this->getDashboardIDForUser($query);
        })->first();

        $query_params = ['task' => $task->id];

        $this->actingAs($this->user, 'api')
            ->deleteJson(route('tasks.destroy', $query_params))
            ->assertForbidden();
    }


    public function testUserCanSortTasks()
    {
        $this->withoutExceptionHandling();

        $column = Column::whereIn('dashboard_id', function ($query) {
            return $this->getDashboardIDForUser($query);
        })->first();

        $query_params = ['column' => $column->id];
        $form_data = [
            'set' => Task::where('column_id', $column->id)->get()->pluck('id')->toArray()
        ];

        $this->actingAs($this->user, 'api')
            ->postJson(route('tasks.sort', $query_params), $form_data)
            ->assertStatus(200);

        Task::where('column_id', $column->id)
            ->each(function ($task) use ($form_data) {
                $expected = array_search($task->id, $form_data['set']);

                $this->assertEquals($expected, $task->sort);
            });
    }


    /**
     * @param Builder $query
     *
     * @return Builder
     */
    private function getDashboardIDForUser(Builder $query): Builder
    {
        return $query->select('dashboard_id')
            ->from((new DashboardUser)->getTable())
            ->where('user_id', $this->user->id);
    }
}
