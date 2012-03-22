<?php
class Task extends CakeTestModel
{
	public $name = 'Task';

	public $validate = array(
		'term' => array(
			'rule' => array('date'),
			'allowEmpty' => false,
			'required' => true
		),
		'title' => array(
			'rule' => array('minLength', 4),
			'allowEmpty' => false,
			'required' => true
		)
	);

	public $actsAs = array('UserGuard.AutoTrim');
}


class AutoTrimBehaviorTest extends CakeTestCase {
	
	public $name = 'AutoTrim';
	
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
	
	/**
	* Testa uma ação de busca
	*/
	public function testFindAction()
	{
		$result = $this->Task->find('all',
			array('conditions' => array('username' => ' Good'))
		);
		
		$expected = array(
			array(
				'Task' => array(
					'id'  => 100,
					'title' => 'The Mayan Prophecy',
					'term'  => '2012-12-21',
					'username'  => 'Good'
				)
			)
		);
		
		$this->assertEqual($result, $expected);
		
		$result = $this->Task->find('all',
			array('conditions' => array('Task.username' => ' Good '))
		);
		
		$this->assertEqual($result, $expected);
		
		$result = $this->Task->find('all',
			array('conditions' => array('or' => array('Task.username' => ' Good  ', 'Task.id' => 100)))
		);
		
		$this->assertEqual($result, $expected);
	}
	
	/*public function testValidateAction()
	{
		
	}*/
	
	public function testSaveAction()
	{
		$this->Task->save(array(
			'id' => 1,
			'title' => 'World Cup',
			'term' => '2014-04-20',
			'username' => ' Brazil '
		));
		
		$result = $this->Task->find('first',
			array('conditions' => array('username' => 'Brazil'))
		);
		
		$expected = array(
			'Task' => array(
				'id'  => 1,
				'title' => 'World Cup',
				'term'  => '2014-04-20',
				'username'  => 'Brazil'
			)
		);
	
		$this->assertEqual($result, $expected);
	}
}