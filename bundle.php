<?php

header("Content-type: text/css");

$libs = array(
	'/css/default.css',
	'/css/style.css',
	'/css/default2.css',
	'/css/default.advanced.css',
	'/css/dropdown.css'
	);

$prefix = dirname(dirname(__FILE__));
foreach ($libs AS $lib)
{	
	//echo "\n\n /* $lib */\n";
	//readfile($prefix.$lib);
	
	// this saves approx 15% of the file size - this could be cached, but may not be quicker
	$css=file_get_contents($prefix.$lib);
	$css=preg_replace('/\/\*[^*]*\*\/|[\s]{2,}/','',$css);

	$css=preg_replace('/([^}])\n+/','$1',$css);
	echo $css;
}
?>