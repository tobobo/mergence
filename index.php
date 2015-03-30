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
		if (!file_exists('data')) {
			$data = fopen('data', 'w');
			fwrite($data, '');
			fclose($data);
		}
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
	if (!file_exists('clients')) {
		$clients = fopen('clients', 'w');
		fwrite($clients, '');
		fclose($clients);
	}
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
	if (!file_exists('clients')) {
		$clients = fopen('clients', 'w');
		fwrite($clients, '');
		fclose($clients);
	}
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
			overflow: hidden;
		}

		body {
			margin: 0;
			padding: 0;
			width: 100%;
			height: 100%;
			overflow: hidden;
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


	</style>
</head>
<body>
	<!--<div class="info">this website is the location of a an experimental composition. the next scheduled performance is friday, may 10th at about 10:55 PM EDT.</div>-->
	<div class="please">please turn up your sound</div>
	<div class="thanks">thank you</div>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript">
		var context;
		var oscillator;
		var lowOsc;
		var squareGain;
		var gainNode;

		hasTouch = ('ontouchstart' in document.documentElement);

		schedule = Array();

		d = (new Date()).getTime();

		latestTimestamp = 0;

		theUid = <?php echo $uid ?>;

		console.log('uid: ' + theUid);

		function ac() {
			Context = window.AudioContext || window.webkitAudioContext;
			if (Context) {
				return new Context();
			}
		}

		var hasAudio = typeof(ac()) === "object";

		$(function() {
			init();
		});

		function flash(el, time) {
					$(el).addClass('active');
					setTimeout(function() {
						$(el).removeClass('active');
					}, time);
		}

		function init() {

			if (hasAudio) {
				initWKA();

				if (hasTouch) {
					$('.please').append('<br>and tap your screen to begin').addClass('active');
				} else {
					if (top === self) {
						flash('.please', 9000);
					}
				}


			}

			flash('.info', 7000);



			setInterval(function() { update(); }, 500);
		}
		function initWKA() {
			context = ac();
    	oscillator = context.createOscillator();

			oscillator.type = 'sine';
			oscillator.frequency.value = 220;
			oscillator.baseFreq = 220;

    	lowOsc = context.createOscillator();
    	lowOsc.type = 'square';
    	lowOsc.frequency.value = 110;
    	lowOsc.baseFreq = 110;

    	squareGain = context.createGain();
    	lowOsc.connect(squareGain);
    	squareGain.gain.value = 0.48;

			// Create a gain node.
			gainNode = context.createGain();
			// Connect the source to the gain node.
			oscillator.connect(gainNode);
	    	squareGain.connect(gainNode);
			// Connect the gain node to the destination.
			gainNode.connect(context.destination);
			gainNode.gain.value = 0.0;

			updateFreq();
			oscillator.start(0);
			lowOsc.start(0);
			if (hasTouch) {
				window.ontouchstart = function() {
					oscillator.start(0);
					lowOsc.start(0);
					$('.please').removeClass('active');
				}
			}
		}

		function update() {


			d = (new Date()).getTime();

			$.ajax({
				url: '/',
				data: {
					uid: theUid
				},
				success: function(theData) {
					if (theData == 1) {
						console.log('pinged server');
					} else {
						console.log('ping failed');
						console.log(theData);
					}

				}
			});

			$.ajax({
				url: '/data?' + d,
				success: function(data) {
					dataArr = data.split(',');

					for (d in dataArr) {
						thisData = dataArr[d].split('-');

						if (thisData[1] == theUid && thisData[0] > latestTimestamp) {
							latestTimestamp = thisData[0];
							getProcFunc(thisData, 2);
						}


					}
				}

			});

		}

		function getProcFunc(thisData, i) {
			for (; i < thisData.length; i++) {
				funcName = 'proc_' + thisData[i];
				if (funcName in window) {
					console.log('received: ' + thisData[i]);
					i = window[funcName](thisData, ++i);
					return true;
				} else {
					return false;
				}
			}

		}

		function updateFreq() {

			newVal = oscillator.baseFreq + (Math.sin(context.currentTime*50))*2;

			oscillator.frequency.value = newVal;
			newLow = lowOsc.baseFreq + (Math.sin(context.currentTime*0.075))*0.75;

			lowOsc.frequency.value = newLow;
			theD = (new Date()).getTime();

			for (s in schedule) {

				diff = Math.abs(theD-schedule[s][0]*1000);

				if (diff <= 1) {

					getProcFunc(schedule[s], 1);

					console.log(schedule[s]);
					schedule.splice(s);

				}

			}

			setTimeout(function() { updateFreq() }, 1);

		}

		function bgColor(color) {
			$('body').css('background-color', '#' + color);
		}


		function proc_bg(data, i) {

			if (data[i] == 0) {
				color = '000000';
			} else {
				color = data[i];
			}

			bgColor(color);

			return i;

		}

		function proc_gain(data, i) {
			if (hasAudio) {
				gainNode.gain.value = data[i];
				console.log('gain: ' + data[i])
				return i;
			}

		}

		function proc_freq(data, i) {
			if (hasAudio) {
				oscillator.baseFreq = data[i]/1;
				lowOsc.baseFreq = data[i]/2;
				console.log('freq: ' + data[i]);
				return i;
			}

		}

		function proc_sched(data, i) {
			thisSched = Array();
			firstIndex = i;


			for (; i < data.length; i++) {
				thisSched.push(data[i]);
			}

			currentD = new Date().getTime();
			thisSched[0] = parseFloat(thisSched[0]);
			schedule.push(thisSched);
			console.log('scheduling at ' + parseFloat(thisSched[0])*1000 +  ' (in ' + (currentD - parseFloat(thisSched[0])*1000) + ' ms)');

			return i;
		}

		function proc_mult(data, i) {
			if (hasAudio) {
				oscillator.baseFreq = oscillator.baseFreq * data[i];
				lowOsc.baseFreq = lowOsc.baseFreq * data[i];
				return i;

			}
		}

		function proc_switch(data, i) {
			if (hasAudio) {
				if (gainNode.gain.value < 0.1) {
					gainNode.gain.value = 1;
					bgColor('ffffff');
				} else {
					gainNode.gain.value = 0;
					bgColor('000000');
				}
			}
			return i;
		}

		function proc_thanks(data, i) {
			flash('.thanks', 10000);

			return i - 1;

		}





	</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-40601699-1']);
  _gaq.push(['_setDomainName', 'obi.as']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>

</html>
