<?php
require_once("../../phplib/util.php");

$query = util_getRequestParameter('q');
$parts = preg_split('/\(/', $query, 2);
$name = AdminStringUtil::internalizeWordName(trim($parts[0]));
$field = StringUtil::hasDiacritics($name) ? 'formNoAccent' : 'formUtf8General';

if (count($parts) == 2) {
  $description = trim($parts[1]);
  $description = str_replace(')', '', $description);
  $lexems = Model::factory('Lexem')->where($field, $name)->where_like('description', "{$description}%")
    ->order_by_asc('formNoAccent')->order_by_asc('description')->limit(10)->find_many();
} else {
  $lexems = Model::factory('Lexem')->where_like($field, "{$name}%")->order_by_asc('formNoAccent')->limit(10)->find_many();
}

foreach ($lexems as $l) {
  print "{$l}\n";
}

?>
