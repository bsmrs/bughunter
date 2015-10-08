<?php

namespace Tests\Hunter\Gearman\Entity;

use Hunter\Gearman\Entity\WorkerCfg;

/**
 * WorkerCfg test class.
 */

class WorkerCfgTest extends \Tests\Hunter\AbstractTest
{
	private $wc;

	protected function setUp()
	{
		$this->wc = new WorkerCfg();
	}

	protected function tearDown()
	{
		$this->wc = null;
	}

	/**
	 * @testdox Verify whether current tests are being executed on WorkerCfg class.
	 */
	public function isInstanceOfWorker()
	{
		$this->assertInstanceOf(
			'Hunter\Gearman\Entity\WorkerCfg',
			$this->wc
		);
	}

	/**
	 * @testdox Verify whether default ID is empty.
	 */
	public function getDefaultId()
	{
		$this->assertEmpty($this->wc->getId());
	}

	/**
	 * @testdox Verify whether default name is empty.
	 */
	public function getDefaultName()
	{
		$this->assertEmpty($this->wc->getName());
	}

	/**
	 * @testdox Verify whether is possible set a invalid name.
	 * @expectedException   Exception
	 * @expectedExceptionMessageRegExp #.*must.*#
	 * @dataProvider invalidString
	 */
	public function setInvalidtName($data)
	{
		$this->wc->setName($data);
	}

	/**
	 * @testdox Try define a valid worker name
	 */
	public function setValidtName()
	{
		$this->wc->setName('test');
		$this->assertEquals($this->wc->getName(), 'test');
	}

	/**
	 * @testdox Verify whether the default minimum is equal to one.
	 */
	public function getDefaultMin()
	{
		$this->assertEquals($this->wc->getMin(), 1);
	}

	/**
	 * @testdox Verify whether is possible set a invalid minimum.
	 * @expectedException   Exception
	 * @expectedExceptionMessageRegExp #.*must.*#
	 * @dataProvider invalidNumbers
	 */
	public function setInvalidtMinimum($data)
	{
		$this->wc->setMin($data);
	}

	/**
	 * @testdox Verify whether is possible set a valid minimum.
	 */
	public function setValidtMinimum()
	{
		$this->wc->setMin(0);
		$this->assertEquals($this->wc->getMin(), 0);
	}

	/**
	 * @testdox Verify whether is possible set a invalid maximum.
	 * @expectedException   Exception
	 * @expectedExceptionMessageRegExp #.*must.*#
	 * @dataProvider invalidNumbers
	 */
	public function setInvalidtMaximum($data)
	{
		$this->wc->setMax($data);
	}

	/**
	 * @testdox Verify whether is possible set a valid maximum.
	 */
	public function setValidtMaximum()
	{
		$this->wc->setMax(1);
		$this->assertEquals($this->wc->getMax(), 1);
	}

	/**
	 * @testdox Verify whether is possible set a invalid increment.
	 * @expectedException   Exception
	 * @expectedExceptionMessageRegExp #.*must.*#
	 * @dataProvider invalidNumbers
	 */
	public function setInvalidtIncrement($data)
	{
		$this->wc->setIncrement($data);
	}

	/**
	 * @testdox Verify whether is possible set a valid increment.
	 */
	public function setValidtIncrement()
	{
		$this->wc->setIncrement(1);
		$this->assertEquals($this->wc->getIncrement(), 1);
	}

	/**
	 * @testdox Verify whether the default maximum is equal to one.
	 */
	public function getDefaultMax()
	{
		$this->assertEquals($this->wc->getMax(), 1);
	}

	/**
	 * @testdox Verify whether the default increment is equal to one.
	 */
	public function getDefaultIncrement()
	{
		$this->assertEquals($this->wc->getIncrement(), 1);
	}
}
