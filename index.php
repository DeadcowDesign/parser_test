#!/usr/bin/php -q
<?php
require_once('Application\FileParser.php');

$generateCSV = new Application\FileParser();

// Not super robust, but takes the first (well, second argument, technically)
// passed to the PHP script and uses that as the folder location.
if (isset($_SERVER['argv'][1])) {
    $generateCSV->writeCSV($_SERVER['argv'][1]);
} else {
    $generateCSV->writeCSV();
}


//
