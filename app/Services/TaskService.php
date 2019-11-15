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
            $this->status = 400;
            $this->message = $validtion->errors()->all();
            return false;
        }
        
        $isUserExist = $this->tasksRepository->isUserExist($data['user_id']);
        
        if (!$isUserExist) {
            $this->status = 400;
            $this->message = 'Failed! User ID is not valid.';
            return false;
        }

        if (isset($data['parent_id'])) {
            $isUserValid = $this->tasksRepository->isExists([
                        'id' => $data['parent_id'], 
                        'user_id' => $data['user_id']
                    ]);

            //False if parent user does not match
/*            if (!$isUserValid) {
                $this->status = 400;
                return false;
            }*/
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
        $request->has('parent_id') && !$request->input('parent_id') ?
            $data = $request->except('parent_id') :
            $data = $request->input();
        
        if (!$this->isValid($data)) {
            return $this->getResponseData();
        }
    
        if (isset($data['parent_id'])) {
            $parentsId = $this->tasksRepository->getParentsId($data['parent_id']);

            //False if node exceeds depth of 5
            if (!$parentsId) {
                $this->status = 500;
                $this->message = 'Depth is exceding! Please change parent ID.';            
                return $this->getResponseData();
            }

            $data['edge_path'] = implode('_', $parentsId);
            $this->tasksRepository->updateParents($parentsId, $data['points'], $data['is_done']);
        }

        $result = $this->tasksRepository->insert($data);

        if (!$result) {
            $this->status = 500;
            $this->message = 'Unexpected Error!';
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
        
        /*if (!$this->isValid($data)) {
            return $this->getResponseData();
        } */
        
        $childData = $this->tasksRepository->getChild($id);

        if ($childData->children->count() > 0) {
            $this->status = 500;
            $this->message = 'This is not leaf. Please update leaf with leaf ID.';
            return $this->getResponseData();            
        }

        $current['parent_id'] = $childData->parent_id;
        $current['is_done'] = $childData->is_done;
        $current['edge_path'] = $childData->edge_path;

        $isParentSame = $this->tasksRepository->isExists($request->only(['id', 'parent_id']));

        if (!$isParentSame) {
            if (isset($data['parent_id'])) {
                //Check if parent ID and ID is equal
                if ($data['parent_id'] == $id) {
                    $this->status = 500;
                    $this->message = 'Parent ID can not be same to ID';
                    return $this->getResponseData();
                }
                $parentsId = $this->tasksRepository->getParentsId($data['parent_id']);

                //False if node exceeds depth of 5
                if (!$parentsId) {
                    $this->status = 500;
                    $this->message = 'Depth is exceding! Please change parent ID.';            
                    return $this->getResponseData();
                }
                $this->tasksRepository->updateParents($parentsId, $data['points'], $data['is_done']);
                
            } else {

            }
                $currentParentsId = $this->tasksRepository->getParentsId($current['parent_id']);
                dd($currentParentsId);
                
                $data['edge_path'] = implode('_', $parentsId);

                dd($parentsId);
        }
        $result = $this->tasksRepository->update($data);
    }
}