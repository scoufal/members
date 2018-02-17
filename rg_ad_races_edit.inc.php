<?php /* adminova stranka - editace zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Kalendář závodů - Editace závodů');
?>
<CENTER>
<script language="javascript">
<!-- 
/*	"menubar=yes,status=yes,width=600,height=600"	*/

	function confirm_delete()
	{
		return confirm('Opravdu chcete smazat tento zavod?');
	}

	javascript:set_default_size(800,600);
//-->
</script>
<?

require_once ('./common_race.inc.php');
require_once ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races - default is OFF
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD);

if (!$g_is_release)
{	// pri debug zobrazit
	@$vysledek=MySQL_Query("SELECT id,datum,typ,typ0,datum2,odkaz,nazev,vicedenni,kategorie,oddil,misto,modify_flag,cancelled FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum , datum2, id');
}
else
{
	@$vysledek=MySQL_Query("SELECT id,datum,typ,typ0,datum2,odkaz,nazev,vicedenni,kategorie,oddil,misto,cancelled FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum, datum2, id');
}

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ akce");
$data_tbl->set_header_col_with_help($col++,'S',ALIGN_CENTER,"Sport");
$data_tbl->set_header_col_with_help($col++,'W',ALIGN_CENTER,"Web závodu");
$data_tbl->set_header_col_with_help($col++,'Kat',ALIGN_CENTER,"Zadané kategorie");
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
if (!$g_is_release)
{	// pri debug zobrazit
	$data_tbl->set_header_col($col++,'Změny',ALIGN_CENTER);
}
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i = 1;
$brk_tbl = false;
$old_year = 0;
if($vysledek && ($num_rows = mysql_num_rows($vysledek)) > 0)
{
	show_link_to_actual_race($num_rows);

	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$row = array();
		
		$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

		$prefix = ($race_is_old) ? '<span class="TextAlertExpLight">' : '';
		$suffix = ($race_is_old) ? '</span>' : '';

		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		$row[] = $prefix.$datum.$suffix;
		$row[] = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".$prefix.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).$suffix."</A>";
		$row[] = $prefix.GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']).$suffix;
		$row[] = $prefix.$zaznam['oddil'].$suffix;
		$row[] = GetRaceType0($zaznam['typ0']);
		$row[] = GetRaceTypeImg($zaznam['typ']);
		$row[] = GetRaceLinkHTML($zaznam['odkaz']);
		$row[] = (strlen($zaznam['kategorie']) > 0) ? 'A' :'<span class="TextAlertBold">N</span>';
		$row [] = "<A HREF=\"javascript:open_win('./race_edit.php?id=".$zaznam['id']."','')\">Edit</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_kat.php?id=".$zaznam['id']."','')\">Kategorie</A>&nbsp;/&nbsp;<A HREF=\"./race_del_exc.php?id=".$zaznam["id"]."\" onclick=\"return confirm_delete();\" class=\"Erase\">Smazat</A>";
		if (!$g_is_release)
		{	// pri debug zobrazit
			$row[] = GetModifyFlagDesc($zaznam['modify_flag']);
		}
		if (!$brk_tbl && $zaznam['datum'] >= GetCurrentDate())
		{
			if($i != 1)
				echo $data_tbl->get_break_row()."\n";
			$brk_tbl = true;
		}
		else if($i != 1 && Date2Year($zaznam['datum']) != $old_year)
		{
				echo $data_tbl->get_break_row(true)."\n";
		}

		echo $data_tbl->get_new_row_arr($row)."\n";
		$i++;
		$old_year = Date2Year($zaznam['datum']);
	}
}

echo $data_tbl->get_footer()."\n";

echo '<BR><hr><BR>';

echo("<A HREF=\"javascript:open_win('./race_new.php?type=0','')\">Vytvořit nový závod</A><br>");
echo("<A HREF=\"javascript:open_win('./race_new.php?type=1','')\">Vytvořit nový vícedenní závod</A><br>");

?>
</CENTER>
