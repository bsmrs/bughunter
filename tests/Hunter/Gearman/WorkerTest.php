<?php

namespace Tests\Hunter\Gearman;
use Hunter\Gearman\Worker;
use Monolog\Logger;

/**
 * Worker test class.
 */

class WorkerTest extends \Tests\Hunter\AbstractTest {
	private $worker;

	protected function setUp()
	{
		$this->worker = new Worker();
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
	 * @testdox Verify whether default worker instance is a instance of GearmanWorker.
	 * @test
	 */
	public function getDefaultGearmanWorkerInstance()
	{
		$this->assertInstanceOf('\GearmanWorker', $this->worker->getWorker());
	}

	/**
	 * @testdox Try invalid workers.
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function setWorkerInvalidParameter($data)
	{
		$this->worker->setWorker($data);
	}

	/**
	 * @testdox Try set invalid Loggers.
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function setLoggerInvalidParameter($data)
	{
		$this->worker->setLogger($data);
	}

	/**
	 * @testdox Try set invalid IO timeout.
	 * @test
	 * @dataProvider        invalidNumbers
	 * @expectedException   \InvalidArgumentException
	 */
	public function setIoTimeoutInvalidParameter($data)
	{
		$this->worker->setIoTimeout($data);
	}

	/**
	 * @testdox Try set a valid IO timeout.
	 * @test
	 */
	public function setValidIoTimeout()
	{
		$this->worker->setIoTimeout(10000);
	}

	/**
	 * @testdox Verify whether default Logger is null.
	 * @test
	 */
	public function getDefaultLogger()
	{
		$this->assertNull($this->worker->getLogger());
	}

	/**
	 * @testdox Try set a valid Logger.
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
	 * @testdox Try set invalid job servers.
	 * @test
	 * @dataProvider		invalidParameter
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function setInvalidJobServers($data)
	{
		$this->worker->setJobServers($data);
	}

	/**
	 * @testdox Try set a valid job server.
	 * @test
	 */
	public function setValidJobServer()
	{
		$this->worker->setJobServers(['192.168.10.1']);
	}

	/**
	 * @testdox Try set a empty job server.
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function setEmptyJobServer()
	{
		$this->worker->setJobServers();
	}

	/**
	 * @testdox Validate logIt bahavior.
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

	/**
	 * @testdox Validate register method behavior.
	 * @test
	 */
	public function registerMethodBehavior()
	{
		$gw = $this->mock('\GearmanWorker', ['addFunction']);

		$gw->expects($this->once())
			->method('addFunction')->with(
				$this->equalTo('test'),
				$this->anything()
			);

		$this->worker->setWorker($gw);
		$this->worker->registerMethods(["test" => "test"]);
	}

	/**
	 * @testdox Validate register method behavior with two methods to register.
	 * @test
	 */
	public function registerMethodBehaviorTwice()
	{
		$gw = $this->mock('\GearmanWorker', ['addFunction']);

		$gw->expects($this->exactly(2))
			->method('addFunction')->withConsecutive(
				$this->equalTo(['test', 'test2']),
				$this->anything()
			);

		$this->worker->setWorker($gw);
		$this->worker->registerMethods(
			[
				"test" => "test",
				"test2" => "test2"
			]
		);
	}

	/**
	 * @testdox Try start a worker without registered methods.
	 * @test
	 * @expectedException Hunter\Gearman\InvalidWorkerException
	 */
	public function tryStartWorkerWithoutRegisteredMethod()
	{
		$this->getReflectedMethod('Hunter\Gearman\Worker', 'preStartWorker')
			->invoke($this->worker);
	}

	/**
	 * @testdox Try start a worker without a job server.
	 * @test
	 * @expectedException Hunter\Gearman\InvalidWorkerException
	 */
	public function tryStartWorkerWithoutJobServer()
	{
		$gw = $this->mock('\GearmanWorker', ['addFunction']);

		$gw->expects($this->once())
			->method('addFunction')
			->will($this->returnValue(true));

		$this->worker->setWorker($gw);
		$this->worker->registerMethods(['test' => 'test']);
		$this->getReflectedMethod('Hunter\Gearman\Worker', 'preStartWorker')
			->invoke($this->worker);
	}

	/**
	 * @testdox Try start a valid worker.
	 * @test
	 */
	public function tryPreStartValidWorker()
	{
		$gw = $this->mock('\GearmanWorker', ['addFunction', 'addServer']);

		$gw->expects($this->once())
			->method('addFunction')
			->will($this->returnValue(true));

		$gw->expects($this->once())
			->method('addServer')
			->with($this->equalTo('127.0.0.1'));

		$this->worker->setWorker($gw);

		$this->worker->registerMethods(['test' => 'test']);
		$this->worker->setJobServers(['127.0.0.1']);
		$this->getReflectedMethod('Hunter\Gearman\Worker', 'preStartWorker')
			->invoke($this->worker);
	}

	/**
	 * @testdox Validate behavior trying register a invalid method.
	 * @test
	 * @expectedException InvalidArgumentException
	 */
	public function registerInvalidMethodBehavior()
	{
		$this->worker->registerMethods([]);
	}
}
