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

	/**
	 * @testdox Try send a job with a invalid priority.
	 * @test
	 * @expectedException InvalidArgumentException
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runInvalidPriority()
	{
		$this->tm->run('test', '{}', 9);
	}

	/**
	 * @testdox Validate run job behavior with normal priority in background and success on submit job.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runBackgroundNormalSuccessBehavior()
	{
		$gc = $this->mock('\GearmanClient', ['doLowBackground', 'returnCode']);

		$gc->expects($this->once())
			->method('doLowBackground')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_SUCCESS));

		$this->tm->setClient($gc);
		$this->tm->runBackground('test', '{}');
	}

	/**
	 * @testdox Validate run job behavior with normal priority in background and failure on submit job.
	 * @expectedException RuntimeException
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runBackgroundNormalFailureBehavior()
	{
		$gc = $this->mock('\GearmanClient', ['doLowBackground', 'returnCode']);

		$gc->expects($this->once())
			->method('doLowBackground')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_ERRNO));

		$this->tm->setClient($gc);
		$this->tm->runBackground('test', '{}');
	}

	/**
	 * @testdox Validate run job behavior with high priority in background and success on submit job.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runBackgroundHighSuccessBehavior()
	{
		$gc = $this->mock('\GearmanClient', ['doBackground', 'returnCode']);

		$gc->expects($this->any())
			->method('doBackground')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_SUCCESS));

		$this->tm->setClient($gc);
		$this->tm->runBackground('test', '{}', TaskManager::HIGH);
	}

	/**
	 * @testdox Validate run job behavior with very high priority in background and success on submit job.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runBackgroundVeryHighSuccessBehavior()
	{
		$gc = parent::mock('\GearmanClient', ['doHighBackground', 'returnCode']);

		$gc->expects($this->once())
			->method('doHighBackground')
			->with($this->equalTo('test'), $this->equalTo('{}'));

		$gc->expects($this->once())
			->method('returnCode')
			->will($this->returnValue(GEARMAN_SUCCESS));

		$this->tm->setClient($gc);
		$this->tm->runBackground('test', '{}', TaskManager::VERY_HIGH);
	}

	/**
	 * @testdox Try send a job to run in background with a invalid priority.
	 * @expectedException InvalidArgumentException
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function runBackgroundInvalidPriority()
	{
		$this->tm->runBackground('test', '{}', 9);
	}

	/**
	 * @testdox Get status of a unknown job.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function getStatusUnknownJob()
	{
		$gc = parent::mock('\GearmanClient', ['jobStatus']);

		$gc->expects($this->once())
			->method('jobStatus')
			->with($this->equalTo('test'))
			->will($this->returnValue([false, false]));

		$this->tm->setClient($gc);
		$this->assertFalse($this->tm->jobIsRunning('test'));
	}

	/**
	 * @testdox Get status of a job that is running.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function getStatusRunningJob()
	{
		$gc = parent::mock('\GearmanClient', ['jobStatus']);

		$gc->expects($this->once())
			->method('jobStatus')
			->with($this->equalTo('test'))
			->will($this->returnValue([true, true]));

		$this->tm->setClient($gc);
		$this->assertTrue($this->tm->jobIsRunning('test'));
	}

	/**
	 * @testdox Get status of a unknown job that is running.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function getStatusUnknownJobRunnning()
	{
		$gc = parent::mock('\GearmanClient', ['jobStatus']);

		$gc->expects($this->once())
			->method('jobStatus')
			->with($this->equalTo('test'))
			->will($this->returnValue([false, true]));

		$this->tm->setClient($gc);
		$this->assertFalse($this->tm->jobIsRunning('test'));
	}

	/**
	 * @testdox Get status of a finished job.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function getStatusFinishedJob()
	{
		$gc = parent::mock('\GearmanClient', ['jobStatus']);

		$gc->expects($this->once())
			->method('jobStatus')
			->with($this->equalTo('test'))
			->will($this->returnValue([true, false]));

		$this->tm->setClient($gc);
		$this->assertFalse($this->tm->jobIsRunning('test'));
	}

	/**
	 * @testdox Get Gearman queue status.
	 * @depends getDefaultTaskManagerClientInstance
	 */
	public function getGearmanQueueStatus()
	{
		$tm = parent::mock(
			'Hunter\Gearman\TaskManager',
			['openSocket', 'getStreamContent', 'sendCommand']
		);

		$tm->expects($this->once())
			->method('openSocket')
			->will($this->returnValue(true));

		$tm->expects($this->exactly(2))
			->method('getStreamContent')
			->with($this->equalTo(true))
			->will($this->onConsecutiveCalls("FUNCTION\tTOTAL\tRUNNING\tAVAILABLE_WORKERS", '.'));

		$tm->expects($this->once())
			->method('sendCommand')
			->with($this->equalTo(true), $this->equalTo('status'));

		$this->assertEquals(
			json_encode($tm->getQueueStatus()),
			json_encode([[
				'queue' => 'FUNCTION',
				'jobs_waiting' => 'TOTAL',
				'jobs_running' => 'RUNNING',
				'available_workers' => 'AVAILABLE_WORKERS'
			]])
		);
	}
}

