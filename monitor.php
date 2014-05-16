<?php

$timestamp = microtime(true)*100;
$newline = '
';

// if there's data from max
if (!empty($_GET['d'])) {



	// split it up, by dollar signs of course
	$receivedData = split('\$',$_GET['d']);

	// if there's data
	if (count($receivedData)) {

		// write it out to the data file

		$datafile = fopen('data', 'r');
		$temp = fopen('tempdata','w');

		$dataStr = fread($datafile, filesize('data'));

		$dataArr = split(',', $dataStr);

		$writeStr = '';

		foreach ($dataArr as $buffer) {
			$linearray = explode('-', $buffer);
			if (count($linearray) > 1) {
				if ($timestamp - $linearray[0] < 6000) {
					$writeStr .= $buffer.',';
				}
			}
		}

		$fakestamp = $timestamp;

		foreach ($receivedData as $d) {
			$writeStr .= $fakestamp.'-'.$d.',';
			$fakestamp += 1;
		}

		fwrite($temp, $writeStr);
		fclose($datafile);
		fclose($temp);

		if (!copy('tempdata','data')) {
	    	die('could not copy');
	    } else {
	    	echo '1';
	    	exit;
	    }
	}

	
} else if (!empty($_GET['c'])) {
	$clients = fopen('clients','r');
	$temp = fopen('tempclients','w');


	$clientstr = '';

	while (($buffer = fgets($clients)) !== false) {


		$thisClient = explode(',', $buffer);

		if (count($thisClient) > 1 && $timestamp - $thisClient[1] < 800) {
			$clientstr .= $thisClient[0].' ';
			fwrite($temp, $buffer);
		}


	}


	fclose($temp);
	fclose($clients);
	
	if (!copy('tempclients','clients')) {
		die('error');
	} else {
		echo $clientstr;
		exit;
	}

} else {
	$clients = fopen('clients','r');

	if (!empty($_GET['uid'])) {
		$uid = $_GET['uid'];
		$render = false;
	} else {
		$uid = rand(1,100000);
		$render = true;
	}

	if ($clients) {

		$temp = fopen('tempclients','w');
	     $isNew = true;
	     $numLines = 0;


		while (($buffer = fgets($clients)) !== false) {
			$numLines += 1;
	        $user = explode(',', $buffer);
	        if (count($user) > 1) {
	        	$thisUid = $user[0];
		        $userTime = $user[1];
		        if ($thisUid == $uid) {
		        	$isNew = false;
		        	fwrite($temp, $thisUid.','.$timestamp.$newline);
		        } else if ($timestamp - $userTime < 800) {
		        	fwrite($temp, $thisUid.','.$userTime.$newline);
		        }
	        }
	        
	    }

	    if ($isNew) {
	    	fwrite($temp, $uid.','.$timestamp.$newline);
	    }

	    fclose($clients);
	    fclose($temp);

	    if (!copy('tempclients','clients')) {
	    	if ($render) {
	    		die('Could not copy file.');
	    	} else {
	    		die('sup ');

	    	}
	    } else {

	    	if (!$render) {
	    		echo 1;
	    		exit;
	    	}

	    }
	}
}

if (!$render) {
	exit;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>&#9670;</title>
	<style type="text/css">
		html {
			margin: 0;
			padding: 0;
		}

		body {
			margin: 0;
			padding: 0;
			background-color: black;
			color: white;
			font-family: Helvetica, sans-serif;
			transition: all 0.1s linear;
			-webkit-transition: all 0.1s linear;
			-moz-transition: all 0.1s linear;
			-o-transition: all 0.1s linear;
			-ms-transition: all 0.1s linear;
		}

		.please, .thanks, .info {
			position: absolute;
			text-align: center;
			width: 100%;
			height: 100%;
			line-height: 100%;
			font-size: 3em;
			margin-top: 4em;
			opacity: 0;
			transition: opacity 1s linear;
			-webkit-transition: opacity 1s linear;
			-moz-transition: opacity 1s linear;
			-o-transition: opacity 1s linear;

		}

		.info {
			width: 50%;
			margin-left: 25%;
			font-size: 16px;
			line-height: 1em;
		}

		.please.active, .thanks.active, .info.active {
			opacity: 0.5;
		}

		.theiframe {
			position: absolute;
			width: 50%;
			height: 50%;
			left: 25%;
			top: 25%;
			overflow: hidden;
			border-width: 0;
		}

		.client {
			border: 1px solid white;
			width: 100px;
			height: 100px;
			margin: 20px;
			display: inline-block;
			color: white;
			text-shadow: 0 0 2px #000000;
			overflow: hidden;
			background-color: black;
		}


	</style>
</head>
<body>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript">
		var hasAudio = typeof(webkitAudioContext) === "function";
		var context;
		var oscillator;
		var lowOsc;
		var squareGain;
		var gainNode;

		clients = Array();

		schedule = Array();

		d = (new Date()).getTime();

		latestTimestamp = 0;

		theUid = <?php echo $uid ?>;

		console.log('uid: ' + theUid);
		
		$(function() {
			init();
		});


		function init() {
			
			setInterval(function() { update(); }, 500);
		}

		function update() {


			d = (new Date()).getTime();
			console.log('requesting clients...');
			$.ajax({
				url: 'http://t.obi.as/clients.php?' + d,
				success: function(theData) {
					console.log('clients received');
					dataArr = theData.split(' ');
					for (d in dataArr) {
						if (clients.indexOf(dataArr[d]) < 0 && !isNaN(dataArr[d])) {
							clients.push(dataArr[d]);
							$('body').append('<div class="client" id="client-' + parseInt(dataArr[d]) + '">440</div>')
										console.log('requesting data...');
										$.ajax({
											url: 'http://t.obi.as/data?' + d,
											success: function(data) {
												console.log('data received');
												dataArr = data.split(',');

												for (d in dataArr) {
													thisData = dataArr[d].split('-');

													if (thisData[0] > latestTimestamp) {
														latestTimestamp = thisData[0];
														getProcFunc(thisData, 2);
													}
													
													
												}
											}

										});						
						}
					}
				}
			});



		}

		function getProcFunc(thisData, i) {
			client = thisData[1];
			for (; i < thisData.length; i++) {
				funcName = 'proc_' + thisData[i];
				if (funcName in window) {
					console.log('received: ' + thisData[i]);
					i = window[funcName](thisData, client, ++i);
					return true;
				} else {
					return false;
				}
			}

		}

		function bgColor(client, color) {
			$('#client-' + client).css('background-color', '#' + color);
		}


		function proc_bg(data, client, i) {
			console.log('received bg', client, data)
			if (data[i] == 0) {
				color = '000000';
			} else {
				color = data[i];
			}

			bgColor(client, color);

			return i;

		}

		function proc_gain(data, client, i) {
			return i
			
		}

		function proc_freq(data, client, i) {
			$('#client-' + client).html(data[i]);
			return i;
			
		}

		function proc_sched(data, client, i) {
			return i;
		}

		function proc_mult(data, client, i) {
			$client = $('#client-' + client);
			$client.html(parseFloat($client.html())*data[i]);
			return i
		}

		function proc_switch(data, client, i) {
			$client = $('#client-' + client);
			console.log('bgcolor', $client.css('background-color'));
			if ($client.css('background-color') == 'rgb(0, 0, 0)') {
				bgColor(client, 'ffffff');
			} else {
				bgColor(client, '000000');
			}
			return i;
		}

		function proc_thanks(data, client, i) {
			flash('.thanks', 10000);

			return i - 1;

		}

		



	</script>
</body>

</html>