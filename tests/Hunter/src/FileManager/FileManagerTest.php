<?php

namespace Hunter;
use Hunter\FileManager\FileManager;

/**
 * FileManager's test class
 */
class FileManagerTest extends \PHPUnit_Framework_TestCase {
	private $file_manager;

	protected function setUp()
	{
		$this->file_manager = new FileManager();
	}

	/**
	 * @testdox Verify whether current tests are being executed on FileManager class.
	 * @test
	 */
	public function isInstanceOfFileManager()
	{
		$this->assertInstanceOf('Hunter\FileManager\FileManager', $this->file_manager);
	}

	/**
	 * @testdox Try copy a invalid file.
	 * @expectedException RuntimeException
	 * @test
	 */
	public function tryCopyInvalidFile()
	{
		$this->file_manager->copyFile("invalid_file", "invalid_file");
	}

	/**
	 * @testdox Try copy a valid file.
	 * @test
	 */
	public function tryCopyValidFile()
	{
		$this->file_manager->copyFile(
			__FILE__,
			__DIR__ . DS . "junk_test_file"
		);
	}

	/**
	 * @testdox Try remove a valid file.
	 * @depends tryCopyValidFile
	 * @test
	 */
	public function tryRemoveValidFile()
	{
		$this->file_manager->removeFile(
			__DIR__ . DS . "junk_test_file"
		);
	}

	/**
	 * @testdox Try remove a invalid file.
	 * @depends tryRemoveValidFile
	 * @test
	 */
	public function tryRemoveInvalidFile()
	{
		$this->assertFalse(
			$this->file_manager->removeFile(
				__DIR__ . DS . "junk_test_file"
			)
		);
	}

	/**
	 * @testdox Try remove a immutable file.
	 * @expectedException RuntimeException
	 * @test
	 */
	public function tryRemoveImmutableFile()
	{
		$this->file_manager->removeFile(
			__DIR__ . DS . "junk_immutable_test_file"
		);
	}

	/**
	 * @testdox Try read a valid file.
	 * @test
	 */
	public function readValidFile()
	{
		$this->assertEmpty(
			$this->file_manager->getFileContent(
				__DIR__ . DS . "junk_immutable_test_file"
			)
		);
	}

	/**
	 * @testdox Try read a invalid file.
	 * @expectedException RuntimeException
	 * @test
	 */
	public function readInvalidFile()
	{
		$this->file_manager->getFileContent(
			"invalid_file"
		);
	}

	/**
	 * @testdox Verify whether is possible list files from a invalid directory.
	 * @expectedException RuntimeException
	 * @test
	 */
	public function tryListFilesFromInvalidDir()
	{
		$this->file_manager->listDirFiles("invalid_directory");
	}

	/**
	 * @testdox Verify whether is possible list a invalid type of files directory.
	 * @expectedException InvalidArgumentException
	 * @test
	 */
	public function tryListInvalidTypeOfFile()
	{
		$this->file_manager->listDirFiles(__DIR__, null, 10);
	}

	/**
	 * @testdox Verify whether is possible list only files from a directory.
	 * @test
	 */
	public function listFiles()
	{
		$files = $this->file_manager->listDirFiles(
			__DIR__,
			"^\w.*.php",
			FileManager::TYPE_FILE
		);

		$this->assertEquals(1, count($files));
	}

	/**
	 * @testdox Verify whether is possible list only directories from a directory.
	 * @test
	 */
	public function listDirectories()
	{
		$dir = $this->file_manager->listDirFiles(
			__DIR__,
			null,
			FileManager::TYPE_DIR
		);

		$this->assertEquals(2, count($dir));
	}

	/**
	 * @testdox Verify whether is possible list directories and files from a directory.
	 * @test
	 */
	public function listDirAndFiles()
	{
		$files = $this->file_manager->listDirFiles(
			__DIR__,
			"(^[.][.]?$|^\w.*)",
			FileManager::TYPE_ALL
		);

		$this->assertEquals(4, count($files));
	}
}

