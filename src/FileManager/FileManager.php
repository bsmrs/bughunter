<?php

namespace Hunter\FileManager;

/**
 * This implements all generic methods to work with files.
 */
class FileManager
{
	/**
	 * @const TYPE_ALL is the code for accept all file types.
	 */
	const TYPE_ALL = null;

	/**
	 * @const TYPE_DIR is code for accept only directories.
	 */
	const TYPE_DIR = 'is_dir';

	/**
	 * @const TYPE_FILE is code for accept only files.
	 */
	const TYPE_FILE = 'is_file';

	/**
	 * This method creates a file's copy.
	 * @param String $src source file with full path.
	 * @param String $dst destination file with full path.
	 * @throw RuntimeException if can't copy a file.
	 */
	public function copyFile($src, $dst)
	{
		if ( ! @copy($src, $dst)) {
			throw new \RuntimeException(
				sprintf("Can't copy file '%s' to '%s'.", $src, $dst)
			);
		}
	}

	/**
	 * This method remove a file from system.
	 * @param String $filename a file with full path.
	 * @throw RuntimeException If the file cannot be removed or it not exists..
	 * @return Boolean
	 */
	public function removeFile($filename)
	{
		if (( ! file_exists($filename)) || ( ! @unlink($filename))) {
			throw new \RuntimeException(
				sprintf("Can't remove file '%s'.", $filename)
			);
		}

		return true;
	}

	/**
	 * This method reads a file content.
	 * @param String $filename a file with full path.
	 * @return String $content file content.
	 * @throw RuntimeException if file cannot be read.
	 */
	public function getFileContent($filename)
	{
		if (false === ($content = @file_get_contents($filename))) {
			throw new \RuntimeException(
				sprintf("Unable to read '%s'. Do you have permissions to do it?", $filename)
			);
		}

		return $content;
	}


	/**
	 * This method returns a file list of a directory. Is possible inform a
	 * regex to match with returned file list. The regex must need "/" around pattern.
	 *
	 * @param String $dir a directory path.
	 * @param $pattern a regex to match with returned file list.
	 * @param $file_type can be TYPE_ALL, TYPE_DIR or TYPE_FILE.
	 * @return Array file list.
	 * @throw RuntimeException
	 *     - If $dir isn't a directory.
	 *     - If Can't open the directory.
	 * @throw InvalidArgumentException if $type is unknown.
	 */
	public function listDirFiles($dir, $type = self::TYPE_ALL, $pattern = '//')
	{

		$this->validateDirectory($dir);
		$this->validateType($type);
		$test_fn = $type;

		$files = array();
		$handler = $this->getHandler($dir);


		while (false !== ($file = readdir($handler))) {
			if ( ! @preg_match($pattern, $file)) {
				continue;
			}

			if ($type !== self::TYPE_ALL && $test_fn($dir . DS . $file)) {
				$files[] = $file;
			} else if ($type === self::TYPE_ALL) {
				$files[] = $file;
			}
		}
		closedir($handler);

		return $files;
	}

	/**
	 * If a type of file did not equals TYPE_ALL, TYPE_DIR, TYPE_FILE
	 * an InvalidArgumentException is launched
	 *
	 * @param mixed $type Type of file that must be returned
	 * @throw InvalidArgumentException
	 */
	private function validateType($type)
	{
		if (($type !== self::TYPE_ALL)
			&& ($type !== self::TYPE_DIR)
			&& ($type !== self::TYPE_FILE)) {
			throw new \InvalidArgumentException(
				sprintf("Unknown file type '%s'", $type)
			);
		}
	}

	/**
	 * If the path to directory is not a directory an RuntimeException is launched
	 *
	 * @param String $dir Path to directory where files will be searched
	 * @throw RuntimeException
	 */
	private function validateDirectory($dir)
	{
		if ( ! is_dir($dir)) {
			throw new \RuntimeException(
				sprintf("The '%s' isn't a directory.", $dir)
			);
		}
	}


	/**
	 * Get filesystem handler
	 *
	 * If the path cannot be opened a RuntimeException is launched
	 *
	 * @param String $dir Path to directory where files will be searched
	 * @throw RuntimeException
	 * @return Resource $handler Filesystem Handler
	 */
	private function getHandler($dir)
	{
		$handler = @opendir($dir);
		if ( ! $handler) {
			throw new \RuntimeException(
				sprintf("Can't open dir '%s'. Permission?", $dir)
			);
		}
		return $handler;
	}
}

