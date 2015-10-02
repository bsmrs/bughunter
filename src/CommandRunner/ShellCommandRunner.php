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
	 * Close a process opened by proc_open() and return the exit code of that process
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 */
	protected function procClose($handler)
	{
		return @proc_close($handler);
	}

	/**
	 * Set stream blocked
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 * @throw \RuntimeException
	 */
	protected function streamSetBlocking($stream)
	{
		if (is_resource($stream)) {
			return stream_set_blocking($stream, 0);
		}

		throw new \RuntimeException(__FUNCTION__ . ": Resource is not valid");
	}

	/**
	 * Select a stream
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 *
	 */
	protected function streamSelect(Array &$read, &$write = null, $except = null, $tv_sec = 0, $tv_usec = 1000)
	{
		return stream_select($read, $write, $except, $tv_sec, $tv_usec);
	}

	/**
	 * Get a quantity of characters from stream
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 * @param Integer $size Quantity of characters
	 * @return String String read from stream
	 */
	protected function fRead($stream, $size)
	{
		return fread($stream, $size);
	}

	/**
	 * Tests for end-of-file on a file pointer
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 * @return Boolean
	 */
	protected function fEof($tream)
	{
		return feof($stream);
	}

	/**
	 * Kills a process opened by proc_open
	 *
	 * @codeCoverageIgnore
	 * @param Resource $stream
	 * @return Boolean
	 */
	protected function procTerminate($handler)
	{
		return proc_terminate($handler);
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

		$this->streamSetBlocking($pipes[1]);
		$this->streamSetBlocking($pipes[2]);

		$timeout += time();

		do {
			$timeleft = $timeout - time();

			$this->streamSelect($read = [$pipes[1]]);

			if ( ! empty($pipes[1])) {
				$this->stdout .= $this->fRead($pipes[1], 8192);
			}

			$this->streamSelect($read = [$pipes[2]]);

			if ( ! empty($pipes[2])) {
				$this->stderr .= $this->fRead($pipes[2], 8192);
			}

		} while (( ! $this->fEof($pipes[1])) && ( ! $this->fEof($pipes[2])) && ($timeleft >= 0));

		if ($timeleft <= 0) {
			$this->procTerminate($command_handle);
			throw new \RuntimeException("Command execution timeout on: " . $command);
		}

		$this->exit_code = $this->procClose($command_handle);

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
