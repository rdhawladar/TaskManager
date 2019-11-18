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
        $this->status = config('constants.status.created');
        $this->message = config('constants.messages.success');
        $this->data = '';
    }

    public function isValid($data) 
    {
        $validtion = Validator::make($data, [
            'id' => 'nullable|exists:tasks,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'user_id' =>'required',
            'title' => 'required',
            'points' => 'required|integer|between:1,10',
            'is_done' => 'required|integer|between:0,1'
        ]);

        if (!$validtion->passes()) {
            $this->status = config('constants.status.bad_request');
            $this->message = $validtion->errors()->all();
            return false;
        }
        
        $isUserExist = $this->tasksRepository->isUserExist($data['user_id']);
        
        if (!$isUserExist) {
            $this->status = config('constants.status.bad_request');
            $this->message = config('constants.messages.invalid_user_id');
            return false;
        }

        if (isset($data['parent_id'])) {
            $isUserValid = $this->tasksRepository->isExists([
                        'id' => $data['parent_id'], 
                        'user_id' => $data['user_id']
                    ]);

            //False if parent user does not match
            if (!$isUserValid) {
                $this->status = config('constants.status.bad_request');
                return false;
            }
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

    public function getTaskData() 
    {
        $task = $this->tasksRepository->get();
    }
    
    public function createTask($request)
    {
        if (!$this->isValid($request->all())) {
            return $this->getResponseData();
        }
    
        if ($request->parent_id) {
            $parentsId = $this->tasksRepository->getParentsId($request->parent_id);

            //False if node exceeds depth of 5
            if (!$parentsId) {
                $this->status = config('constants.status.server_error');
                $this->message =  config('constants.messages.invalid_depth');
                return $this->getResponseData();
            }

            $request->request->add(['edge_path' => implode('_', $parentsId)]);
            $request->is_done ?
                $param = [] :
                $param = $request->only('is_done');
            $this->tasksRepository->updateParents($parentsId, $request->points, $param);
        }

        $result = $this->tasksRepository->insert($request->all());

        if (!$result) {
            $this->status = config('constants.status.server_error');
            $this->message = config('constants.messages.undefined_error');
        }

        unset($result['edge_path']);
        $this->data = $result;
        return $this->getResponseData();
    }

    public function updateTask($request, $id)
    {
        $request->request->add(['id' => $id]);
        $request->has('parent_id') && !$request->input('parent_id') ?
            $data = $request->except('parent_id') :
            $data = $request->input();
        
        if (!$this->isValid($data)) {
            return $this->getResponseData();
        } 
        
        $childData = $this->tasksRepository->getChild($id);

        if ($childData->children->count() > config('constants.numbers.zero')) {
            $this->status = config('constants.status.server_error');
            $this->message = config('constants.messages.invalid_leaf');
            return $this->getResponseData();            
        }

        $current['parent_id'] = $childData->parent_id;
        $current['is_done'] = $childData->is_done;
        $current['points'] = $childData->points;
        $current['edge_path'] = $childData->edge_path;

        $isParentSame = $this->tasksRepository->isExists($request->only(['id', 'parent_id']));

        if ($request->parent_id) {
            #Check if parent ID and ID is equal
            if ($data['parent_id'] == $id) {
                $this->status = config('constants.status.server_error');
                $this->message = config('constants.messages.parent_conflict');
                return $this->getResponseData();
            }
            $parentsId = $this->tasksRepository->getParentsId($request->parent_id);

            #False if node exceeds depth of 5
            if (!$parentsId) {
                $this->status = config('constants.status.server_error');
                $this->message = config('constants.messages.invalid_depth');            
                return $this->getResponseData();
            }

            $request->request->add(['edge_path' => implode('_', $parentsId)]);
            $request->is_done ?
                $param = [] :
                $param = $request->only('is_done');
            $points = $request->points;

            if ($isParentSame) {
                $points = $points - $current['points'];
                if ($request->is_done 
                    && !$this->tasksRepository->checkParentsAreNotDone($parentsId, $id)) {
                    $param = ['is_done' => config('constants.numbers.one')];
                }
            }

            $this->tasksRepository->updateParents($parentsId, $points, $param);
        }

        #Operations to the current parents to adjust point and status.
        if (!$isParentSame && $current['parent_id']) {
            $currentParentsId = $this->tasksRepository->getParentsId($current['parent_id']);
            
            /**
             * Check is_done status for all current parents. check when is_done 0
             * If any parents are waiting for, this leave to be is_done true,
             * then make all parents is_done to 1.
             */
            $param = [];
            if (!$current['is_done'] 
                && !$this->tasksRepository->checkParentsAreNotDone($currentParentsId, $id)) {
                $param = ['is_done' => config('constants.numbers.one')];
            }
            $this->tasksRepository->updateParents($currentParentsId, -$request->points, $param);
        }
        $result = $this->tasksRepository->update($id, $request->except('id', 'email'));

        if (!$result) {
            $this->status = config('constants.status.server_error');
            $this->message = config('constants.messages.undefined_error');
        }

        $this->data = $result;
        return $this->getResponseData();
    }
}