<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		table {
  			border-collapse: collapse;
		}
		table, td {
			border: 1px solid #555;
			vertical-align: top;
		}
		li {
			list-style-type: disc;
		}
		ul, li {
			margin: 3px;
		}
	</style>
</head>
<body>
	@php $count = 0; @endphp
	<table>
		@if (count($tasks) > 0)
		    <tr>
			    @foreach ($tasks as $task)
			    	<td> 
			    		{{$users[$task['user_id']]}} ({{$count.'/'.$task['points']}})
			    		count: <span id="done"></span>
			    		<br> <br>
			        	@include('partials.task', $task)
			    	</td>
			    @endforeach
		    </tr>
		@else
		    @include('partials.no-tasks')
		@endif
	</table>
</body>
</html>