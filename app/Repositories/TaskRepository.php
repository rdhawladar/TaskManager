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
        foreach ($this->getUserData() as $value) {
            if ($value['id'] == $id) {
                return true;
            }
        }
        return false;
    }

    public function getParentIds() {
    	$isDone = false;
    	$parent = Task::with('parentRecursive')->where('id', 16)->first()->toArray();
    	$parentIds = [];
    	while (true) {
    		array_push($parentIds, $parent['id']);
    		if (!isset($parent['parent_recursive'])) {
    			break;
    		}
    		$parent = $parent['parent_recursive'];
    	}
    	return $parentIds;
    }

    public function updateStatus($data)
    {
    		// Task::whereIn('is_done', [0,1,2,3,4,5])->update(['is_done' => 1]);
    		// exit();
    	$isDone = false;
    	$parent = Task::with('parentRecursive')->where('id', 16)->first()->toArray();
    	$parentIds = [];
    	while (true) {
    		array_push($parentIds, $parent['id']);
    		if (!isset($parent['parent_recursive'])) {
    			break;
    		}
    		$parent = $parent['parent_recursive'];
    	}
    	dd($parentIds);

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
    }

    public function get($id = null)
    {

    }

    public function insert($data)
    {
        try {
            $result = Task::create($data);
        } catch (QueryException $e) {
            $result = false;
        }
        return $result;
    }

    public function update($data)
    {
        dump('update repo');
    }
}