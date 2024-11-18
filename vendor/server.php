<?php

/**
 * возвращает строку пути запроса к файлу или страницы без query string
 */
function getUriPath(string $str): string
{
	if ($r = explode("?", $str)[0]) {
		# code...
	}
	return $r ?? $str;
}

function startServer()
{
	/**
	 * Путь к файлу без query string
	 */
	define("URIPath", getUriPath($_SERVER['REQUEST_URI']));
	define('URIQuery', $_SERVER["QUERY_STRING"]);

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

	$reqRout = $_SERVER["ROUTS"][$_SERVER["REQUEST_METHOD"]];
	// echo count(explode("?", $_SERVER['REQUEST_URI']));
	if (count(explode("?", $_SERVER['REQUEST_URI'])) > 1) {
		if (isset($reqRout[URIPath . "?"])) {
			$reqRout[URIPath . "?"]($_REQUEST);
		};
	} else if (isset($reqRout[URIPath])) {
		$reqRout[URIPath]($_REQUEST);
	} else if (thisIsSourceFile(URIPath)) {
		responseSourceFile(URIPath);
	} else {
		response404();
	}
}
