<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");
@extract($_REQUEST);

require_once ('connect.inc.php');
require_once ('sess.inc.php');
require_once ('common.inc.php');
require_once ("./cfg/_globals.php");

if (IsLoggedFinance()) {
	db_Connect();

	// basic sanity checks
	if (empty($payment_type)) {
		header('location: '.$g_baseadr.'error.php?code=62'); // missing required field
		exit;
	}

	// sanitize/normalize input
	$payment_type = correct_sql_string($payment_type);
   
    $sqlkeys = [];

    foreach ( $g_payrule_keys as [$key,$label] ) {
        if ( array_key_exists($key.'_all') ) $sqlkeys[$key] = null;
        else if ( $key === 'financial_type' ) {
            $value = $_POST[$key] ?? []; $value = array_map('intval',$value);
            $sqlkeys[$key] = array_map('intval',$_POST[$key] ?? []);
        }
        else {
            $sqlkeys[$key] = $_POST[$key] ?? [];
        }
    }

    // value/ratio fields
	$amount_currency = isset($amount_currency) ? intval($amount_currency) : 0;
	$amount_percent = isset($amount_percent) ? intlval($amount_percent) : 0;

    $types = ""; // list of bind types
    $values = [];  // list of bind values
    $update_query = "
        UPDATE " . TBL_PAYRULES . "
        SET ";

    $update_query .= 'druh_platby = :druh_platby';
    $types .= 's';
    $values[] = $payment_type;

    // choose which field to save depending on payment type
    if ($payment_type === 'P') {
        $update_query .= 'platba = :platba';
        $types .= 'i';
        $values[] = $amount_currency;
    } else {
        $update_query .= 'pomer_platby  = :pomer_platby ';
        $types .= 'd';
        $values[] = $amount_percent / 100.0;
    }    
    
    $update_query .= 'WHERE id = :id';
    $types .= 'i';

	if (isset($id) && is_numeric($id)) {
        // update payement info
		$values[] = (int)$id;

        $stmt = $db_connect->prepare($update_query);

        $db_exec ( $stmt, $types, $values );
	}
	else
	{
        $value_marker = [];
        $values = []; // value array, containt some bind values. Will be modified on multivalue updates 
        $keyIndexes = []; // mapping key => value position

        $check_query = "SELECT id FROM " .TBL_PAYRULES. " WHERE ";
        $check_values = [];

        $insert_query = "INSERT INTO " .TBL_PAYRULES. "(";
            
        foreach ( $sqlkeys as [$key,$value] ) {
            $insert_query .= $key . ', ';
            $test_query .= $key . '= :' . $key; 
            $types .= ( $key === 'financial_type' ? 'i' : 's' );
            $values[] = null;
            $check_values[] = null;
            $keyIndexes[$key] = count($values);
            $value_marker[] = '?';
        }

        $insert_query .= 'druh_platby, ';
        $values[] = $payment_type;
        $value_marker[] = '?';

        // choose which field to save depending on payment type
        if ($payment_type === 'P') {
            $insert_query .= 'platba ';
            $values[] = $amount_currency;
            $value_marker[] = '?';
        } else {
            $insert_query .= 'pomer_platby ';
            $values[] = $amount_percent / 100.0;
            $value_marker[] = '?';
        }    

        $insert_query .= ") VALUES (" . implode ( ',', $value_marker ) . ");";

        $stmt = $db_connect->prepare($insert_query);

        // build the combinations for checked values
        $combinations = [[]]; // start with one empty combination

        foreach ($g_payrule_keys as [$key, $label]) {
            $allKey = $key . '_all';
            $arrayKey = $key; // checkbox array

            if (!empty($_POST[$allKey])) {
                // "all" selected → single NULL value
                $values = [null];
            } elseif (!empty($_POST[$arrayKey]) && is_array($_POST[$arrayKey])) {
                $values = $_POST[$arrayKey];
            } else {
                // nothing selected
                $values = [null];
            }

            // build Cartesian product
            $newCombinations = [];
            foreach ($combinations as $combo) {
                foreach ($values as $v) {
                    $newCombinations[] = array_merge($combo, [$v]);
                }
            }
            $combinations = $newCombinations;
        }




        $db_exec ( $stmt, $types, $values );
    }

	$result = query_db($insert_query)
		or die("Chyba při provádění dotazu do databáze.");

	if ($result == FALSE)
		die("Nepodařilo se uložit záznam o platbě.");

	header('location: '.$g_baseadr.'index.php?id='._FINANCE_GROUP_ID_.'&subid=5');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>
