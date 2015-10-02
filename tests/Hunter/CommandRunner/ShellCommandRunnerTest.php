<?php

namespace Tests\Hunter\CommandRunner;

use Hunter\CommandRunner\ShellCommandRunner as Shell;

/**
 * Class ShellCommandRunnerTest
 */
class ShellCommandRunnerTest extends \Tests\Hunter\AbstractTest
{
	protected $shell;

	protected function setUp()
	{
		$this->shell = new Shell();
	}

	protected function tearDown()
	{
		$this->shell = null;
	}

	/**
	 * @testdox Verify whether $shell is an instance of Hunter\CommandRunner\ShellCommandRunner
	 */
	public function shellInstanceOf()
	{
		$this->assertInstanceOf(
			'Hunter\CommandRunner\ShellCommandRunner',
			$this->shell,
			'The attribute shell is not a instance of ShellCommandRunner'
		);
	}

	/**
	 * @testdox ShellCommandRunner::run() I must not accept either null or empty parameter
	 * @expectedException	\InvalidArgumentException
	 * @dataProvider	runInvalidParameterProvide
	 */
	public function getExceptionToNullAndEmptyParameter($data)
	{
		$this->shell->run($data);
	}

	public function runInvalidParameterProvide()
	{
		return [
			[null],
			['']
		];
	}

	/**
	 * @testdox Can ShellCommandRunner::run() set stdout, stderr and exit code correctly?
	 *
	 */
	public function getStdsRunningCommand()
	{
		$mock = $this->mock('Hunter\CommandRunner\ShellCommandRunner', [
			'procOpen',
			'streamGetContent',
			'procClose'
		]);

		$mock->expects($this->once())
			->method('procOpen')
			->will($this->returnCallback(function ($command, &$pipes) {
				$pipes = [1 => "1", 2 => "2"];
				return true;
			}));

		$mock->expects($this->once())
			->method('procClose')
			->will($this->returnValue(0));

		$mock->expects($this->any())
			->method('streamGetContent')
			->will($this->returnArgument(0));

		$this->assertSame(0, $mock->run('oi'));
		$this->assertSame(0, $mock->getExitCode());
		$this->assertSame("1", $mock->getStdout());
		$this->assertSame("2", $mock->getStderr());
	}

	/**
	 * @testdox ShellCommandRunner::runWithTimeout() I must not accept either null or empty parameter
	 * @expectedException	\InvalidArgumentException
	 * @dataProvider	runInvalidParameterProvide
	 */
	public function runWithTimeoutGetExceptionToNullAndEmptyParameter($data)
	{
		$this->shell->runWithTimeout($data);
	}

	/**
	 * @testdox ShellCommandRunner::runWithTimeout() I must not accept either null or empty parameter
	 * @expectedException	\InvalidArgumentException
	 * @dataProvider	invalidNumbers
	 */
	public function runWithTimeoutGetExceptionToInvalidTimeout($data)
	{
		$this->shell->runWithTimeout('oi', $data);
	}
}
