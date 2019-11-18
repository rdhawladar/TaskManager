@php $count = 0; @endphp
<li>{{ 'edge: '.$task['edge_path'].'|'.$task['is_done'].'('.$task['points'].')'}}</li>
@if (count($task['children_recursive']) > 0)
    <ul>
	    @foreach($task['children_recursive'] as $task)
	    	@if($task['is_done'] == 1)
	    		@php $count++; @endphp
	    	@endif
	        @include('partials.task', $task)
	    @endforeach
    </ul>
@endif

<script type="text/javascript">
	    var bool = {!! json_encode($count) !!};
	    document.getElementById("done").innerHTML = bool;
	    console.log(bool);

</script>