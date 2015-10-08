<?php

namespace Tests\Hunter\Gearman;

use Hunter\Gearman\WorkerManager;
use Monolog\Logger;

/**
 * Worker Manager test class.
 */
class WorkerManagerTest extends \Tests\Hunter\AbstractTest
{
	private $wm;

	protected function setUp()
	{
		$this->wm = new WorkerManager();
	}

	protected function tearDown()
	{
		$this->wm = null;
	}

	/**
	 * @testdox Verify whether current tests are being executed on WorkerManager class.
	 */
	public function isInstanceOfWorkerManager()
	{
		$this->assertInstanceOf('Hunter\Gearman\WorkerManager', $this->wm);
	}
}
