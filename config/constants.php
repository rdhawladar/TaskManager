<?php

return [
	'constant' => 'myConstant',
	'depth' => 5,
	'user_api' => 'https://gitlab.iterato.lt/snippets/3/raw',
	'numbers' => [
		'zero' => 0,
		'one' => 1,
		'two' => 2,
		'three' => 3,
		'four' => 4,
		'five' => 5,
		'six' => 6,
		'seven' => 7,
		'eight' => 8,
		'nine' => 9,
	],
	'status' => [
		'created' => 201,
		'bad_request' => 400,
		'server_error' => 500
	],
	'messages' => [
		'success' => 'Request successful!',
		'undefined_error' => 'Unexpected Error!',
		'invalid_user_id' => 'Failed! User ID is not valid.',
		'invalid_depth' => 'Depth is exceding! Please change parent ID.',
		'parent_conflict' => 'Parent ID can not be same to requested ID',
		'invalid_leaf' => 'This is not leaf. Please update leaf with leaf ID',
	]
];
