<?php
/**
 * Unpack the data and resize using a new grid. The output files are
 * then imported into MATLAB for further analysis and compute the ice
 * thickness over mutiple years.
 *
 * @author Stefano Simoncelli <simoncelli@igb-berlin.de>
 */

use components\Utils;

require __DIR__ . '/Utils.php';

$utils = new Utils();

$utils->files = glob($utils->dataPath . DIRECTORY_SEPARATOR . "*.tar");
$utils->totalFiles = count($utils->files);
if(!$utils->totalFiles) {
    $utils::errorMessage("No files were found in {$utils->dataPath}");

    return false;
}

// Unpack files
$unTarFolder = $utils->dataPath . DIRECTORY_SEPARATOR . 'unpacked';
$utils::logMessage(">> Unpacking files to {$unTarFolder}");
if(is_dir($unTarFolder) === false && Utils::isDirectoryEmpty($unTarFolder) === false) {
    @mkdir($unTarFolder);
    foreach($utils->files as $j => $file) {
        $j = $j + 1;
        $utils::logMessage("Unpacking ({$j}/{$utils->totalFiles}) {$file}");
        exec("tar -xf '{$file}' -C '{$unTarFolder}'", $output, $flag);
        if($flag) {
            $utils::errorMessage("Failed to unpack because: {$output}");
        }

    }
    $utils::logMessage('All files have been unpacked');
} else {
    $utils::logMessage('Files have already been unpacked');
}

// Resize
$totalRawFiles = sizeof(glob($unTarFolder . DIRECTORY_SEPARATOR . '*'));
$unpack = true;
if(file_exists($utils->resizedFilesFolder)) {
    $utils::logMessage("Output folder {$utils->resizedFilesFolder} already exists.");
    echo "Do you want to delete? If not, unpacked files will be preserved [y/N]";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    if(trim(strtolower($line)) == 'y') {
        $utils::removeDirectory($utils->resizedFilesFolder);
        $utils::logMessage("Folder {$utils->resizedFilesFolder} deleted");
    } else {
        $unpack = false;
    }

}
// If directory does not exist or has been previously deleted, create it
if(file_exists($utils->resizedFilesFolder) === false) {
    @mkdir($utils->resizedFilesFolder);
    chmod($utils->resizedFilesFolder, 0777);
}
$utils::logMessage("Resizing files to {$utils->resizedFilesFolder}");
$dataToUnpack = glob($unTarFolder . DIRECTORY_SEPARATOR . 'gdas1.sflux.*12.f*');
$totalFiles = sizeof($dataToUnpack);

if($unpack === true) {
    // skip samples not taken at noon
    foreach($dataToUnpack as $j => $inFile) {
        $outFile = $utils->resizedFilesFolder . DIRECTORY_SEPARATOR . basename($inFile);
        $utils::logMessage("Resizing ({$j}/{$totalFiles}) {$inFile}");
        $utils->resizeFile($inFile, $outFile);
    }
} else {
    $utils::logMessage("Skipped resizing.");
}

// Extract data
$resizedData = glob($utils->resizedFilesFolder . DIRECTORY_SEPARATOR . 'gdas1.sflux.*12.f*');

$outFolder = $utils->dataPath . DIRECTORY_SEPARATOR . 'out';
$utils::logMessage("Extracting data to {$outFolder} for MATLAB analysis");
$totalFiles = sizeof($resizedData);
@mkdir($outFolder);
$time = [];
foreach($resizedData as $j => $file) {
    $fileName = basename($file);
    $tmpFile = $outFolder . DIRECTORY_SEPARATOR . "{$fileName}.txt";
    $j++;

    if(strpos($fileName, '.txt') !== false)
        continue;

    // Date
    preg_match('/^gdas1.sflux.([0-9]+).f(\w+)/', $fileName, $matches);
    $date = $matches[1];
    $dateObject = DateTime::createFromFormat("YmdH", $date);
    $time[] = $dateObject->format('d/m/Y');

    // Get position and air temperature
    $utils::logMessage("Processing ({$j}/{$totalFiles}) {$file}");
    $command = "grib_get_data -m -9999 -F '%.5e' '{$file}' > '{$tmpFile}'";

    exec($command);

    if(file_exists($tmpFile) === false)
        $utils::errorMessage("File {$tmpFile} not created");
}
//file_put_contents($utils->dataPath . DIRECTORY_SEPARATOR . 'out/time.txt',
//    implode(PHP_EOL, $time));

// Remove unpacked
$utils::removeDirectory($unTarFolder);
$utils::logMessage("Deleted folder {$unTarFolder}");