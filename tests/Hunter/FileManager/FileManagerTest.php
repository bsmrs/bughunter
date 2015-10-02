<?php

namespace Tests\Hunter\FileManager;

use Hunter\FileManager\FileManager as FM;

/**
 * FileManager's test class
 */
class FileManagerTest extends \Tests\Hunter\AbstractTest
{

	private $file_manager;
	private $junkFile;
	private $immutableFile;

	protected function setUp()
	{
		$this->file_manager = new FM();
		$this->junkFile = __DIR__ . DS . "junk_test_file";
		$this->immutableFile = __DIR__ . DS . "junk_immutable_test_file";
	}

	protected function tearDown()
	{
		unset($this->file_manager);
	}

	/**
	 * @testdox Verify whether current tests are being executed on FileManager class.
	 */
	public function isInstanceOfFileManager()
	{
		$this->assertInstanceOf('Hunter\FileManager\FileManager', $this->file_manager);
	}

	/**
	 * @testdox Try copy a invalid file.
	 * @expectedException RuntimeException
	 */

	public function tryCopyInvalidFile()
	{
		$this->file_manager->copyFile("invalid_file", "invalid_file");
	}

	/**
	 * @testdox Try copy a valid file.
	 */
	public function tryCopyValidFile()
	{
		$this->file_manager->copyFile(__FILE__, $this->junkFile);
	}

	/**
	 * @testdox Try remove a valid file.
	 * @depends tryCopyValidFile
	 */
	public function tryRemoveValidFile()
	{
		$this->file_manager->removeFile($this->junkFile);
	}

	/**
	 * @testdox Try remove a invalid file.
	 * @depends tryRemoveValidFile
	 * @expectedException \RuntimeException
	 */
	public function tryRemoveInvalidFile()
	{
		$bool = $this->file_manager->removeFile($this->junkFile);
		$this->assertFalse($bool);
	}

	/**
	 * @testdox Try remove a immutable file.
	 * @expectedException RuntimeException
	 */
	public function tryRemoveImmutableFile()
	{
		$this->file_manager->removeFile($this->immutableFile);
	}

	/**
	 * @testdox Try read a valid file.
	 */
	public function readValidFile()
	{
		$this->assertEmpty($this->file_manager->getFileContent($this->immutableFile));
	}

	/**
	 * @testdox Try read a invalid file.
	 * @expectedException RuntimeException
	 */
	public function readInvalidFile()
	{
		$this->file_manager->getFileContent("invalid_file");
	}

	/**
	 * @testdox Verify whether is possible list files from a invalid directory.
	 * @expectedException RuntimeException
	 */
	public function tryListFilesFromInvalidDir()
	{
		$this->file_manager->listDirFiles("invalid_directory");
	}

	/**
	 * @testdox Verify whether is possible list a invalid type of files directory.
	 * @expectedException InvalidArgumentException
	 */
	public function tryListInvalidTypeOfFile()
	{
		$this->file_manager->listDirFiles(__DIR__, 10);
	}

	/**
	 * @testdox Verify whether is possible list only files from a directory.
	 */
	public function listFiles()
	{
		$anyPhpFile = "/^\w.*.php/";
		$files = $this->file_manager->listDirFiles(__DIR__, FM::TYPE_FILE, $anyPhpFile);
		$this->assertEquals(1, count($files));
	}

	/**
	 * @testdox Verify whether is possible list only directories from a directory.
	 */
	public function listDirectories()
	{
		$dir = $this->file_manager->listDirFiles(__DIR__, FM::TYPE_DIR);
		$this->assertEquals(2, count($dir));
	}

	/**
	 * @testdox Verify whether is possible list directories and files from a directory.
	 */
	public function listDirAndFiles()
	{
		$getAllFilesThatIsNotHidden = "(^[.][.]?$|^\w.*)";
		$files = $this->file_manager->listDirFiles(__DIR__, FM::TYPE_ALL, $getAllFilesThatIsNotHidden);
		$this->assertEquals(4, count($files));
	}

	/**
	 * @testdox Verify whether an exception is launched when a directory could not be opened
	 * @expectedException \RuntimeException
	 */
	public function getHandlerError()
	{
		$method = $this->getReflectedMethod('Hunter\FileManager\FileManager', 'getHandler');
		$method->invokeArgs($this->file_manager, ['/oi']);
	}

	/**
	 * @testdox Does FileManager::listDirFiles() returns an empty array when an invalid pattern is passed?
	 */
	public function invalidPatternToListDirFiles()
	{
		$files = $this->file_manager->listDirFiles(__DIR__, FM::TYPE_ALL, null);
		$this->assertEmpty($files);
	}
}
