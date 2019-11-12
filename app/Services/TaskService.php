<?php

namespace App\Services;

use App\Interfaces\TaskRepositoryInterface;
use App\Interfaces\TaskServiceInterface;
use Validator;

class TaskService implements TaskServiceInterface
{
    protected $tasksRepository;

    public function __construct(TaskRepositoryInterface $tasksRepository)
    {
        $this->tasksRepository = $tasksRepository;
        $this->status = 201;
        $this->message = 'Request successful!';
        $this->data = '';
    }

    public function isValid($request) 
    {
        $validtion = Validator::make($request, [
            'parent_id' => 'nullable|exists:tasks,id',
            'user_id' =>'required',
            'title' => 'required',
            'points' => 'required|integer|between:1,10',
            'is_done' => 'required|integer|between:0,1'
        ]);
        if (!$validtion->passes()) {
            $this->status = 400;
            $this->message = $validtion->errors()->all();
            return false;
        }
        return true;
    }

    public function getResponseData()
    {
        $response['status'] = $this->status;
        $response['message'] = $this->message;
        $response['data'] = $this->data;
        return $response;
    }

    public function getUser() 
    {
        return $this->tasksRepository->getUserData();
    }

    public function getTask() 
    {
        $task = $this->tasksRepository->get();
    }
    
    public function createTask($request)
    {
        $parentOfChildIds = [];
        /*$isValidUser = $this->tasksRepository->isUserExist($request->query('user_id'));
        if (!$isValidUser) {
            $this->status = 400;
            $this->message = 'Failed! User ID is not valid.';
            return $this->getResponseData();
        }

        $request->has('parent_id') && !$request->query('parent_id') ?
            $request = $request->except('parent_id') :
            $request = $request->query();

        if (!$this->isValid($request)) {
            return $this->getResponseData();
        }*/
        /*$request = $request->query();
            dd($request);
        dd(array_search('parent_id', $request));*/

/*         $a = 1245;
        dump((int) ($a.'9'));
        dd(array_map('intval', str_split(12345))); */
        $parentsId = $this->tasksRepository->getParentsId(15);
        //Need to check if pareId is equal to 5
        //Need to check if is_done field is done or not done
        $childsIdOfAllParent = [];
        //Chceck if parents has any child with not done. If any parent has child with not done, returns false, Otherwise it will return true.
        $isParentReadyToDone = $this->tasksRepository->checkIfParentReadyToDone($parentsId);
        
        dd($parentsId);

        $this->tasksRepository->updateStatus($request);
        if ($request->has('parent_id')) {
        }
        $result = $this->tasksRepository->insert($request);
        if (!$result) {
            $this->status = 500;
            $this->message = 'Unexpected Error!';
        }
        $this->data = $result;
        return $this->getResponseData();
    }

    public function editTask($test)
    {
    }
}
