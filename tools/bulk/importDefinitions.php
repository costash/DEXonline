<?
require_once('../phplib/util.php');

$shortopts = "f:u:s:t:hvid"; 
$options = getopt($shortopts);

function HELP() {
	exit("
Usage: From tools/ directory run: 
	php bulk/importDefinitions.php options

Options:
	-f fileName (mandatory)
	-u userId (mandatory)
	-s sourceId (mandatory)
	-t status (mandatory: 0 - active, 1 - temporary, 2 - deleted)
	-i = use inflections for multilexem entries
	-d = dry run
	-h = help
	-v = verbose

Example:
	php importDefinition.php -f definitions.txt -u 471 -s 19 -t 0

");
}

function CHECK($option, $c) {
	if (!is_array($option)) 
		exit("Error reading options\n");

	if (!isset($option[$c])) {
		echo "Missing mandatory option $c\n";
		HELP();
	}
}

/*** check options ***/

if(isset($options['h'])) HELP();

CHECK($options, 'u');
$userId = $options['u']; 

CHECK($options, 's');
$sourceId = $options['s'];

CHECK($options, 'f');
$fileName = $options['f'];
if (!file_exists($fileName)) exit("Error: file $fileName doesn't exist!\n");

CHECK($options, 't');
$status = $options['t'];

$verbose = false;
if (isset($options['v'])) {
	$verbose = true;
}

$allowInflected = false;
if (isset($options['i'])) {
	$allowInflected = true;
}

$dryrun = false;
if (isset($options['d'])) {
	$dryrun = true;
}

/*** main loop ***/
if($verbose) echo("Everything OK, start processing...\n");

$lines = file($fileName);
if($verbose) echo("File read. Start inserting the definitions...\n");

$i = 0;
while ($i < count($lines)) {
    $def = $lines[$i++];
    preg_match('/^@([^@]+)@/', $def, $matches);
    $lname = preg_replace("/[!*'^1234567890]/", "", $matches[1]);

	if($verbose) echo(" * Inserting definition for '$lname'...\n");
    $definition = new Definition();
    $definition->userId = $userId;
    $definition->sourceId = $sourceId;
    $definition->status = $status;
    $definition->internalRep = text_internalizeDefinition($def);
    $definition->htmlRep = text_htmlize($definition->internalRep);
    $definition->lexicon = text_extractLexicon($definition);
    $definition->save();
    if($verbose) echo("\tAdded definition {$definition->id} ({$definition->lexicon})\n");

    $lname = addslashes(text_formatLexem($lname));
	$names = preg_split("/[\s,]+/", $lname);
	foreach ($names as $name) {
		if($verbose) echo("\t * Process part: {$name}\n");

		$lexems = db_find(new Lexem(), "form = '{$name}'");
		if (!count($lexems)) {
		  $lexems = db_find(new Lexem(), "formNoAccent = '{$name}'");
		}

		if ($allowInflected) {
			if (!count($lexems)) {
				$lexems = db_getObjects(new Lexem(), db_execute("select l.* from Lexem l, InflectedForm i where l.id=i.lexemId and i.formNoAccent='{$name}'"));
				if ( count($lexems) ) {
					if($verbose) echo("\t\tFound inflected form {$name} for lexem {$lexems[0]->id} ({$lexems[0]->form})\n");
				}
			}
		}

		# procedura de refolosire a lexemului sau de regenerare
		if (count($lexems)) {
		  // Reuse existing lexem.
		  $lexem = $lexems[0];
		  if($verbose) echo("\t\tReusing lexem {$lexem->id} ({$lexem->form})\n");
		} else {
			if($verbose) echo("\t\tCreating a new lexem for name {$name}\n");
			if (!$dryrun) {
			  // Create a new lexem.
			  $lexem = new Lexem($name, 'T', '1', '');
			  $lexem->save();
			  $lexem->regenerateParadigm();
			  if($verbose) echo("\t\tCreated lexem {$lexem->id} ({$lexem->form})\n");
			}
		}

		# procedura de asociere a definiției cu lexemul de mai sus
		if($verbose) echo("\t\tAssociate lexem {$name} ({$lexem->id}) to definition ({$definition->id})\n");
		if (!$dryrun) {
			LexemDefinitionMap::associate($lexem->id, $definition->id);
		}
	}
}

?>
