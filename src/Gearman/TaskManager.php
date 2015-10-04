<?php

namespace Hunter\Gearman;

/**
 * A pure PHP class to manage gearman tasks.
 */
class TaskManager {
	/**
	 * @const NORMAL is the normal priority for a gearman task.
	 */
	const NORMAL = 0;

	/**
	 * @const HIGH is the high priority for a gearman task.
	 */
	const HIGH = 1;

	/**
	 * @const VERY_HIGH is the very high priority for a gearman task.
	 */
	const VERY_HIGH = 2;

	/**
	 * @var Object $client is used to store a instance of the GearmanClient
	 * class.
	 */
	private $client;

	public function __construct(\GearmanClient $client = null)
	{
		$this->setClient(($client) ?: new \GearmanClient());
		$this->client->addServer('127.0.0.1');
	}

	/**
	 * This method returns a copy of the local of GearmanClient instance.
	 *
	 * @return GearmanClient
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * The setClient method is used to overwrite original instance of the
	 * GearmanClient.
	 *
	 * @param GearmanClient $client
	 * @return TaskManager
	 */
	public function setClient(\GearmanClient $client)
	{
		$this->client = $client;
		return $this;
	}

	/**
	 * This method send a task for a gearman queue with some priority and wait
	 * the result. The priority can by NORMAL (default), HIGH or VERY HIGH.
	 *
	 * @param String $queue
	 *     Is the queue's name that will used to sent a task.
	 * @param Mixed $data
	 *     Is the data that will be sent to worker.
	 * @param Integer $priority
	 *     Is the priority that the task has to be performed.
	 * @throw InvalidArgumentException
	 *     If priority is unknown.
	 * @throw RuntimeException
	 *     If can't submit a task for geaman.
	 * @return String
	 *     Is a string representing the results of running a task.
	 */
	public function run($queue, $data, $priority = self::NORMAL)
	{
		switch ($priority) {
			case self::NORMAL:
				$result = $this->client->doLow($queue, $data);
				break;
			case self::HIGH:
				$result = $this->client->doNormal($queue, $data);
				break;
			case self::VERY_HIGH:
				$result = $this->client->doHigh($queue, $data);
				break;
			default:
				throw new \InvalidArgumentException(
					"Unknown run priority: " . $priority
				);
		}

		if ($this->client->returnCode() != GEARMAN_SUCCESS) {
			throw new \RuntimeException("Can't submit job to queue: " . $queue);
		}

		return $result;
	}

	/**
	 * This method send a task for a gearman queue runs in background with some
	 * priority. The priority can by NORMAL (default), HIGH or VERY HIGH.
	 *
	 * @param String $queue
	 *     Is the queue's name that will used to sent a task.
	 * @param Mixed $data
	 *     Is the data that will be sent to worker.
	 * @param Integer $priority
	 *     Is the priority that the task has to be performed.
	 * @throw InvalidArgumentException
	 *     If priority is unknown.
	 * @throw RuntimeException
	 *     If can't submit a task for geaman.
	 * @return String
	 *     The job handle for the submitted task.
	 */
	public function runBackground($queue, $data, $priority = self::NORMAL)
	{
		switch ($priority) {
			case self::NORMAL:
				$handler = $this->client->doLowBackground($queue, $data);
				break;
			case self::HIGH:
				$handler = $this->client->doBackground($queue, $data);
				break;
			case self::VERY_HIGH:
				$handler = $this->client->doHighBackground($queue, $data);
				break;
			default:
				throw new \InvalidArgumentException(
					"Unknown run priority: " . $priority
				);
		}

		if ($this->client->returnCode() != GEARMAN_SUCCESS) {
			throw new \RuntimeException("Can't submit job to queue: " . $queue);
		}

		return $handler;
	}

	/**
	 * The getQueueStatus method returns the gearman's queue status, with the
	 * queue name, available workers, jobs waiting and running.
	 *
	 * @return Array
	 */
	public function getQueueStatus()
	{
		$data = [];
		$sock = $this->openSocket();
		$this->sendCommand($sock, 'status');

		do {
			$content = $this->getStreamContent($sock);

			if (preg_match("/^\./", $content)) {
				break;
			}

			@list($name, $waiting, $running, $available_workers)
				= explode("\t", $content);
			$data[] = [
				"queue" => $name,
				"jobs_waiting" => $waiting,
				"jobs_running" => $running,
				"available_workers" => $available_workers
			];
		} while (true);

		@fclose($sock);

		return $data;
	}

	/**
	 * The sendCommand is used to send commands into a resource.
	 *
	 * @param Resource $stream
	 * @aparam String $command to send.
	 * @throw RuntimeException whether $stream isn't a resource.
	 * @codeCoverageIgnore
	 */
	protected function sendCommand($stream, $command)
	{
		if (is_resource($stream)) {
			fprintf($stream, $command . "\n");
			return;
		}

		throw new \RuntimeException(__FUNCTION__ . ": Resource is invalid.");
	}

	/**
	 * This method reads from stream resource (socket).
	 *
	 * @param Resource
	 * @return String
	 * @codeCoverageIgnore
	 */
	protected function getStreamContent($stream)
	{
		return (is_resource($stream)) ? trim(fgets($stream)) : "";
	}

	/**
	 * The openSocket method open a socket with Gearman on localhost.
	 *
	 * @return Resource
	 * @throw RuntimeException Whether can't connect on Gearman.
	 * @codeCoverageIgnore
	 */
	protected function openSocket()
	{
		$sock = @fsockopen('127.0.0.1', 4730, $err, $errstr, 3);
		if ( ! $sock) {
			// @codeCoverageIgnoreStart
			throw new \RuntimeException(
				"Can't connect on Gearman using localhost:4730."
			);
			// @codeCoverageIgnoreEnd
		}

		stream_set_timeout($sock, 2);

		return $sock;
	}

	/**
	 * Get the status of a background job.
	 *
	 * @param String $job_handler
	 *    Is the same returned by run and runBackground methods.
	 * @return Array
	 *    An array containing status information for the job corresponding to
	 *    the supplied job handle. The first array element is a boolean
	 *    indicating whether the job is even known, the second is a boolean
	 *    indicating whether the job is still running, and the third and fourth
	 *    elements correspond to the numerator and denominator of the
	 *    fractional completion percentage, respectively.
	 * @see TaskManager::run()
	 * @see TaskManager::runBackground()
	 * @codeCoverageIgnore
	 */
	public function getJobStatus($job_handler)
	{
		return $this->client->jobStatus($job_handler);
	}

	/**
	 * Verify whether a job is running or not.
	 *
	 * @param String $job_handler
	 *     Is the same returned by run and runBackground methods.
	 * @return Boolean
	 * @see TaskManager::run()
	 * @see TaskManager::runBackground()
	 */
	public function jobIsRunning($job_handler)
	{
		$status = $this->getJobStatus($job_handler);

		// unknown job or finished
		return (( ! $status[0]) || ( ! $status[1])) ? false : true;
	}
}

