<?php

declare(strict_types=1);

global $isType;
$isType = array(
	"string" => function (string $str): bool {
		return is_string($str);
	},
	"number" => function (string $str): bool {
		return preg_match('/^-?\d+(\.\d+)?$/', $str) === 1;
	},
	"json" => function (string $str): bool {
		json_decode($str);
		return json_last_error() === JSON_ERROR_NONE;
	}
);


define("CSS_PATH", "./css/");
define("JS_PATH", "./js/");
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
// define("WRAPPER_DIRS", (function () {
// 	$preg = "/\[(.*?)\]/";
// 	$css = 


// 	return array(
// 		"CSS" => array(),
// 		"JS" => array()
// 	);
// })());

/**
 * 
 */
function responseContentType(string $str): void
{
	$resStr = null;
	if (is_file($str)) {
		$resStr = getFileContentType($str);
	} else {
		if (!isset(MIME_TYPES[$str])) {
			$resStr = "text/plain";
		} else {
			$resStr = MIME_TYPES[$str];
		}
	}
	header("Content-type: " . $resStr);
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
			$i = 0;
			$count = count(SOURCE_DIRS["css"]);
			while ($i < $count) {
				$item = SOURCE_DIRS["css"][$i];
				if ($item === "[" . PAGE_WRAPPER[2] . "]") {
					echo genCSSBundle(CSS_PATH . $item);
					exit();
				}
				$i++;
			}
		} else if ($reqContentType == MIME_TYPES['js']) {
			$i = 0;
			$count = count(SOURCE_DIRS["js"]);
			while ($i < $count) {
				$item = SOURCE_DIRS["js"][$i];
				if ($item === "[" . PAGE_WRAPPER[2] . "]") {
					echo genJSBundle(JS_PATH . $item);
					exit();
				}
				$i++;
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
	$count = count($dirFiles);
	$i = 0;
	while ($i < $count) {
		$item = $dirFiles[$i];
		if ($item != "." && $item != "..") {
			$filepath = "$path/$item";
			if (is_file($filepath)) {
				$filesData = $filesData . file_get_contents("$filepath") . "\n";
			} else {
				$filesData = $filesData . "\n" . getFilesContent("$filepath");
			}
		}
		$i++;
	}
	return $filesData;
}

/**
 * Возвращает код ответа 404
 */
function response404(callable $fn = null): void
{
	http_response_code(404);
	if (is_callable($fn)) {
		$fn();
	}
	exit();
}

/**
 * Функция которая проверяет содержит ли запрос обращение к файлу
 * @param string $filename - путь к файлу и это путь должен начинаться с `/`
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
 * Добавляет роут в $_SERVER["ROUTS"] с указанием метода и пути
 * @param string $URI - путь 
 * @param callable $func - что будет происходить обращении по такому пути
 */
// // function newRout(string $URI, callable $func)
// // {
// 	// $_SERVER["ROUTS"][$URI] = $func;
// // }
/**
 * Парсит query string запроса и добавляет их в массив если есть ошибка возвращает false и выводит ошибку
 * @param string $rout 
 */
function parseRoutQuery(string $rout)
{
	try {
		if (
			preg_match_all('/(\w+)=/', $rout, $matches) && // Находит ключи
			preg_match_all('/=\{(\w+)\}/', $rout, $param) // Находит значения
		) {
			$keys = $matches[1] ?? [];
			$values = $param[1] ?? [];

			if (count($keys) !== count($values)) {
				throw new Exception('Не совпадают по количеству', 1);
			}

			$arr = array_combine($keys, $values);

			if ($arr) {
				return $arr;
			} else throw new Exception('Не верная запись', 2);
		} else {
			return false;
		}
	} catch (\Throwable $th) {
		echo '<pre>';
		echo 'Error query: ' . $th->getMessage() . ":\n";
		$trace = $th->getTrace()[0];
		echo "\t{$trace['file']} on line {$trace['line']}\n";
		echo "\t{$trace['function']}({$trace['args'][0]});";
		echo '</pre>';
		return false;
	}
}

interface IRout
{
	/**
	 * @param string $URI путь 
	 * @param callable $func что будет происходить при обращении по такому пути
	 */
	static public function get(string $URI, callable $func);
	/**
	 * @param string $URI путь 
	 * @param callable $func что будет происходить при обращении по такому пути
	 */
	static public function post(string $URI, callable $func);
	/**
	 * @param string $URI путь 
	 * @param callable $func что будет происходить при обращении по такому пути
	 */
	static public function put(string $URI, callable $func);
	/**
	 * @param string $URI путь 
	 * @param callable $func что будет происходить при обращении по такому пути
	 */
	static public function delete(string $URI, callable $func);
};

class Rout implements IRout
{

	private const METHODS = ['GET', 'POST', 'PUT', 'DELETE'];
	/**
	 * @param string $URI путь 
	 * @param callable $func что будет происходить обращении по такому пути
	 * @param int $method 0 = GET; 1 = POST; 2 = PUT; 3 = DELETE;
	 */
	static private function newRout(string $URI, callable $func, int $method): void
	{
		if (count(explode("?", $URI)) > 1) {
			$_SERVER["ROUTS"][self::METHODS[$method]][getUriPath($URI) . '?'] = function ($args) use ($URI, $func) {
				global $isType;
				$query = parseRoutQuery($URI);
				foreach ($_GET as $key => $value) {
					if (!isset($query[$key]) || $isType[$query[$key]]($value) !== true) {
						response404(function () {
							header("Content-type: " . MIME_TYPES["txt"]);
							echo 123;
						});
					}
				}
				$func($args);
			};
		} else {
			$_SERVER["ROUTS"][self::METHODS[$method]][getUriPath($URI)] = $func;
		}
	}
	static public function get(string $URI, callable $func)
	{
		self::newRout($URI, $func, 0);
	}
	static public function post(string $URI, callable $func)
	{
		self::newRout($URI, $func, 1);
	}
	static public function put(string $URI, callable $func)
	{
		self::newRout($URI, $func, 2);
	}
	static public function delete(string $URI, callable $func)
	{
		self::newRout($URI, $func, 3);
	}
}


// TODO: function parseGetInURI(string $uri): array {}
/**
 * Создает страницу
 * @param string $pageContainer имя контейнера `[name]`
 * @include "./router/Page.php"
 */
function generatePage(string $pageContainer)
{
	$cssDir = "./css/[${pageContainer}]/";
	$jsDir = "./js/[${pageContainer}]";
	$contentDir = "./pages/[${pageContainer}]";
	// if () {
	$css = is_dir($cssDir) ? "/css/${pageContainer}/page.css" : "/css/page.css";
	$javascript = is_dir($jsDir) ? "/js/${pageContainer}/page.js" : "/js/page.js";
	// } else {
	// $css = ;
	// }
	// if () {
	// 	$javascript = ;
	// } else {
	// 	$javascript = ;
	// }
	$content = $contentDir . "/page.php";

	include_once("./router/Page.php");
}
