<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");
@extract($_REQUEST);

require_once ('connect.inc.php');
require_once ('sess.inc.php');
require_once ('common.inc.php');
require_once ('common_fin.inc.php');
require_once('./cfg/_globals.php');

if (!IsLoggedFinance()) {
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}

db_Connect();

// --- Sanitize / validate input ---
$payment_type = isset($_POST['payment_type']) ? trim($_POST['payment_type']) : '';
if ($payment_type === '') {
		header('location: '.$g_baseadr.'error.php?code=62'); // missing required field
		exit;
	}

$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
   
$local_payrule_keys = $g_payrule_keys; // make a shallow copy
$local_payrule_keys[] = ['finance_type', 'Finanční typ', 'i'];

// --- Build normalized key array ---
$sqlkeys = [];

foreach ($local_payrule_keys as [$key, $label, $type]) {
	$post_all = $_POST[$key . '_all'] ?? null;
	$post_arr = $_POST[$key] ?? [];

	if ($post_all) {
		$sqlkeys[$key] = null;
	} elseif ($key === 'termin' && is_array($post_arr) && count($post_arr) > 0 ) {
		$nums = array_map('intval', $post_arr);
		sort($nums);
		$first = reset($nums);
		// if full continuous range n..5 is selected -> -n
		if ($nums === range($first, 5)) {
			$sqlkeys[$key] = -$first;
		} else {
			$sqlkeys[$key] = $nums;
        }
	} else {
		$sqlkeys[$key] = is_array($post_arr) && count($post_arr) > 0 ? $post_arr : null;
        }
    }

// --- Check existing record (same key combination) ---
function find_existing_id(array $keys)
{
	$conditions = [];
	$values = [];
	$types = '';
	foreach ($keys as $k => $v) {
		if (is_null($v)) {
			$conditions[] = "$k IS NULL";
        } else {
			$conditions[] = "$k = ?";
			$types .= is_int($v) ? 'i' : 's';
			$values[] = $v;
		}
	}
	$sql = "SELECT id FROM " . TBL_PAYRULES . " WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
	$stmt = db_prepare($sql);
	$res = db_select($stmt,$types,$values);
	return $res && count ($res) > 0 ? $res[0]['id'] : null;
}    

// --- Determine existing / new record ---
$existing_id = null;

// Build combinations of all key values
$combinations = [[]];
foreach ($local_payrule_keys as [$key, $label, $type]) {

	$vals = $sqlkeys[$key];

	if ($vals === null) {
		$vals = [null];
	} elseif (is_array($vals)) {
		$vals = $vals;
	} else {
		$vals = [$vals];
	}

	// build Cartesian product
	$newCombinations = [];
	foreach ($combinations as $combo) {
		foreach ($vals as $v) {
			$newCombinations[] = array_merge($combo, [$key => $v]);
		}
	}
	$combinations = $newCombinations;
}

var_dump ( $combinations );
// --- For each combination: update or insert ---
foreach ($combinations as $combo) {

	var_dump ( $combo );

	$existing_id = find_existing_id($combo);

	if ($existing_id) {
		// --- Update existing ---
		$sql = "UPDATE " . TBL_PAYRULES . " 
		        SET druh_platby = ?, platba = ? 
		        WHERE id = ?";
		$stmt = db_prepare($sql);

		echo '<br>' . $sql . ' id=' . $existing_id . ' ' . $payment_type . ' ' . $amount;
		$result = db_exec($stmt, 'sii', [$payment_type, $amount, $existing_id]);

		if ($result === FALSE)
			die("Nepodařilo se uložit záznam o definici platby.");
	} else {
		// --- Insert new ---
		$fields = [];
		$placeholders = [];
		$values = [];
		$types = '';

		foreach ($local_payrule_keys as [$key, $label, $type]) {
			$fields[] = $key;
			$placeholders[] = '?';
			$v = $combo[$key] ?? null;
			$values[] = $v;
			$types .= $type;
		}

		// add fixed fields
		$fields[] = 'druh_platby';
		$fields[] = 'platba';
		$placeholders[] = '?';
		$placeholders[] = '?';
		$values[] = $payment_type;
		$values[] = $amount;
		$types .= 'si';

		$sql = "INSERT INTO " . TBL_PAYRULES . " (" . implode(',', $fields) . ")
			VALUES (" . implode(',', $placeholders) . ")";

		$stmt = db_prepare($sql);

		echo '<br>' . $sql; var_dump ( $values );
		$result = db_exec($stmt, $types, $values);

		var_dump($result);

		if ($result == FALSE)
			die("Nepodařilo se vytvořit záznam o definici platby.");
	}
}

//header('location: '.$g_baseadr.'index.php?id='._FINANCE_GROUP_ID_.'&subid=4');
exit;
?>
