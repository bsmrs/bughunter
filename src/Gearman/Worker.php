<?php

namespace Hunter\Gearman;

use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

class InvalidWorkerException extends \Exception
{
}

/**
 * A abstract class for a gearman worker.
 */
abstract class Worker {

	/**
	 * @var Object $logger is a instance of Logger.
	 */
	protected $logger;

	/**
	 * @var String stores the class name.
	 */
	protected $whoiam;

	/**
	 * @var Array $registered_functions stores all methods that we need register
	 * for a worker.
	 */
	private $registered_functions = array();

	/**
	 * @var Array $job_servers is a list with all job servers address.
	 */
	private $job_servers;

	/**
	 * @var Object $worker is a instance of GearmanWorker class.
	 */
	private $worker;

	/**
	 * @var Integer $io_timeout is the maximum time spent for a worker with IO.
	 */
	private $io_timeout = 10000; // in milliseconds

	public function __construct(\GearmanWorker $worker = null)
	{
		$this->whoiam = get_class($this);
		$this->setWorker(($worker) ?: new \GearmanWorker());
	}

	/**
	 * The setWorker method is used to overwrite original instance of the
	 * GearmanWorker.
	 * @param GearmanWorker $worker
	 * @return $this
	 */
	public function setWorker(\GearmanWorker $worker)
	{
		unset($this->worker);
		$this->worker = $worker;
		return $this;
	}

	/**
	 * This method returns a copy of the local GearmanWorker instance.
	 * @return GearmanWorker
	 */
	public function getWorker()
	{
		return $this->worker;
	}

	/**
	 * This method is used to define the maximum time spent for a worker with IO
	 * in milliseconds
	 * @param Integer $timeout
	 *     Is the maximum time spent for a worker with IO in milliseconds.
	 * @throw InvalidArgumentException
	 *     If the timeout isn't numeric.
	 * @return $this
	 */
	public function setIoTimeout($timeout)
	{
		if (( ! is_int($timeout)) || ($timeout <= 0)) {
			throw new \InvalidArgumentException(
				"Trying define a invalid worker IO timeout."
			);
		}

		$this->io_timeout = $timeout;

		return $this;
	}

	/**
	 * The setLogger can define a Logger that will be used to write messages.
	 * @param Logger $logger
	 * @return $this;
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * This method returns an Logger object or null if a logger wasn't defined.
	 * @return Object Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	/**
	 * This method writes messages in a log file.
	 * @param String $msg A message to write.
	 * @return $this;
	 */
	protected function logIt($msg)
	{
		if ( ! is_null($this->logger)) {
			$this->logger->log(
				LogLevel::INFO,
				$msg
			);
		}

		return $this;
	}

	/**
	 * This method is used to register all queues and methods for a worker. The
	 * registered method is called with a GearmanJob parameter.
	 * @param Array $method_names
	 *     Is the name of the all methods and queues that we need to register
	 * for this worker. The method that will be called when a task get in queue.
	 * @throw InvalidArgumentException
	 *     If the $method_names is empty.
	 */
	protected function registerMethods(Array $method_names)
	{
		if (empty($method_names)) {
			throw new \InvalidArgumentException(
				"There must be at least one method to be registered."
			);
		}

		$this->registered_functions = $functions;

		foreach ($functions as $key => $value) {
			$this->worker->addFunction($key, array($this, $value));
			$this->logIt("Registering function: " . $key);
		}
	}

	/**
	 * This method is called to configure worker's servers and check if there is
	 * registered methods.
	 * @throw InvalidWorkerException
	 *     If the worker doesn't have registered methods.
	 *     If the worker doesn't have job servers.
	 */
	private function preStartWorker()
	{
		$this->logIt("Starting worker...");

		if (empty($this->registered_functions)) {
			$this->logIt(
				"Trying start a worker without a registered function."
			);
			throw new \InvalidWorkerException(
				"Trying start a worker without a registered function."
			);
		}

		$this->worker->setTimeout($this->io_timeout);

		if (empty($this->job_servers)) {
			$this->logIt(
				"Trying start a worker without a job server."
			);
			throw new \InvalidWorkerException(
				"Trying start a worker without a job server."
			);
		}

		foreach ($this->job_servers as $server) {
			$this->worker->addServer($server);

			$msg = sprintf(
				"Adding server: %s",
				$server
			);

			$this->logIt($msg);
		}

		$this->logIt("Worker ready to job!");
	}

	/**
	 * This method must be called to start the worker execution.
	 */
	public function run()
	{
		$this->preStartWorker();

		while (true) {
			try {
				$this->worker->work();
			} catch (\Exception $e) {
				$this->logIt($e->getMessage());
				$error = $this->worker->error();

				if ( ! empty($error)) {
					$this->logIt($error);
				}

				exit(0);
			}
		}
	}

	/**
	 * This method is used to define a list of job servers.
	 * @param Array $servers
	 *     Is a list of the job servers address where this worker need runs.
	 * @throw InvalidArgumentException
	 *     If the list of the servers address is empty.
	 * @return $this
	 */
	public function setJobServers(Array $servers = array())
	{
		if (empty($servers)) {
			$this->logIt("Invalid job servers list.");
			throw new \InvalidArgumentException("Invalid job servers list.");
		}

		$this->job_servers = $servers;

		return $this;
	}
}

