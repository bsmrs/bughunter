<?php

namespace Hunter\FileManager;

/**
 * This implements all generic methods to work with files.
 */
class FileManager {

	/**
	 * @const TYPE_ALL is the code for accept all file types.
	 */
	const TYPE_ALL = 100;

	/**
	 * @const TYPE_DIR is code for accept only directories.
	 */
	const TYPE_DIR = 101;

	/**
	 * @const TYPE_FILE is code for accept only files.
	 */
	const TYPE_FILE = 102;

	/**
	 * This method creates a file's copy.
	 * @param String $src source file with full path.
	 * @param String $dst destination file with full path.
	 * @throw RuntimeException if can't copy a file.
	 */
	public function copyFile($src, $dst)
	{
		$ret = @copy($src, $dst);
		if ( ! $ret) {
			$msg = sprintf(
				"Can't copy file '%s' to '%s'.",
				$src,
				$dst
			);

			throw new \RuntimeException($msg);
		}
	}

	/**
	 * This method remove a file from system.
	 * @param String $filename a file with full path.
	 * @throw RuntimeException if can't remove a file.
	 * @return Boolean
	 */
	public function removeFile($filename)
	{
		if ( ! file_exists($filename)) {
			return false;
		}

		$ret = @unlink($filename);
		if ( ! $ret) {
			$msg = sprintf(
				"Can't remove file '%s'.",
				$filename
			);

			throw new \RuntimeException($msg);
		}

		return true;
	}

	/**
	 * This method reads a file content.
	 * @param String $filename a file with full path.
	 * @return String $content file content.
	 * @throw RuntimeException if can't read a file.
	 */
	public function getFileContent($filename)
	{
		$content = @file_get_contents($filename);
		if ($content === false) {
			throw new \RuntimeException(
				"Unable to read '" . $filename . "'. Permissions?"
			);
		}

		return $content;
	}


	/**
	 * This method returns a file list of the a directory. Is possible inform a
	 * regex to match with returned file list. The regex doesn't need "/" at
	 * start and at end.
	 * @param String $dir a directory path.
	 * @param $pattern a regex to match with returned file list.
	 * @param $file_type can be TYPE_ALL, TYPE_DIR or TYPE_FILE.
	 * @return Array file list.
	 * @throw RuntimeException
	 *     - If $dir isn't a directory.
	 *     - If Can't open the directory.
	 * @throw InvalidArgumentException if $file_type is unknown.
	 */
	public function listDirFiles($dir, $pattern = null, $file_type = self::TYPE_ALL)
	{
		if ( ! is_dir($dir)) {
			$msg = sprintf(
				"The '%s' isn't a directory.",
				$dir
			);

			throw new \RuntimeException($msg);
		}

		$files = array();

		switch ($file_type) {
			case self::TYPE_ALL:
				$test_fn = null;
				break;
			case self::TYPE_DIR:
				$test_fn = "is_dir";
				break;
			case self::TYPE_FILE:
				$test_fn = "is_file";
				break;
			default:
				$msg = sprintf(
					"Unknown file type '%s'",
					$file_type
				);

				throw new \InvalidArgumentException($msg);
				break;
		}

		$handler = @opendir($dir);
		if ( ! $handler) {
			// @codeCoverageIgnoreStart
			$msg = sprintf(
				"Can't open dir '%s'. Permission?",
				$dir
			);

			throw new \RuntimeException($msg);
			// @codeCoverageIgnoreEnd
		}

		while (false !== ($file = readdir($handler))) {
			if (($file_type !== self::TYPE_ALL) && ($test_fn($dir . DS . $file))) {
				$files[] = $file;
			} else if ($file_type === self::TYPE_ALL) {
				$files[] = $file;
			}
		}

		closedir($handler);

		if (is_null($pattern)) {
			return $files;
		}

		$matched_files = array();
		if ( ! preg_match("/^\/.*\/$/", $pattern)) {
			$pattern = sprintf(
				"/%s/",
				$pattern
			);
		}

		foreach ($files as $file) {
			if (preg_match($pattern, $file)) {
				$matched_files[] = $file;
			}
		}

		return $matched_files;
	}
}

