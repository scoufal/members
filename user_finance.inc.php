<?php /* finance -  show exact user finance */ ?>

<?
@$vysledek_historie=query_db("select fin.id fin_id, fin.id_users_editor id_editor, rc.nazev zavod_nazev, rc.cancelled zavod_cancelled, from_unixtime(rc.datum,'%Y-%c-%e') zavod_datum, fin.amount amount, fin.note note, us.sort_name name, fin.date `date` from ".TBL_FINANCE." fin 
		left join ".TBL_USER." us on fin.id_users_editor = us.id
		left join ".TBL_RACE." rc on fin.id_zavod = rc.id
		where fin.id_users_user = ".$user_id." and fin.storno is null order by fin.date desc, fin.id desc");

//vytazeni jmena uzivatele a typu prispevku
$vysledek_user_name=query_db("select us.sort_name name, ft.nazev ft_nazev from ".TBL_USER." us LEFT JOIN ".TBL_FINANCE_TYPES." ft ON us.finance_type = ft.id where us.id = ".$user_id);
$zaznam_user_name=mysqli_fetch_array($vysledek_user_name);

DrawPageSubTitle('Historie účtu pro člena: '.$zaznam_user_name['name']);

if ($zaznam_user_name['ft_nazev'] != null)
{
	DrawPageSubTitle('Typ oddílového příspěvku člena: '.$zaznam_user_name['ft_nazev']);
}

require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum transakce',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Závod',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Datum závodu',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Částka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Zapsal',ALIGN_LEFT);
if ($g_enable_finances_claim)
	$data_tbl->set_header_col($col++,'Reklamace',ALIGN_LEFT);
if (!isset($finance_readonly) && IsLoggedFinance())
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_LEFT);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_amount = 0;
$i = 0;
$year = 0;
while ($zaznam=mysqli_fetch_array($vysledek_historie))
{
	$row = array();
	$datum = SQLDate2String($zaznam['date']);
	$row[] = $datum;
	$row[] = ($zaznam['zavod_nazev'] == null) ? '-':GetFormatedTextDel($zaznam['zavod_nazev'], $zaznam['zavod_cancelled']);
	$row[] = ($zaznam['zavod_nazev'] == null) ? '-':formatDate($zaznam['zavod_datum']);
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['note'];
	$row[] = ($zaznam['name'] == null) ? "<<<".$zaznam['id_editor'].">>>":$zaznam['name'];
	if ($g_enable_finances_claim)
		$row[] = '<A HREF="javascript:open_win(\'./claim.php?payment_id='.$zaznam['fin_id'].'\',\'\')">Problém?</A>';
	if (!isset($finance_readonly) && IsLoggedFinance())
		$row[]=" <a href=\"?change=change&trn_id=".$zaznam['fin_id']."\">Změnit</a>&nbsp;/&nbsp;<a href=\"?storno=storno&trn_id=".$zaznam['fin_id']."\">Storno</a>";
	
	$sum_amount += $zaznam['amount'];
	
	//pridani roku do class kvuli moznosti schovat starsi roky z vypisu
	$row_date = substr($zaznam['date'],0,4);
	if ($year != $row_date) {
		$year = $row_date;
		$odkaz = "<button onclick='toggle_display_by_group(\"$year\")'>Histore transakcí pro rok $year</button>";
		echo $data_tbl->get_info_row($odkaz)."\n";
	}

	$attrs = [ 'data-group' => $year ];
	if ($year < date("Y"))
		$attrs['style'] = "display:none";

	echo $data_tbl->get_new_row_arr($row, $attrs)."\n";
	$i++;
}
if ($i > 0)
	echo $data_tbl->get_break_row()."\n";

$row = array();
$row[] = '';
$row[] = "Konečný zůstatek";
$row[] = '';
$sum_amount<0?$class="red":$class="";
$row[] = "<span class='amount$class'>".$sum_amount."</span>";
echo $data_tbl->get_new_row_arr($row)."\n";

//--------------pridani vypisu stavu kont sverencu pro rodice
$nch_query = "select u.id, u.sort_name, ifnull(sum(f.amount),0) as sum from `".TBL_USER."` u left join `".TBL_FINANCE."` f on u.id = f.id_users_user where f.storno is null and (u.chief_pay = $user_id) group by u.id;";
$nch_result = query_db($nch_query);
if (mysqli_num_rows($nch_result))
{
	while ($nch_record = mysqli_fetch_array($nch_result))
	{
		$row = array();
		$row[] = '';
		$row[] = $nch_record['sort_name'];
		$row[] = '';
		$nch_record['sum']<0?$class="red":$class="";
		$row[] = "<span class='amount$class'>".$nch_record['sum']."</span>";
		$sum_amount += $nch_record['sum'];
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	echo $data_tbl->get_break_row()."\n";
	//vypis konecneho zustatku vcetne sverencu
	$row = array();
	$row[] = '';
	$row[] = "Kompletní zůstatek";
	$row[] = '';
	$sum_amount<0?$class="red":$class="";
	$row[] = "<span class='amount$class'>".$sum_amount."</span>";
	echo $data_tbl->get_new_row_arr($row)."\n";
	//-------------------------------------------------------------
}
echo $data_tbl->get_footer()."\n";


//------------ formular pro prevod financi mezi cleny
$return_url = full_url();
$return_url = parse_url($return_url, PHP_URL_QUERY);
require_once 'user_finance_transfer_form.inc.php';



?>
