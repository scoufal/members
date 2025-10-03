<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");
require_once ("ctable.inc.php");
if (!IsLoggedFinance()) {
    header("location: ".$g_baseadr."error.php?code=21");
    exit;
}
require_once("cfg/_globals.php");
require_once("cfg/race_enums.php");

db_Connect();
$is_new = array_key_exists('new',$_REQUEST);

$ids = (isset($ids) && is_numeric($ids)) ? (int)$ids : 0;

$sql_query = 'SELECT * FROM '.TBL_FINANCE_TYPES;
@$finTypes = query_db($sql_query);

if ( !$is_new ) {
    @$zaznam = mysqli_fetch_array($vysledek);
}

require_once ("./header.inc.php");
require_once ("./common.inc.php");
require_once ("./common_fin.inc.php");

DrawPageTitle($is_new ? 'Nová definice platby' : 'Editace definice platby');
?>

<TABLE cellpadding="0" cellspacing="0" border="0">

<form method="post" action="payment_save.php">
<input type="hidden" name="id" value="<?= $id ?>">

<!-- Sport -->
<TR>
    <TD align="right" valign="top">Sport</TD>
    <TD>
        <div class="form-row">
<?
    $cbr = new CheckboxRow ( '', 'typ', true, 'typ_' );
    for ($ii=0; $ii < $g_racetype_cnt; $ii++) {
        $cbr->addEntry ( $g_racetype[$ii]['nm'], null, $g_racetype[$ii]['enum'], !$is_new && ( $zaznam['typ'] === $g_racetype[$ii]['enum'] ), true );
    }
    echo $cbr->render();
?>
        </div>
    </TD>
</TR>

<!-- Typ akce -->
<TR>
    <TD align="right" valign="top">Typ akce</TD>
    <TD>
<?
    $cbr = new CheckboxRow ( '', 'typ0', true, 'typ0_' );

    foreach ($g_racetype0 as $key => $value) {
        $cbr->addEntry ( $value, null, $key, !$is_new && ( $zaznam['typ0'] === $key ), true );
    }
    echo $cbr->render();
?>
    </TD>
</TR>

<!-- Termín -->
<TR>
    <TD align="right" valign="top">Termín</TD>
    <TD>
<?
    $cbr = new CheckboxRow ( '', 'termin', true, 'termin_' );
    for ($t=1; $t<=5; $t++) {
        $cbr->addEntry ( $t, null, $t, !$is_new && ( $zaznam['termin'] === $t ), true );
    }
    echo $cbr->render();    
?>
    </TD>
</TR>

<!-- Žebříček -->
<TR>
    <TD align="right" valign="top">Žebříček</TD>
    <TD>
<?
    $cbr = new CheckboxRow ( '', 'zebricek', true, 'zebricek_' );
    for($ii=0; $ii<$g_zebricek_cnt; $ii++) {
        $cbr->addEntry ( $g_zebricek[$ii]['nm'], null, $g_zebricek[$ii]['id'], !$is_new && ( $zaznam['rebricek'] & $g_zebricek[$ii]['id'] != 0 ), true );
    }
    echo $cbr->render();    
?>
    </TD>
</TR>

<!-- Financial Type -->
<TR>
    <TD align="right" valign="top">Finanční typ</TD>
    <TD>
<?
    $cbr = new CheckboxRow ( '', 'finType', true, 'financial_type_' );
    while ($ft = mysqli_fetch_array($finTypes)) {
        $cbr->addEntry ( $ft['nazev'], $ft['popis'], $ft['id'], !$is_new && ( $zaznam['financial_type'] === $ft['id'] ), true );
    }
    echo $cbr->render();    
?>
    </TD>
</TR>

<!-- Payment Type -->
<TR>
    <TD align="right">Typ platby</TD>
    <TD>
<?
    $paymentTypes = ['C' => 'Z celé', 'R' => 'Z rozdílu', 'P' => 'Pevná'];
    foreach ($paymentTypes as $key => $label) {
        echo('<input type="radio" name="payment_type" value="'.$key.'" id="pt_'.$key.'" onclick="toggleAmount(\''.$key.'\')"');
        if (($zaznam['payment_type'] ?? '') == $key) echo(' checked');
        echo('><label for="pt_'.$key.'">'.$label.'</label>&nbsp;');
    }
?>
    </TD>
</TR>

<TR id="amountRow">
    <TD align="right">Platba</TD>
    <TD>
        <span id="amount_currency"><input type="text" name="amount_currency">Kč</span>
        <span id="amount_percent" style="display:none;"><input type="text" name="amount_percent">%</span>
    </TD>
</TR>

<TR><TD colspan="2" align="center">
    <input type="submit" value="Uložit">
</TD></TR>
</form>

<script>
function toggleAmount( type ) {
    document.getElementById('amount_currency').style.display = (type === 'P') ? 'inline' : 'none';
    document.getElementById('amount_percent').style.display = (type !== 'P') ? 'inline' : 'none';
}
window.onload = toggleAmount;
</script>

<BR><hr><BR>
<? echo('<A HREF="index.php?id='._FINANCE_GROUP_ID_.'&subid=4">Zpět</A><BR>'); ?>
<BR><hr><BR>
</CENTER>
</TD>
<TD></TD>
</TR>
<TR><TD COLSPAN=3 ALIGN=CENTER>
<!-- Footer Begin -->
<? require_once ("footer.inc.php"); ?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<? HTML_Footer(); ?>
