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

		$mock->expects($this->exactly(2))
			->method('streamGetContent')
			->will($this->returnArgument(0));

		$this->assertSame(0, $mock->run('oi'), "[run] don't run correctly");
		$this->assertSame(0, $mock->getExitCode(), 'The exit code is not OK');
		$this->assertSame("1", $mock->getStdout(), 'The content of stdout is not "1"');
		$this->assertSame("2", $mock->getStderr(), 'The content of stderr is not "2"');
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

	/**
	 * @testdox Can ShellCommandRunner::runWithTimeout() set stdout, stderr and exit code correctly?
	 *
	 */
	public function getStdsRunningCommandWithTimeout()
	{
		$mock = $this->mock('Hunter\CommandRunner\ShellCommandRunner', [
			'procOpen',
			'streamGetContent',
			'procClose',
			'streamSetBlocking',
			'streamSelect',
			'fREad',
			'fEof'
		]);

		$mock->expects($this->once())
			->method('procOpen')
			->will($this->returnCallback(function ($command, &$pipes) {
				$pipes = [1 => "1", 2 => "2"];
				return true;
			}));

		$mock->expects($this->exactly(2))
			->method('streamSelect')
			->will($this->returnValue(true));

		$mock->expects($this->once())
			->method('procClose')
			->will($this->returnValue(0));

		$mock->expects($this->exactly(2))
			->method('streamSetBlocking')
			->will($this->returnValue(true));

		$mock->expects($this->any(2))
			->method('fEof')
			->will($this->returnValue(true));

		$mock->expects($this->exactly(2))
			->method('fREad')
			->will($this->returnValue('value'));

		$this->assertSame(0, $mock->runWithTimeout('oi'), "runWithTimeout don't run correctly");
		$this->assertSame(0, $mock->getExitCode(), 'The exit code is not OK');
		$this->assertSame("value", $mock->getStdout(), 'The content of stdout is not "value"');
		$this->assertSame("value", $mock->getStderr(), 'The content of stderr is not "value"');
	}

	/**
	 * @testdox Can ShellCommandRunner::runWithTimeout() respect timout?
	 * @expectedException	\RuntimeException
	 */
	public function runWithTimeoutRespectTimout()
	{
		$mock = $this->mock('Hunter\CommandRunner\ShellCommandRunner', [
			'procOpen',
			'streamGetContent',
			'streamSetBlocking',
			'streamSelect',
			'fREad',
			'fEof',
			'procTerminate'
		]);

		$mock->expects($this->once())
			->method('procOpen')
			->will($this->returnCallback(function ($command, &$pipes) {
				$pipes = [1 => "1", 2 => "2"];
				return true;
			}));

		$mock->expects($this->any())
			->method('streamSelect')
			->will($this->returnValue(true));

		$mock->expects($this->any())
			->method('streamSetBlocking')
			->will($this->returnValue(true));

		$mock->expects($this->any())
			->method('fEof')
			->will($this->returnValue(false));

		$mock->expects($this->any())
			->method('fREad')
			->will($this->returnValue('value'));

		$mock->runWithTimeout('oi', 1);
	}
}
