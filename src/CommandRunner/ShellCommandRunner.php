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
	private $descriptors = [["pipe","r"], ["pipe","w"], ["pipe","w"]];

	/**
	 * Returns the command stdout.
	 *
	 * @return String
	 */
	public function getStdout()
	{
		return $this->stdout;
	}

	/**
	 * Returns the command stderr.
	 *
	 * @return String
	 */
	public function getStderr()
	{
		return $this->stderr;
	}

	/**
	 * Get the lastest exit code
	 *
	 * @return Integer
	 */
	public function getExitCode()
	{
		return $this->exit_code;
	}

	/**
	 * Runs a shell command and returns the command exit code.
	 *
	 * @param String $command
	 * @return Integer
	 * @throw InvalidArgumentException if $command is null or empty
	 */
	public function run($command)
	{
		$this->validateCommand($command);

		$pipes = array();
		$command_handle = $this->procOpen($command, $pipes);

		$this->stdout = $this->streamGetContent($pipes[1]);
		$this->stderr = $this->streamGetContent($pipes[2]);
		$this->exit_code = $this->procClose($command_handle);

		return $this->exit_code;
	}

	/**
	 * Open a processor
	 *
	 * @codeCoverageIgnore
	 * @param String $command
	 * @param Array $pipes Empty Array
	 * @return Resource
	 */
	protected function procOpen($command, &$pipes)
	{
		return proc_open($command, $this->descriptors, $pipes);
	}

	/**
	 * Get content from resource
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 * @return String
	 * @throw \RuntimeException
	 */
	protected function streamGetContent($stream)
	{
		if (is_resource($stream)) {
			return stream_get_contents($stream);
		}

		throw new \RuntimeException(__FUNCTION__ . ": Resource is not valid");
	}

	/**
	 * Close a processor
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 */
	protected function procClose($stream)
	{
		return @proc_close($command_handle);
	}

	/**
	 * Runs a shell command waiting until a timeout and returns the command exit code or throw a RuntimeException.
	 *
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
		$this->validateCommand($command);
		$this->validateTimeout($timeout);

		$pipes = array();
		$command_handle = $this->procOpen($command, $pipes);

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

	private function validateCommand($command)
	{
		if ((is_null($command)) || (empty($command))) {
			throw new \InvalidArgumentException("Can't execute a null command.");
		}
	}

	private function validateTimeout($timeout)
	{
		if ((! is_int($timeout)) || ($timeout <= 0)) {
			throw new \InvalidArgumentException("Timeout parameter must be numeric.");
		}
	}
}
