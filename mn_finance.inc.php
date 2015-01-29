<?php /* trener stranka - seznam sverencu pro finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance členů');
?>
<CENTER>
<script language="javascript">
<!-- 
	javascript:set_default_size(800,800);
//-->
</script>
<?
include "./common_user.inc.php";
include('./csort.inc.php');

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url('index.php?id=500&subid=10',true);
$sub_query = $sc->get_sql_string();

$query = 'SELECT u.id,prijmeni,jmeno,reg,hidden,lic,lic_mtbo,lic_lob, ifnull(sum(f.amount),0) sum_amount, ft.nazev FROM '.TBL_USER.' u 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		left join '.TBL_FINANCE.' f on u.id=f.id_users_user where f.storno is null 
		group by u.id '.$sub_query;


$vysledek=MySQL_Query($query);

$i=1;
if ($vysledek != FALSE && mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
	$data_tbl->set_header_col_with_help($col++,'Fin.st.',ALIGN_CENTER,"Aktuální finanční stav");
	$data_tbl->set_header_col_with_help($col++,'Typ o.p.',ALIGN_CENTER,"Typ oddílových příspěvků");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo $data_tbl->get_header_row_with_sort()."\n";
	
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		if (!$zaznam['hidden'])
		{
			$row = array();
			$row[] = $i++;
			$row[] = $zaznam['prijmeni'];
			$row[] = $zaznam['jmeno'];
			$row[] = RegNumToStr($zaznam['reg']);
			$zaznam['sum_amount']<0?$class="red":$class="";
			$row[] = "<span class='amount$class'>".$zaznam['sum_amount']."</span>";
			$row[] = ($zaznam['nazev'] != null)? $zaznam['nazev'] : '-';
			$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['id'].'\',\'\')">Přehled</A>';
			$row[] = $row_text;
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}
	echo $data_tbl->get_footer()."\n";
}

?>
<BR>
</CENTER>