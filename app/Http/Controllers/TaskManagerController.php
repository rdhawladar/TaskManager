<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Models\Task;

class TaskManagerController extends Controller
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $tasks;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TaskService $tasks)
    {
        $this->tasks = $tasks;
    }


    public function getCreate(Request $request)
    {
        $response = $this->tasks->createTask($request);
        return response()->json(
                    [
                        'data' => $response['data'],
                        'message' => $response['message']
                    ], 
                    $response['status'],
                    [],
                    JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
                );
    }

    public function getUpdate(Request $request) {
        dd('update');
    }
}
