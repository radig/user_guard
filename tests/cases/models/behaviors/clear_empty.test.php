<?php
App::import('Behavior', 'UserGuard.ClearEmpty');

class Task extends CakeTestModel
{
	public $name = 'Task';

	public $validate = array(
		'term' => array(
			'rule' => array('date'),
			'allowEmpty' => true,
			'required' => true
		),
		'title' => array(
			'rule' => array('minLength', 4),
			'allowEmpty' => false,
			'required' => true
		)
	);

	public $actsAs = array('UserGuard.ClearEmpty');
}


class ClearEmptyTest extends CakeTestCase {

	public $name = 'ClearEmpty';

	public $fixtures = array('plugin.user_guard.task');

	public $Task;

	public function startTest()
	{
		$this->Task =& ClassRegistry::init('UserGuard.Task');
	}

	public function endTest()
	{
		unset($this->Task);
	}

	public function testSaveAction()
	{
		$result = $this->Task->save(array(
			'id' => 1,
			'title' => '',
			'term' => '',
			'username' => 'Brazil'
		));

		$this->assertFalse($result);
		$this->assertEqual($this->Task->validationErrors, array('term' => 'Este campo não pode ficar em branco'));

		$this->Task->create();
		$result = $this->Task->save(array(
			'id' => 1,
			'title' => '',
			'term' => '2012-12-31',
			'username' => 'Brazil'
		));

		$expected = array(
			'Task' => array(
				'id'  => 1,
				'title' => 'teste',
				'term'  => '2012-12-31',
				'username'  => 'Brazil'
			)
		);

		$result = $this->Task->find('first',
			array('conditions' => array('username' => 'Brazil'))
		);

		$this->assertEqual($result, $expected);
	}
}