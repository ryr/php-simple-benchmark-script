<?php

$phpversion = explode('.', PHP_VERSION);

$options = getopt("m:");
if (isset($options['m']) && (int)$options['m']) {
	$memoryLimit = (int)$options['m'];
} else if (isset($_GET['memory_limit']) && (int)$_GET['memory_limit']) {
	$memoryLimit = (int)$_GET['memory_limit'];
} else if ((int)getenv('PHP_MEMORY_LIMIT')) {
	$memoryLimit = (int)getenv('PHP_MEMORY_LIMIT');
} else {
	$memoryLimit = 256;
}

ini_set('memory_limit', $memoryLimit . "M");

// Force output flushing, like in CLI
// May help with proxy-pass apache-nginx
@ini_set('output_buffering', 0);
@ini_set('implicit_flush', 1);
ob_implicit_flush(1);
// Special for nginx
header('X-Accel-Buffering: no');

@ini_set('error_reporting', 0);
@ini_set('display_errors', 0);
@ini_set('log_errors', 0);

function convert($size)
{
	$unit = array('b', 'kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb');
	return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function shutDownFunction() {
	global $s, $memoryLimit, $phpversion;

	echo '<pre>' . PHP_EOL;
	if ((int)$phpversion[0] >= 7) {
		echo str_pad('String length: ', 18) . convert(mb_strlen($s)) . PHP_EOL;
	}
	echo str_pad('Allocated memory: ', 18) . convert(memory_get_peak_usage(true)) . PHP_EOL;
	echo str_pad('Memory limit: ', 18) . convert($memoryLimit * 1024 * 1024) . PHP_EOL;
	echo '</pre>' . PHP_EOL;
}
register_shutdown_function('shutDownFunction');

$l = 1024 * 1024; // 1 Mb
$s = '';

for ($i = 0; $i < $memoryLimit; $i++) {
	$s .= str_pad('a', $l);
}
