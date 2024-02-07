<?php

$page = (strtok((explode('/', $_SERVER['REQUEST_URI']))[1] , '?')) ?: 'index';

$Title = [
	'index' => "Юридический портал",
];

$Description = [
	'index' => "Юридический портал",
];

$Keywords = [
	'index' => "Юридический портал",
];

if( isset( $Title[$page] ) ) $HTML = str_replace( '<!--title-->' , $Title[$page] , $HTML );
if( isset( $Description[$page] ) ) $HTML = str_replace( '<!--description-->' , $Description[$page] , $HTML );
if( isset( $Keywords[$page] ) ) $HTML = str_replace( '<!--keywords-->' , $Keywords[$page] , $HTML );