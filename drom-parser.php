<?php

$baseUrl = "https://irkutsk.drom.ru/";
$addQ = "&maxprice=360000&transmission=2&pts=2&damaged=2&unsold=1";

$links = [
  "lanser_9" => "mitsubishi/lancer/?generation_number=9&w=2",
  "mazda3_bk" => "mazda/mazda3/?generation_number=1&w=2",
  "focus_2" => "ford/focus/?generation_number=2&w=2",
  "nissan_note" => "nissan/note/?generation_number=1",
  "nissan_teana" => "nissan/teana/?generation_number=1",
];

$fileName = dirname(__FILE__) . "/" . (new \DateTime())->format('Y-m-d') . ".txt";
file_put_contents($fileName, "");

foreach ($links as $k => $v) {
  parseCatalog($baseUrl . $v . $addQ);
}

function parseCatalog($url) {
  $content = file_get_contents($url);
  preg_match_all("|<a\s+name=\"\d+\"\n\s*href=\"([^\"]+)\"\n\s*class=\"b-advItem[^\"]*\"\n>|", $content, $m);
  
  foreach($m[1] as $v) {
    echo($v . PHP_EOL);
    parseAd($v);
    sleep(2);
  }
}

function parseAd($url) {
  $content = file_get_contents($url);

  $title = rx0("|<h1[^>]+>([^<]+)</h1>|", $content);
  $title = preg_replace("|[\s\n]+|", " ", $title);

  $descr = rx0("|<p><span class=\"b-text-gray\">Дополнительно:</span> (.*?)</p>|s", $content);
  $descr = preg_replace("|<br />|", "\n", $descr);
  $descr = preg_replace("|(\s*\n\s*)+|s", "\n", $descr);

  $price = rx0("|>([^>]+)<span class='rouble'>|", $content);
  $price = preg_replace("|[^\d]+|", "", $price);

  $vin = rx0("|b-text_color_gray-dark\">VIN:</span>\n\s*<span>([^<]+)</span>|", $content);
  if (!$vin)
    $vin = rx0("|b-text_color_gray-dark\">Номер кузова:</span>\n\s*<span>([^<]+)</span>|", $content);

  $str = "URL $url\n";
  $str .= "TITLE $title\n";
  $str .= "PRICE $price\n";
  $str .= "VIN $vin\n";
  $str .= "$descr\n\n";

  $fileName = dirname(__FILE__) . "/" . (new \DateTime())->format('Y-m-d') . ".txt";
  file_put_contents($fileName, $str, FILE_APPEND);
}

function rx0($rx, $str) {
  preg_match_all($rx, $str, $m);
  return (isset($m[1][0])) ? $m[1][0] : "";
}