<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Členská základna - Administrace');
?>
<CENTER>

<script language="JavaScript">
<!--
function confirm_delete(name) {
	return confirm('Opravdu chcete smazat člena oddílu ? \n Jméno člena : "'+name+'" \n Člen bude nenávratně smazán !!');
}

function confirm_entry_lock(name) {
	return confirm('Opravdu chcete zamknout členu oddílu možnost se přihlašovat? \n Jméno člena : "'+name+'" \n Člen nebude mít možnost se přihlásit na závody!');
}

function confirm_entry_unlock(name) {
	return confirm('Opravdu chcete odemknout členu oddílu možnost se přihlašovat ? \n Jméno člena : "'+name+'"');
}

-->
</script>

<?
require_once "./common_user.inc.php";
require_once('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=700&subid=1',true);
$sub_query = $sc->get_sql_string();

$query = "SELECT id,prijmeni,jmeno,reg,hidden,entry_locked FROM ".TBL_USER.$sub_query;
@$vysledek=MySQL_Query($query);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require_once('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
$data_tbl->set_header_col_with_help($col++,'Účet',ALIGN_CENTER,"Stav a existence účtu");
$data_tbl->set_header_col_with_help($col++,'Přihl.',ALIGN_CENTER,"Možnost přihlašování se člena na závody");
$data_tbl->set_header_col_with_help($col++,'Práva',ALIGN_CENTER,"Přiřazená práva (zleva) : novinky, přihlašovatel, trenér, malý trenér, správce, finančník");
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
//echo $data_tbl->get_header_row()."\n";

$data_tbl->set_sort_col(1,$sc->get_col_content(0));
$data_tbl->set_sort_col(3,$sc->get_col_content(1));
//echo $data_tbl->get_sort_row()."\n";
echo $data_tbl->get_header_row_with_sort()."\n";

$i=1;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$row = array();
	$row[] = $i++;
	$row[] = $zaznam['prijmeni'];
	$row[] = $zaznam['jmeno'];
	$row[] = RegNumToStr($zaznam['reg']);
	$acc = '';
	$acc_r = '<code>';
	if ($zaznam["hidden"] != 0) 
		$acc = '<span class="WarningText">H </span>';
	$val=GetUserAccountId_Users($zaznam['id']);
	if ($val)
	{
		$vysl2=MySQL_Query("SELECT locked, policy_news, policy_regs, policy_mng, policy_adm,policy_fin FROM ".TBL_ACCOUNT." WHERE id = '$val'");
		$zaznam2=MySQL_Fetch_Array($vysl2);
		if ($zaznam2 != FALSE)
		{
			if ($zaznam2['locked'] != 0) 
				$acc .= '<span class="WarningText">L </span>';
			$acc .= "Ano";
			$acc_r .= ($zaznam2['policy_news'] == 1) ? 'N ' : '. ';
			$acc_r .= ($zaznam2['policy_regs'] == 1) ? 'P ' : '. ';
			$acc_r .= ($zaznam2['policy_mng'] == _MNG_BIG_INT_VALUE_) ? 'T ' : '. ';
			$acc_r .= ($zaznam2['policy_mng'] == _MNG_SMALL_INT_VALUE_) ? 't ' : '. ';
			$acc_r .= ($zaznam2['policy_adm'] == 1) ? 'S ' : '. ';
			$acc_r .= ($zaznam2['policy_fin'] == 1) ? 'F' : '.';
		}
		else
		{
			$acc .= '-';
			$acc_r .= '. . . . . .';
		}
	}
	else
	{
		$acc .= '-';
		$acc_r .= '. . . . . .';
	}
	$row[] = $acc;
	if ($zaznam['entry_locked'] != 0)
		$row[] = '<span class="WarningText">Ne</span>';
	else
		$row[] = '';
	$row[] = $acc_r.'</code>';
	$action = '<A HREF="./user_edit.php?id='.$zaznam['id'].'&cb=700">Edit</A>';
	$action .= '&nbsp;/&nbsp;';
	$action .= '<A HREF="./user_login_edit.php?id='.$zaznam["id"].'&cb=700">Účet</A>';
	$action .= '&nbsp;/&nbsp;';
	$action .= '<A HREF="./user_del_exc.php?id='.$zaznam["id"]."\" onclick=\"return confirm_delete('".$zaznam["jmeno"].' '.$zaznam["prijmeni"]."')\" class=\"Erase\">Smazat</A>";
	$lock = ($zaznam['entry_locked'] != 0) ? 'Odemknout' : 'Zamknout';
	$lock_onclick = ($zaznam['entry_locked'] != 0) ? 'confirm_entry_unlock' : 'confirm_entry_lock';
	$action .= '&nbsp;/&nbsp;';
	$action .= '<A HREF="./user_lock2_exc.php?gr_id='._SMALL_ADMIN_GROUP_ID_.'&id='.$zaznam['id'].'" onclick="return '.$lock_onclick.'(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">'.$lock.'</A>';
	$row[] = $action;
	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";

echo '<BR><BR>';
echo '(Červené <span class="WarningText">H</span> značí skrytého člena. Tj. vidí ho jen admin.)<BR>';
echo '(Červené <span class="WarningText">L</span> značí že účet je zablokován. Tj. nejde se na něj přihlásit.)<BR>';
echo '<BR><hr><BR>';

require_once "./user_new.inc.php";
?>
<BR>
</CENTER>