<?php

use Illuminate\Database\Seeder;
use App\Models\Task;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	
        $points = [0,3,6,9];
        $task = [];
        for ($i = 1; $i <= 3; $i++) {
            $task['user_id'] = rand(1,5);
            $task['title'] = "Task_$i";
            $task['points'] = $points[$i];
            $task['is_done'] = 0;
            $task['edge_path'] = $i;
            Task::create($task);
            
        }
        
        for ($i = 1; $i <= 3; $i++) {
            for ($j=1; $j<=3; $j++) {
                $task['parent_id'] = $i;
                $task['user_id'] = rand(1,5);
                $task['title'] = "Task_".$i."_$j";
                $task['points'] = $points[$i]/3;
                $task['is_done'] = 0;
                $task['edge_path'] = $i.$j;
                Task::create($task);
            }
        }
        
        for ($i = 3; $i <= 3; $i++) {
            for ($j=1; $j<=3; $j++) {
                $task['parent_id'] = 10;
                $task['user_id'] = rand(1,5);
                $task['title'] = "Task_".$i."_1_$j";
                $task['points'] = 1;
                $task['is_done'] = 0;
                $task['edge_path'] = $i.'1'.$j;
                Task::create($task);
            }
        }
    }
}
