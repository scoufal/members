<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (!defined('SYSTEM_VERSION_INCLUDED'))
{
	define('SYSTEM_VERSION_INCLUDED', 1);

	define('SYSTEM_NAME','members');
	define('SYSTEM_AUTORS','Arno�t a Kenia');

	function GetCodeVersion()
	{
		//pro zmenu podverze staci tento soubor komitnout ;)
		$actualVersion = '$LastChangedRevision$';
		$actualVersion = explode(' ', $actualVersion);
		return "v2.3.2.$actualVersion[1] dbg";
	}

	function GetDevelopYears()
	{
		return "2002-2013";
	}
}
?>