<?php

/**
 * возвращает строку пути запроса к файлу или страницы без query string
 */
function getUriPath(string $str): string
{
	return explode("?", $str)[0];
}

function startServer()
{
	/**
	 * Путь к файлу без query string
	 */
	define("URIPath", getUriPath($_SERVER['REQUEST_URI']));
	/**
	 * Массив Content-types
	 */
	define("MIME_TYPES", array(
		'txt' => 'text/plain',
		'htm' => 'text/html',
		'html' => 'text/html',
		'php' => 'text/html',
		'css' => 'text/css',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'swf' => 'application/x-shockwave-flash',
		'flv' => 'video/x-flv',

		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'bmp' => 'image/bmp',
		'ico' => 'image/vnd.microsoft.icon',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',
	));

	require("./vendor/routerFunction.php");
	$_SERVER["ROUTS"] = array();


	include_once("./router/router.php");

	if (isset($_SERVER["ROUTS"][URIPath])) {
		$_SERVER["ROUTS"][URIPath]();
	} else if (thisIsSourceFile(URIPath)) {
		responseSourceFile(URIPath);
	} else {
		response404();
	}
}
