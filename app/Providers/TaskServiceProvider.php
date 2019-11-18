<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TaskRepository;
use App\Interfaces\TaskRepositoryInterface;
use Illuminate\Support\Facades\View;
use App\Models\Task;

class TaskServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            TaskRepositoryInterface::class, 
            TaskRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('taskboard', function ($view) {
            $users = TaskRepository::getUserData();
            foreach ($users as $user) {
                $userMap[$user['id']] = $user['first_name'].' '.$user['last_name'];
            }
            $tasks = Task::with('childrenRecursive')->whereNull('parent_id')->get()->toArray();
            foreach ($tasks as $key => $value) {
                $data = Task::where('edge_path', 'like', $value['id'].'_%')->where('is_done', 1)->get()->toArray();
                dump($data);
                // $value['test'] = 'haha';
                array_push($tasks[$key], ['count' => $key]);
                # code...
            }
            dd($tasks);
            $view->with('tasks', $tasks);
            $view->with('users', $userMap);
            // $view->with('count', 0);

        });
    }
}
