<?php
require_once("const.php");
require_once("db/fixed_cost.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	try {
		$fixed_costs = FixedCost::select_all();
		$fixed_cost_array = [];
		foreach ($fixed_costs as $fixed_cost) {
			$fixed_cost_array[] = $fixed_cost["cost"];
		}

		$response = json_encode(
			[
				"result" => true,
				"cost" => $fixed_cost_array
			]
		);
		echo($response);
	} catch (Exception $e) {
		$log_message = $ERROR_LOG_TMPL. $e->getMessage();
		error_log($log_message, 3, ERROR_LOG_PATH);
		$response = json_encode(
			[
				"result" => false,
				"income" => ""
			]
		);
		echo($response);
	}

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

	try {
		// TODO ポストされてきた値のチェック
		$fixed_costs_length = count($_POST["cost"]);
		$fixed_costs = [];
		for ($i = 0; $i < $fixed_costs_length; $i++) {
			$fixed_cost = ["cost" => $_POST["cost"][$i]];
			$fixed_costs[] = $fixed_cost;
		}
		
		$dbh = new PDO(DSN, DB_USER, DB_PASSWORD, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
		$dbh->beginTransaction();
		FixedCost::upsert($dbh, $fixed_costs);
		FixedCost::delete($dbh, $fixed_costs_length);
		$dbh->commit();

		// リダイレクト
		header('Location: http://localhost/budget/');
		exit;
	} catch (Exception $e) {
		$dbh->rollBack();
		$log_message = $ERROR_LOG_TMPL. $e->getMessage();
		error_log($log_message, 3, ERROR_LOG_PATH);
		
		// リダイレクト
		header('Location: http://localhost/budget/error.php');
		exit;
	}
}
?>