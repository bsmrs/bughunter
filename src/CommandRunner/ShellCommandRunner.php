<?php

namespace Hunter\CommandRunner;

/**
 * This class run a shell command and keeps the stdout and stderr.
 */
class ShellCommandRunner
{

	private $stdout = null;
	private $stderr = null;
	private $exit_code = null;

	/**
	 * Returns the command stdout.
	 * @return String
	 */
	public function getStdout()
	{
		return $this->stdout;
	}

	/**
	 * Returns the command stderr.
	 * @return String
	 */
	public function getStderr()
	{
		return $this->stderr;
	}

	/**
	 * Runs a shell command and returns the command exit code.
	 * @param String $command
	 * @return Integer
	 * @throw InvalidArgumentException if $command is null or empty
	 */
	public function run($command)
	{

		if ((is_null($command)) || (empty($command))) {
			throw new \InvalidArgumentException("Can't execute a null command.");
		}

		$this->stdout = null;
		$this->stderr = null;
		$this->exit_code = null;
		$this->command = $command;

		$descriptors = array(
			0 => array("pipe","r"),
			1 => array("pipe","w"),
			2 => array("pipe","w")
		);

		$pipes = array();
		$command_handle = proc_open(
			$command,
			$descriptors,
			$pipes
		);

		$this->stdout = stream_get_contents($pipes[1]);
		$this->stderr = stream_get_contents($pipes[2]);
		$this->exit_code = proc_close($command_handle);

		return $this->exit_code;
	}

	/**
	 * Runs a shell command waiting until a timeout and returns the command exit code or throw a RuntimeException.
	 * @param String $command
	 * @param Integer $timeout in seconds
	 * @return Integer
	 * @throw InvalidArgumentException
	 *     - If $command is null or empty.
	 *     - If $timeout isn't numeric.
	 * @throw RuntimeException if the command doesn't execute before timeout.
	 */
	public function runWithTimeout($command, $timeout = 5)
	{

		if ((is_null($command)) || (empty($command))) {
			throw new \InvalidArgumentException("Can't execute a null command.");
		}

		if ( ! is_numeric($timeout)) {
			throw new \InvalidArgumentException("Timeout parameter must be numeric.");
		}

		$this->stdout = null;
		$this->stderr = null;
		$this->exit_code = null;

		$descriptors = array(
			0 => array("pipe","r"),
			1 => array("pipe","w"),
			2 => array("pipe","w")
		);

		$pipes = array();

		$command_handle = proc_open(
			$command,
			$descriptors,
			$pipes
		);

		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);

		$timeout += time();

		do {
			$timeleft = $timeout - time();

			stream_select(
				$read = array($pipes[1]),
				$write = null,
				$exeptions = null,
				0,
				10000
			);

			if ( ! empty($pipes[1])) {
				$this->stdout .= fread($pipes[1], 8192);
			}

			stream_select(
				$read = array($pipes[2]),
				$write = null,
				$exeptions = null,
				0,
				10000
			);

			if ( ! empty($pipes[2])) {
				$this->stderr .= fread($pipes[2], 8192);
			}
		} while (( ! feof($pipes[1])) && ( ! feof($pipes[2])) && ($timeleft >= 0));

		if ($timeleft <= 0) {
			proc_terminate($command_handle);
			throw new \RuntimeException("Command execution timeout on: " . $command);
		}

		$this->exit_code = proc_close($command_handle);

		return $this->exit_code;
	}

	public function getExitCode()
	{
		return $this->exit_code;
	}
}
