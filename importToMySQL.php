<?php
/**
 * Import into the databse data from iceThickness.txt. This file is generated
 * from MATLAB scripts.
 *
 * @author Stefano Simoncelli <simoncelli@igb-berlin.de>
 */

$content = parse_ini_file('settings.ini', true);
$settings = $content['mysql'];
$file = $settings['fileToImport'];

$conn = new mysqli($settings['servername'], $settings['username'], $settings['password'], $settings['dbname']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$inputQuery = mysqli_query($conn, "TRUNCATE ice_thickness");

$lines = file($file);
$columns = ['timestamp', 'latitude', 'longitude', 'air_temperature', 'ice_thickness'];
$rowDataStr = [];
$k = 0;
if ($lines !== false) {
    for ($lineNum = 0; $lineNum <= count($lines) - 1; $lineNum++) {
        $row = trim($lines[$lineNum]);
        $lineData = explode(',', $row);

        $rowDataStr[$k] = '(';
        foreach ($columns as $i => $column) {
            // MySQL lines for INSERT query
            if ($i <= 4) {
                $rowDataStr[$k] .= (float) $lineData[$i] . ',';
            } else {
                $value = $lineData[$i];
                $rowDataStr[$k] .= '"' . $value . '",';
            }
        }
        $rowDataStr[$k] = substr_replace($rowDataStr[$k], "", -1) . ')';
        $k++;

        if($k > 4000) {
            echo ">> Inserted new batch\n";
            insertRows($conn, $rowDataStr);
            $k = 0;
            $rowDataStr= [];
        }
    }
}

// Insert remaining rows
echo ">> Inserted final batch\n";
insertRows($conn, $rowDataStr);

$conn->close();

// Export SQL
$fileOut = str_replace('.txt', '.sql', $file);
exec("mysqldump -u {$settings['username']} -p{$settings['password']} {$settings['dbname']} > '{$fileOut}'");

function insertRows($conn, $data)
{
    $data = implode(',', $data);
    $inputQuery = mysqli_query($conn, "INSERT INTO ice_thickness (`timestamp`, `latitude`, `longitude`, `air_temperature`, `ice_thickness`) VALUES {$data}");
    if ($inputQuery === false) {
        echo "Error: " . mysqli_error($conn);
        return false;
    }
    return true;

}