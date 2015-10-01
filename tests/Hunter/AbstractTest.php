<?php

namespace Tests\Hunter;

use \PHPUnit_Framework_TestCase as TestCase;
/**
 * Class AbstractTest
 * @author yourname
 */
abstract class AbstractTest extends TestCase
{
	public function mock($class, Array $methods, $disableConstructor = false)
	{
		$m = $this->getMockBuilder($class);

		if ($disableConstructor) {
			$m->disableOriginalConstructor();
		}

		$m->setMethods($methods);

		return $m->getMock();
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

	public function getReflectedMethod($class, $method)
	{
		$class = new \ReflectionClass($class);
		$method = $class->getMethod($method);
		$method->setAccessible(true);

		return $method;

	}

}
