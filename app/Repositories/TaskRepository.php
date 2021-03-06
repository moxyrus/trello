<?php


namespace App\Repositories;


use App\Column;
use App\Events\TaskCreated;
use App\Task;
use Illuminate\Support\Facades\DB;

class TaskRepository
{

    public function store(array $data): Task
    {
        $data['archived'] = false;

        $this->setSort($data);

        $task = Task::create($data);

        return $task;
    }


    /**
     * sort tasks inside column
     *
     * @param Column $column
     * @param array $set
     */
    public function sort(Column $column, array $set): void
    {
        $case = collect($set['set'])->map(function ($id, $index) {
            return sprintf('WHEN id = %d then %d', $id, $index);
        })->implode(' ');
        $case = sprintf('(case %s end)', $case);

        DB::table((new Task())->getTable())
            ->where('dashboard_id', $column->dashboard_id)
            ->whereIn('id', $set['set'])
            ->update([
                'sort'      => DB::raw($case),
                'column_id' => $column->id
            ]);
    }


    /**
     * get maximum sort field for specific dashboard and set it up for new Column
     *
     * @param array $data
     */
    private function setSort(array &$data)
    {
        $data['sort'] = Task::where('column_id', $data['column_id'])->max('sort');
    }
}
