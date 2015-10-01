<?php

namespace Tests\Hunter\Gearman;

use Hunter\Gearman\TaskManager;

/**
 * Gearman TaskManager test class.
 */
class TaskManagerTest extends \Tests\Hunter\AbstractTest {
	private $tm;

	protected function setUp()
	{
		$this->tm = new TaskManager();
	}

	protected function tearDown()
	{
		$this->tm = null;
	}

	/**
	 * @testdox Verify whether current tests are being executed on TaskManager class.
	 * @test
	 */
	public function isInstanceOfTaskManager()
	{
		$this->assertInstanceOf('Hunter\Gearman\TaskManager', $this->tm);
	}

	/**
	 * @testdox Verify whether default client instance is a instance of GearmanClient.
	 * @test
	 */
	public function getDefaultTaskManagerClientInstance()
	{
		$this->assertInstanceOf('\GearmanClient', $this->tm->getClient());
	}

	/**
	 * @testdox Try set invalid Gearman client.
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function trySetInvalidClient($data)
	{
		$this->tm->setClient($data);
	}

	/**
	 * @testdox Try set a valid Gearman client.
	 * @test
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function trySetValidClient()
	{
		$gc = new \GearmanClient();
		$this->assertInstanceOf('Hunter\Gearman\TaskManager', $this->tm->setClient($gc));
	}

	/**
	 * @testdox Validate run job behavior with normal priority and success on submit job.
	 * @test
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runNormalSuccessBehavior()
	{
		$gc = $this->mock('\GearmanClient', ['doLow', 'returnCode']);

		$gc->expects($this->once())
			->method('doLow')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_SUCCESS));

		$this->tm->setClient($gc);
		$this->tm->run('test', '{}');
	}

	/**
	 * @testdox Validate run job behavior with normal priority and failure on submit job.
	 * @test
	 * @expectedException RuntimeException
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runNormalFailureBehavior()
	{
		$gc = $this->mock('\GearmanClient', ['doLow', 'returnCode']);

		$gc->expects($this->once())
			->method('doLow')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_ERRNO));

		$this->tm->setClient($gc);
		$this->tm->run('test', '{}');
	}

	/**
	 * @testdox Validate run job behavior with high priority and success on submit job.
	 * @test
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runHighSuccessBehavior()
	{
		$gc = $this->mock('\GearmanClient', ['do', 'doNormal', 'returnCode']);

		$gc->expects($this->any())
			->method('do')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->any())
			->method('doNormal')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_SUCCESS));

		$this->tm->setClient($gc);
		$this->tm->run('test', '{}', TaskManager::HIGH);
	}

	/**
	 * @testdox Validate run job behavior with very high priority and success on submit job.
	 * @test
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runVeryHighSuccessBehavior()
	{
		$gc = parent::mock('\GearmanClient', ['doHigh', 'returnCode']);

		$gc->expects($this->once())
			->method('doHigh')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_SUCCESS));

		$this->tm->setClient($gc);
		$this->tm->run('test', '{}', TaskManager::VERY_HIGH);
	}


}

