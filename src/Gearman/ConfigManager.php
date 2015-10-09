<?php

/**
 * This class implements a gearman worker configuration manager.
 */

namespace Hunter\Gearman;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Hunter\Gearman\Entity\WorkerCfg;
use Psr\Log\LoggerInterface as Logger;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;
use Respect\Validation\Validator;

class ConfigManager {
	private $em;

	private $db_conn = [
		'dbname'	=> 'config',
		'user'		=> DBUSER,
		'password'	=> DBPASS,
		'host'		=> DBHOST,
		'driver'	=> DBDRIVER
	];

	public function __construct(EntityManager $em = null)
	{
		$this->setEntityManager(($em) ?: $this->getNewEntityManager());
	}

	private function getNewEntityManager()
	{
		return EntityManager::create(
			$this->db_conn,
			Setup::createAnnotationMetadataConfiguration(
				array(__DIR__."/Entity")
			)
		);
	}

	public function getDBConn()
	{
		return $this->db_conn;
	}

	public function getEntityManager()
	{
		return $this->em;
	}

	public function setEntityManager(EntityManager $em)
	{
		$this->em = $em;
		return $this;
	}

	public function getDBname()
	{
		return $this->db_conn['dbname'];
	}

	public function setDBname($name)
	{
		Validator::alnum('_')
			->noWhitespace()
			->notEmpty()
			->length(1, 255)
			->check($name);

		$this->db_conn['dbname'] = $name;
		return $this;
	}
}
