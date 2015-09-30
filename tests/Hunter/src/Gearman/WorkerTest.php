<?php

namespace Hunter\Gearman;
use Hunter\Gearman\Worker;
use Monolog\Logger;

class MyWorker extends Worker {
	public function logIt($msg)
	{
		parent::logIt($msg);
	}
}

class WorkerTest extends \PHPUnit_Framework_TestCase {
	private $worker;

	protected function setUp()
	{
		$this->worker = new MyWorker();
	}

	protected function tearDown()
	{
		$this->worker = null;
	}

	/**
	 * @testdox Verify whether current tests are being executed on FileManager class.
	 * @test
	 */
	public function isInstanceOfWorker()
	{
		$this->assertInstanceOf('Hunter\Gearman\Worker', $this->worker);
	}

	/**
	 * @test
	 */
	public function getDefaultGearmanWorkerInstance()
	{
		$this->assertInstanceOf('\GearmanWorker', $this->worker->getWorker());
	}

	/**
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function setWorkerInvalidParameter($data)
	{
		$this->worker->setWorker($data);
	}

	/**
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function setLoggerInvalidParameter($data)
	{
		$this->worker->setLogger($data);
	}

	/**
	 * @test
	 * @dataProvider        invalidNumbers
	 * @expectedException   \InvalidArgumentException
	 */
	public function setIoTimeoutInvalidParameter($data)
	{
		$this->worker->setIoTimeout($data);
	}

	/**
	 * @test
	 */
	public function setValidIoTimeout()
	{
		$this->worker->setIoTimeout(10000);
	}

	/**
	 * @test
	 */
	public function getDefaultLogger()
	{
		$this->assertNull($this->worker->getLogger());
	}

	/**
	 * @test
	 */
	public function setValidLogger()
	{
		$logger = new Logger("testing");
		$this->worker->setLogger($logger);
		$this->assertInstanceOf(
			'Psr\Log\LoggerInterface',
			$this->worker->getLogger()
		);
	}

	/**
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function setInvalidJobServers($data)
	{
		$this->worker->setJobServers($data);
	}

	/**
	 * @test
	 */
	public function setValidJobServer()
	{
		$this->worker->setJobServers(['192.168.10.1']);
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function setEmptyJobServer()
	{
		$this->worker->setJobServers();
	}

	/**
	 * @test
	 */
	public function logItBehavior()
	{
		$logger = $this->getMockBuilder('Monolog\Logger')
			->disableOriginalConstructor()
			->setMethods(['log'])
			->getMock();

		$logger->expects($this->once())
			->method('log')->with(
				$this->equalTo('info'),
				$this->equalTo('testing...')
			);

		$this->worker->setLogger($logger);
		$this->worker->logIt('testing...');
	}

	public function invalidNumbers()
	{
		return [
			[""],
			["1"],
			["a"],
			[null],
			[-1],
			[0]
		];
	}

	public function invalidParameter()
	{
		return [
			[null],
			[false],
			[1],
			[-1],
			[0],
			[""],
			["\t"],
			["\r"],
		];
	}
}
