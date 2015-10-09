<?php

namespace Tests\Hunter\Gearman;

use Hunter\Gearman\ConfigManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * This class implements tests of the gearman ConfigManager class.
 */
class ConfigManagerTest extends \Tests\Hunter\AbstractTest {
	private $cm;

	protected function setUp()
	{
		$this->cm = new ConfigManager();
	}

	protected function tearDown()
	{
		$this->cm = null;
	}

	/**
	 * @testdox Verify whether current tests are being executed on ConfigManager class.
	 */
	public function isInstanceOfConfigManager()
	{
		$this->assertInstanceOf('Hunter\Gearman\ConfigManager', $this->cm);
	}

	/**
	 * @testdox Try set a valid EntityManager.
	 */
	public function trySetValidEntityManager()
	{
		$em = EntityManager::create(
			$this->cm->getDBconn(),
			Setup::createAnnotationMetadataConfiguration(
				array(__DIR__."/Entity")
			)
		);

		$this->assertInstanceOf(
			'Doctrine\ORM\EntityManager',
			$this->cm->setEntityManager($em)->getEntityManager()
		);
	}

	/**
	 * @testdox Try set invalid Gearman client.
	 * @dataProvider		invalidString
	 * @expectedException	\PHPUnit_Framework_Error
	 */
	public function trySetInvalidClient($data)
	{
		$this->tm->setClient($data);
	}
}
