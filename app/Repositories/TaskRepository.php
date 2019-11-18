<?php
namespace App\Repositories;

use App\Interfaces\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\QueryException;
use DB;

class TaskRepository implements TaskRepositoryInterface
{

    public static function getUserData()
    {
        $result = json_decode(file_get_contents(config('constants.user_api')), true);
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
        $parentEdge = Task::find($id, 'edge_path')->edge_path;
        if ($parentEdge) {
            $parentsId = explode('_', $parentEdge);
            if (count($parentsId) >= config('constants.depth')) {
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

    public function updateParents($ids, $points, $params = [])
    {
        return Task::whereIn('id', $ids)->increment('points', $points, $params);
    }

    public function checkParentsAreNotDone($ids, $id, $isDone = 0)
    {
    	array_push($ids, $id);
    	return Task::where('edge_path', 'like', $ids[config('constants.numbers.zero')].'_%')
    		->whereNotIn('id', $ids)
    		->where('is_done', 	$isDone)
			->exists();
    }

    public function getUserName($id) {
    	foreach ($this->getUserData() as $user) {
    		if ($id == $user['id']) {
    			return $user['first_name']." ".$user['last_name'];
    		}
    	}

    }

public function doOutputList($TreeArray, $deep=0)
{
    $padding = str_repeat('  ', $deep*3);

    echo $padding . "<ul>\n";
    foreach($TreeArray as $key => $arr)
    {
        if(is_array($arr)) 
        {
                $this->doOutputList($arr, $deep+1);
        }
        else
        {
        	if ($key == 'edge_path') {

        		echo $padding . "  <li>\n";
                echo $padding .'  edge_path:  '. $TreeArray['edge_path'];
		        echo $padding . "  </li>\n";
        	}
        }
    }
    echo $padding . "</ul>\n";
}
    public function get()
    {

    	$parent = Task::with('childrenRecursive')->whereNull('parent_id')->get()->toArray();

    	// dd($parent);

		$this->doOutputList($parent);
		exit();

    	$it = new \RecursiveIteratorIterator( new \RecursiveArrayIterator($parent));

    	foreach ($it as $key => $val) {
    		dump($key . ":" . $val);
		}
    	dd($it);

    	foreach ($parent as $childrens) {
    		echo "<br>edge_path: ".$childrens['edge_path']."<br>";
    		dump(count($parent));
    		dump($parent);
    		$parent = $childrens['children_recursive'];
    		foreach ($parent as $childrens) {
    				echo "<br>*** edge_path: ".$childrens['edge_path']." "."<br>";
    				dump(count($parent));
    				dump($parent);
    				$parent = $childrens['children_recursive'];
    				foreach ($parent as $childrens) {
    					echo "<br>****** edge_path: ".$childrens['edge_path']." "."<br>";
    					dump(count($parent));
    					dump($parent);
    					$parent = $childrens['children_recursive'];
	    				foreach ($parent as $childrens) {
	    					echo "<br>********* edge_path: ".$childrens['edge_path']." "."<br>";
	    					dump(count($parent));
	    					dump($parent);
	    					$parent = $childrens['children_recursive'];
		    				foreach ($parent as $childrens) {
		    					echo "<br>************ edge_path: ".$childrens['edge_path']." "."<br>";
		    					dump(count($parent));
		    					dump($parent);
		    					$parent = $childrens['children_recursive'];
			    				foreach ($parent as $childrens) {
			    					echo "<br>************ edge_path: ".$childrens['edge_path']." "."<br>";
			    					dump(count($parent));
			    					dump($parent);
			    					$parent = $childrens['children_recursive'];
			    				}
		    				}
	    				}
    				}
    		}
    	}
    	exit();
    	$parent = $parent[0]->toArray();
    	while (true) {
    		if(!isset($parent['children_recursive']))
    			break;
    		echo "<br>:".$parent['edge_path'].":<br>";
    		$parent = $parent['children_recursive'];
    		foreach ($parent as $children) {
    			dump($children);
    		}


    		# code...
    	}


    	$data = Task::with('children')->whereNull('parent_id')->get()->toArray();
    	dd($data);
    	$data = Task::where('edge_path', 'like', '1_%')->orderBy('parent_id', 'asc')->get();

    	dump($data->where('id', 13)->toArray());
    	dd($data->toArray());

    	$tasks = Task::with('childrenRecursive')->where('id', 1)->get()->toArray();
    	// $tasks = Task::with('parentRecursive')->where('id', 16)->get()->toArray();

    	    $ProjectTree = Task::where('id', 3)->with([
            'children' => function ($query) {
                $query->with([
                    'children' => function ($query) {
                        $query->with(['children' => function ($query) {
                            $query->with('children');
                        }]);
                    }]);
            }])->select(['id'])->get();
    	dd($tasks);
    	foreach ($tasks as $key => $parent) {
    		if($key > 0)
    			break;
    		echo "<br>user: .".$parent['user_id']." : ".$this->getUserName($parent['user_id'])."<br>";
    		dump($parent);
    		while (true) {
    			if (!isset($parent['children_recursive'])) {
    				break;
    			}

    			echo "<br>*".$parent['title']." <br>";
    			$parent = $parent['children_recursive'];
    			dump($parent);
    			// dump($parent['title']);
    		}
    	}
    	dd('from ');

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

    public function update($id, $data)
    {
        try {
        	$result = DB::table('tasks')->where('id', $id)->update($data);
        	$result = DB::table('tasks')->select('id', 'parent_id', 'user_id', 'title', 'points', 'created_at', 'updated_at')->where('id', $id)->first();
        } catch (QueryException $e) {
            $result = false;
        }
        return $result;
    }
}
