<?php

/**
 * This class implements a gearman worker manager.
 */

namespace Hunter\Gearman;

use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

/**
 * Exception thrown when a worker doesn't stop before the timeout.
 */
class KillWorkersTimeout extends \Exception
{
}

/**
 * Exception thrown when an action require that the worker is stoped, but it's
 */
class WorkersRunningYet extends \Exception
{
}

/**
 * Gearman utilities class to manipulate workers
 */
class WorkerManager {
	/**
	 * Used to store workers configurations and their processes properties.
	 * @var Array with all workers configurations.
	 */
	private $workers = [];

	/**
	 * Used to store servers configurations.
	 * @var Array with all servers configurations.
	 */
	protected $servers = [];

	private $cfg = null;

	private $logger = null;

	public function __construct()
	{
//		$this->cfg = new ConfigManager();
	}

	/**
	 * If any worker is running this method read agian the workers'
	 * configurations. Otherwise throw WorkersRunningYet exception.
	 * @throws WorkersRunningYet
	 * @return $this;
	 */
	public function clearWorkersRuntimeCfg()
	{
		foreach ($this->workers as $worker) {
			if ($this->workerIsRunning($worker)) {
				$msg = "There are one or more workers running yet.";
				$msg .= " Can't clean workers' configurations.";

				throw new WorkersRunningYet($msg);
			}
		}

		unset($this->workers);
		$this->workers = [];

		return $this;
	}

	/**
	 * This method returns an array with all workers configurations and their
	 * process properties.
	 * @return Array
	 */
	public function getWorkers()
	{
		return $this->workers;
	}

	/**
	 * The setLogger can define a Logger that will be used for GearmanUtils
	 * class write messages.
	 * @param Object $logger
	 * @return $this;
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	 	return $this;
	}

	/**
	 * This method returns the workers' configuration. If the workers'
	 * configurations aren't clean the configuration file won't read again.
	 * @param String $filename A single file with the workers' configuration.
	 * @see ConfigManager::getWorkersCfg
	 */
	public function getWorkersCfg($filename = null)
	{
		if ( ! empty($this->workers)) {
			return $this->workers;
		}

		$this->workers = $this->cfg->getWorkersCfg($filename);
		return $this->workers;
	}

	/**
	 * The getServersCfg read the servers configurations and store internally.
	 * @param String $filename A single file with the servers' configuration.
	 * @return Array with all gearman servers in configuration file.
	 * @see ConfigManager::getServersCfg
	 */
	public function getServersCfg($filename = null)
	{
		if ( ! empty($this->servers)) {
			return $this->servers;
		}

		$this->servers = $this->cfg->getServersCfg($filename);
		return $this->servers;
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
	 * This method writes messages in gearman-utils log file.
	 * @param String $msg message to write.
	 * @return $this;
	 * @codeCoverageIgnore
	 */
	public function logIt($msg)
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
	 * This method start a instance of the worker.
	 * @param Object $proc worker process.
	 * @return Resource process handler
	 */
	private function startWorkerProcess($proc)
	{
		return proc_open(
			"exec " . $proc->path,
			$proc->descriptor_spec,
			$proc->pipes
		);
	}

	/**
	 * The startWorkers method starts all workers with all their instances.
	 * @return Array with all workers' configurations and process properties.
	 * @throw Exception
	 * @throw WorkersRunningYet
	 */
	public function startWorkers()
	{
		if (empty($this->workers)) {
			throw new \Exception("Can't start workers without any configuration.");
		}

		foreach ($this->workers as $worker) {
			if ($this->workerIsRunning($worker)) {
				throw new WorkersRunningYet(
					"There are one or more workers running yet. Can't start a running worker."
				);
			}
		}

		foreach ($this->workers as &$worker) {
			// start X times current worker
			for ($i=0; $i < $worker->count; $i++) {
				$proc = new \StdClass();
				$proc->path = $worker->path;
				$proc->dont_run = false;
				$proc->sleeping = 0;
				$proc->pipes = [];
				$proc->descriptor_spec = array (
					0 => array("file", "/dev/null", "r"),
					1 => array("file", ConfigManager::LOGFILE, "a"),
					2 => array("file", ConfigManager::LOGFILE, "a")
				);

				$proc->handler = $this->startWorkerProcess($proc);

				$worker->procs[] = $proc;
			}

			$msg = sprintf(
				"Started %s worker(s) %s",
				$worker->count,
				basename($worker->path, ".php")
			);

			$this->logIt($msg);
		}

		return $this->workers;
	}

	/**
	 * This method kills all worker's process.
	 * @param Object $worker
	 * @return Object $worker with process data updated.
	 */
	private function killWorker($worker)
	{
		foreach ($worker->procs as &$proc) {
			$status = @proc_get_status($proc->handler);

			if (($status !== false) && ($status['running'])) {
				// @codeCoverageIgnoreStart
				posix_kill($status['pid'], SIGKILL);
				// @codeCoverageIgnoreEnd
			} else if ($proc->handler !== false) {
				proc_terminate($proc->handler);
				$proc->handler = false;
			}
		}

		return $worker;
	}

	/**
	 * This method kills all process of the all workers.
	 * @throw KillWorkersTimeout
	 */
	public function killWorkers()
	{
		$time_spent = 0;
		do {
			$all_dead = true;
			foreach ($this->workers as $worker) {
				if (!isset($worker->procs)) {
					continue;
				}

				$worker = $this->killWorker($worker);
				// @codeCoverageIgnoreStart
				if ($this->workerIsRunning($worker)) {
					$all_dead = false;
				}
				// @codeCoverageIgnoreEnd
			}

			// @codeCoverageIgnoreStart
			if (!$all_dead) {
				sleep(1);
				$time_spent++;
			}
			// @codeCoverageIgnoreEnd
		} while ((!$all_dead) && ($time_spent <= ConfigManager::KILLWORKERS_TIMEOUT));

		if ($time_spent > ConfigManager::KILLWORKERS_TIMEOUT) {
			// @codeCoverageIgnoreStart
			throw new KillWorkersTimeout(
				"Timeout killing workers. Unexpected behavior!"
			);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Verify whether there is a instance runnig of the a worker.
	 * @param Object $worker
	 * @return Boolean
	 */
	public function workerIsRunning($worker)
	{
		if (!isset($worker->procs)) {
			return false;
		}

		foreach ($worker->procs as $proc) {
			$status = @proc_get_status($proc->handler);
			if (($status !== false) && ($status['running'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 *     Check the current state of the worker's process. The returned status
	 * are "running", "dont_run", "sleeping" or null for unknown status.
	 * @param Object $proc worker's process.
	 * @return String with process status or Null for unknown status.
	 */
	public function getProcessStatus($proc)
	{
		$status = @proc_get_status($proc->handler);
		if (($status !== false) && ($status['running'] === true)) {
			return "running";
		}

		if ((isset($status['exitcode']))
			&& ($status['exitcode'] == ConfigManager::DONTRUN_CODE)) {
			return 'dont_run';
		} elseif ((isset($status['exitcode']))
			 && ($status['exitcode'] == ConfigManager::SLEEP_CODE)) {
			return 'sleeping';
		}

		return null;
	}

}

