<?php
/**
 * @author Stefano Simoncelli <simoncelli@igb-berlin.de>
 */

namespace components;

use Exception;

class Utils
{

    /**
     * @var string The full path where to store the GRIB files.
     */
    public $dataPath;

    /**
     * @var string The full path to resized GRIB files.
     */
    public $resizedFilesFolder;

    /**
     * @var array The TAR files in $dataPath.
     */
    public $files;

    /**
     * @var int The number of TAR files in $dataPath.
     */
    public $totalFiles;

    /**
     * @var int The grid type to use.
     * See see https://www.nco.ncep.noaa.gov/pmb/docs/on388/tableb.html
     */
    public $grid = 2;

    public function __construct()
    {
        $content = parse_ini_file('settings.ini', true);
        $this->dataPath = $content['general']['dataPath'];
        $this->resizedFilesFolder = $this->dataPath . DIRECTORY_SEPARATOR . 'resized';
        $this->grid = $content['general']['grid'];

        if(!file_exists($this->dataPath))
            throw new Exception("The '{$this->dataPath}' does not exist");


        $this->files = glob($this->dataPath . DIRECTORY_SEPARATOR . "*.tar");
        $this->totalFiles = count($this->files);
        if(!$this->totalFiles) {
            throw new Exception("No files were found in {$this->dataPath}");
        }
    }

    /**
     * Check whether a directory is empty
     * @param $dir string The path to the directory.
     * @return bool True if the directory is empty.
     */
    public static function isDirectoryEmpty($dir): bool
    {
        if(file_exists($dir)=== false)
            return true;

        $handle = opendir($dir);
        while(false !== ($entry = readdir($handle))) {
            if($entry != "." && $entry != "..") {
                closedir($handle);

                return false;
            }
        }
        closedir($handle);

        return true;
    }

    /**
     * Resize the GRIB file with a new grid.
     * @param $inFile string The input GRIB file
     * @param $outFile string The output GRIB file
     * @return bool Whether the file is resized successfully.
     */
    public function resizeFile($inFile, $outFile): bool
    {
        $command = "'{$this->wgrib2Path}' '{$inFile}' -new_grid ncep grid {$this->grid} '{$outFile}' 2>&1";
        exec($command, $output, $flag);

        if($flag || file_exists($outFile) === false) {
            throw new Exception(sprintf('An error occurred while resizing the file: %s ', implode(' ', $output)));

            return false;
        }
        return true;
    }
    /**
     * Print a message into the console
     * @param $message string The message to print
     */
    public static function logMessage($message)
    {
        echo '>> ' . $message . PHP_EOL;
    }

    /**
     * Print an error message into the console
     * @param $message string The message to print
     */
    public static function errorMessage($message)
    {
        return self::logMessage("ERROR: {$message}");;
    }

    /**
     * Delete files in a directory and the directory itself.
     * @param string $dir The directory to remove
     */
    public static function removeDirectory($dir)
    {
        if(file_exists($dir) === false)
            return false;

        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }
}