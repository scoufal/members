<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.6.652';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupcu pro definice plateb
$sql[1] = "CREATE TABLE `" . TBL_PAYRULES . "` (
 `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `typ` enum('ob','mtbo','lob','jine','trail') COLLATE utf8_czech_ci,
 `typ0` enum('Z','T','S','V','N','J') COLLATE utf8_czech_ci,
 `druh_platby` enum('C','P','R') COLLATE utf8_czech_ci,
 `termin` tinyint(1) unsigned COMMENT 'Platný termín pro pozitivní hodnoutu, první platný pro negativní',
 `zebricek` int(10) unsigned,
 `finance_type` int(10) unsigned,
 `platba` int(10)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='podminene predpisy plateb uzivate'";

//#############################################################################

require_once ('action.inc.php');
?>
