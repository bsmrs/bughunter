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

}
