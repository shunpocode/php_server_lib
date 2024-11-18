<?php

Rout::get("/", function () {
	echo "<a href='/?name=Maxim&age=20'>/</a>";
});
Rout::get("/?name={string}&age={number}", function ($query) {
	generatePage("start");
});
Rout::get("/test", function () {
	generatePage("test");
});
Rout::post("/?name={string}&age={number}", function ($query) {
	responseContentType('json');
	echo json_encode($query);
});

// newRout("/", function ($v) {
// 	generatePage("start");
// });
// newRout("/test", function () {
// 	$testString = "test fkfkfk ssss [ttt], 12313213 sadfdfdasoasgnaoso [123[123[[32132131[]123[123]";
// 	$preg = "/\[(.*?)\]/";
// 	// $arr = array();
// 	preg_match_all($preg, $testString, $arr);
// 	echo '<pre>';
// 	print_r($arr);
// 	echo '</pre>';
// 	// generatePage("test");
// });
// newRout("/ttt/about", function () {
// 	generatePage("about");
// });
