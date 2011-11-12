<?php
require_once("../phplib/util.php");
util_assertNotMirror();
?>

<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Semnalare greșeală de tipar</title>
</head><body onLoad="if (document.frmFlag) document.frmFlag.problem.focus()">

<?php

// Parse or initialize the GET/POST arguments
$problem = util_getRequestParameter('problem');
$definitionId = util_getRequestIntParameter('definitionId');
$definition = Definition::get_by_id($definitionId);

if ($problem && $definitionId) {
  $typo = Model::factory('Typo')->create();
  $typo->definitionId = $definitionId;
  $typo->problem = $problem;
  $typo->save();

  echo '<font color=red><b>Eroarea a fost semnalată. Vă mulțumim!</b></font>';
} else {
  echo 'Vă rugăm să precizați care este greșeala de tipar (maxim 200 ' .
    'caractere). NU folosiți acest formular pentru a raporta greșeli de ' .
    'ordin semantic.<br/><br/>';
  echo '<form name="frmFlag" method="post" action="greseala">';
  echo 'Greșeala: <input type="text" name="problem" maxlength=200 '.
    'size=50><p>';
  echo '<input type="submit" value=" Trimite ">';
  echo "<input type=\"hidden\" name=\"definitionId\" value=\"$definitionId\">";
  echo '</form><p>';

  echo "Definiția:<br/>";
  echo $definition->htmlRep;
}

?>
