<?php
class TaskFixture extends CakeTestFixture {
	public $name = 'Task';

	public $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false, 'default' => 'teste'),
		'term' => array('type'=>'date', 'null' => true),
		'username' => array('type' => 'string', 'null' => false),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);

	public $records = array(
		array(
			'id'  => 100,
			'title' => 'The Mayan Prophecy',
			'term'  => '2012-12-21',
			'username'  => 'Good'
		)
	);
}