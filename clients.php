<?php
$clients = fopen('clients','r');
$temp = fopen('tempclients','w');

$newline = '
';

$clientstr = '';

while (($buffer = fgets($clients)) !== false) {
	$thisClient = explode(',', $buffer);
	if (count($thisClient) > 1 && $timestamp - $thisClient[1] < 800) {
		$clientstr .= $thisClient[0].' ';
		fwrite($temp, $buffer);
	}
	
	if (!copy('tempclients','clients')) {
		die('0');
	} else {
		echo $clientstr;
	}
}



?>