<?php

declare(strict_types=1);

define("CSS_PATH", "./css/");
define("JS_PATH", "./JS/");
/**
 * Массивы с папками
 * @var array{css: array, js: array}
 */
define("SOURCE_DIRS", array(
	"css" => scandir(CSS_PATH),
	"js" => scandir(JS_PATH)
));
/**
 * Путь к [name]
 */
define("PAGE_WRAPPER", explode("/", URIPath));

function responseContentType(string $filepath): void
{
	header("Content-type: " . getFileContentType($filepath));
}
/** 
 * Функция которая находит файл и возвращает его пользователю если файл не найден отвечает `response404()`;
 * @param string $filename - путь к файлу и это путь должен начинаться с `/`
 */
function responseSourceFile(string $filename): void
{
	try {
		$reqContentType = getFileContentType($filename);
		header("Content-type: " . $reqContentType);
		if ($reqContentType == MIME_TYPES['css']) {
			foreach (SOURCE_DIRS["css"] as $item) {
				if ($item === "[" . PAGE_WRAPPER[2] . "]") {
					echo genCSSBundle(CSS_PATH . $item);
					exit();
				}
			}
		} else if ($reqContentType == MIME_TYPES['js']) {
			foreach (SOURCE_DIRS["js"] as $item) {
				if ($item === "[" . PAGE_WRAPPER[2] . "]") {
					echo genJSBundle(JS_PATH . $item);
					exit();
				}
			}
		}
		if (".$filename" === "./css/page.css") {
			echo file_get_contents(CSS_PATH . "global.css");
			exit();
		} else if (".$filename" === "./js/page.js") {
			echo file_get_contents(JS_PATH . "global.js");
			exit();
		} else if (!file_exists(".$filename")) throw new Exception("Error Processing Request", 1);

		echo file_get_contents(".$filename");
	} catch (\Throwable $th) {
		response404();
	}
}
/**
 * Создаёт CSS бандл 
 * @param string $path путь к папке
 * @return string
 */
function genCSSBundle(string $path): string
{
	$bundle = file_get_contents(CSS_PATH . "global.css");
	return $bundle . "\n" . getFilesContent($path);
}
/**
 * Создаёт JS бандл 
 * @param string $path путь к папке
 * @return string
 */
function genJSBundle(string $path): string
{
	$bundle = file_get_contents(JS_PATH . "global.js");

	return $bundle . "\n" . getFilesContent($path);
}
/**
 * Получает контент из [name] папки и подпапок
 * @param string $path путь к папке
 * @return string
 */
function getFilesContent($path)
{
	$filesData = "";
	$dirFiles = scandir($path);
	foreach ($dirFiles as $item) {
		if ($item != "." && $item != "..") {
			$filepath = "$path/$item";
			if (is_file($filepath)) {
				$filesData = $filesData . file_get_contents("$filepath") . "\n";
			} else {
				$filesData = $filesData . "\n" . getFilesContent("$filepath");
			}
		}
	}
	return $filesData;
}

/**
 * Возвращает код ответа 404
 */
function response404(): void
{
	http_response_code(404);
}

/**
 * Функция которая проверяет содержит ли запрос обращение к файлу
 * @param string $filename - путь к файлу и это путь должен начинаться с `/`ё
 */
function thisIsSourceFile(string $filename): bool
{
	if (strpos($filename, ".") == 0) {
		return false;
	} else {
		if (isset(PAGE_WRAPPER[2])) {
			return true;
		} else {
			$type = explode(".", $filename)[1]; // Тип файла
			return MIME_TYPES[$type] ? true : false;
		}
	}
}
/**
 * Возвращает `Content-type` для ответа пользователю
 * @param string $filename - путь к файлу и это путь должен начинаться с `/`
 * @return string - MIME_TYPE | `text/plain`
 */
function getFileContentType(string $filename): string
{

	$type = explode(".", $filename)[1]; // тип файла из запроса
	return MIME_TYPES[$type] ? MIME_TYPES[$type] : "text/plain";
}


/**
 * @param string $URI - путь 
 * @param callable $func - что будет происходить обращении по такому пути
 */
function newRout(string $URI, callable $func)
{
	$_SERVER["ROUTS"][$URI] = $func;
}
/**
 * Создает страницу
 * @param string $pageContainer имя контейнера `[name]`
 * @include "./router/Page.php"
 */
function generatePage(string $pageContainer)
{
	$css = "./css/[${pageContainer}]/";
	$javascript = "./js/[${pageContainer}]";
	$contentDir = "./pages/[${pageContainer}]";
	if (is_dir($css)) {
		$css = "./css/${pageContainer}/page.css";
	} else {
		$css = "./css/page.css";
	}
	if (is_dir($javascript)) {
		$javascript = "./js/${pageContainer}/page.js";
	} else {
		$javascript = "./js/page.js";
	}
	$content = $contentDir . "/page.php";

	include_once("./router/Page.php");
}
