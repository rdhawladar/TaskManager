<?php
namespace App\Repositories;

use App\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\QueryException;

class TaskRepository implements TaskRepositoryInterface
{

    public function getUserData()
    {
        $result = json_decode(file_get_contents('https://gitlab.iterato.lt/snippets/3/raw'), true);
        return $result['data'];
    }

    public function isUserExist($id)
    {
        foreach ($this->getUserData() as $value)
        {
            if ($value['id'] == $id) {
                return true;
            }
        }
        return false;
    }
    
    public function isExists($params)
    {
        return Task::where($params)->exists();
    }

    public function getParentsId($id)
    {
        // $child = Task::where(['is_done' => 0])->update(['is_done' => 1]);
        $parentEdge = Task::find($id, 'edge_path')->edge_path;
        if ($parentEdge) {
            $parentsId = explode('_', $parentEdge);
            if (count($parentsId) >= 5) {
                return false;
            }
            array_push($parentsId, $id);
            return $parentsId;
        }
        return [$id];
    }

    public function getChild($id) 
    {
        $data = Task::with('children')->where('id', $id)->first();
        return $data;
    }

    public function updateParents($ids, $points, $isDone = true) 
    {        
        $param = [];        
        if (!$isDone) {
            $param['is_done'] = $isDone;
        }
        return Task::whereIn('id', $ids)->increment('points', $points, $param);
    }

    public function checkParentStatus($ids) 
    {
        foreach ($ids as $id) {
            $childs = $this->getChild($id)->children->toArray();
            foreach ($childs as $child) {
                if (!$child['is_done']) {
                    return false;
                }
            }
        }
    }

    public function updateTaskStatus($parentsId, $idDone)
    {
        /* if ($is_done) {
            return null;
        } */
    }

    /* public function isAllChildsDone($id) {
        dump($id);
        $child = Task::with('childrenRecursive')->where('id', 3)->first()->toArray();
        
        $childsId = [];
    	while (true) {
            dump($child);
            dump(isset($child['children_recursive']));
    		if (!isset($child['children_recursive'])) {
                break;
    		}
            array_push($childsId, $child['id']);
    		$child = $child['children_recursive'];
        }
        dd($childsId);
    	return $childsId;
    } */

    /* public function checkIfParentReadyToDone($parentsId) {
        foreach ($parentsId as $key => $value) {
            $isDone = $this->isAllChildsDOne($value);
            dump($isDone);


        }
        dd('fomr child');

    } */
    /* public function updateStatus($data)
    {
    		// Task::whereIn('is_done', [0,1,2,3,4,5])->update(['is_done' => 1]);
    		// exit();
    	$isDone = false;
    	$parent = Task::with('parentRecursive')->where('id', 16)->first()->toArray();
    	$parentsId = [];
    	while (true) {
    		array_push($parentsId, $parent['id']);
    		if (!isset($parent['parent_recursive'])) {
    			break;
    		}
    		$parent = $parent['parent_recursive'];
    	}
    	dd($parentsId);

    	dump($parent->toArray());
    	dd($parent::where('is_done', 1)->get()->toArray());
    	$lastParent = false;
    		if ($isDone) {
    			dump($parent->parent->toArray());
    			dd($parent->toArray());
    			$parent->is_done;


    		} else {
    			while (true) {
    				dump($parent->toArray());
    				($parent->parent) ?
    					$params = [$parent->id, $parent->parent->id] :
    					$params = $parent->id;
    				Task::where('id', $params)->update(['is_done' => 0]);
    				if (!$parent->parent_id) {
    					break;
    				}
    				$parent = Task::with('parent')->where('id', $parent->parent_id)->first();
    				# code...
    			}
    			exit();
    			dump($parent);
    			dd($parent->toArray());


    		}
    	// while ($parent) {
    	// }
    	dd($parent);

        // dd($data['parent_id']);
        $id = $data['parent_id'];
		// $surveys = Task::with('childrenRecursive')->whereNull('parent_id')->get();
        // $surveys = Task::with('childrenRecursive')->where('parent_id', 3)->get();
        $surveys = Task::with('parentRecursive')->where('id', 16)->get();
        dd(collect($surveys->toArray()));
        $data = Task::with('children')->where('parent_id', 3)->get();
        dump($data->toArray());
        $ProjectTree = Task::where('id', 3)->with([
            'children' => function ($query) {
                $query->with([
                    'children' => function ($query) {
                        $query->with(['children' => function ($query) {
                            $query->with('children');
                        }]);
                    }]);
            }])->select(['id'])->get();
        dd($ProjectTree->toArray());

        dd('from update');
    } */

    public function get($id = null, $parentId = null, $userId = null)
    {
        $param = [];
        if ($id) {
            $param['id'] = $id;
        }
        if ($parentId) {
            $param['parent_id'] = $parentId;
        }
        if($userId) {
            $param['user_id'] =$userId;
        }
        dd($param);

    }

    public function insert($data)
    {
        try {
            $result = Task::create($data)->toArray();
        } catch (QueryException $e) {
            $result = false;
        }
        return $result;
    }

    public function update($data)
    {
        dd('update repo');
    }
}
