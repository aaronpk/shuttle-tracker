<?php
chdir(dirname(__FILE__).'/..');
include('lib/inc.php');

$result = $tile38->rawCommand('PDELHOOK', '*');
print_r($result);

echo "\n";
