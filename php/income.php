<?php
require_once("const.php");
require_once("db/income.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	try {
		$incomes = Income::select_all();
		$income_array = [];
		foreach ($incomes as $income) {
			$income_array[] = $income["income"];
		}

		$response = json_encode(
			[
				"result" => true,
				"income" => $income_array
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
		$incomes_length = count($_POST["income"]);
		$incomes = [];
		for ($i = 0; $i < $incomes_length; $i++) {
			$income = ["income" => $_POST["income"][$i]];
			$incomes[] = $income;
		}
		
		$dbh = new PDO(DSN, DB_USER, DB_PASSWORD, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
		$dbh->beginTransaction();
		Income::upsert($dbh, $incomes);
		Income::delete($dbh, $incomes_length);
		$dbh->commit();

		// リダイレクト
		header('Location: http://localhost/budget/cost');
		exit;
	} catch (Exception $e) {
		$dbh->rollBack();
		$log_message = $ERROR_LOG_TMPL. $e->getMessage();
		error_log($log_message, 3, ERROR_LOG_PATH);
		
		// リダイレクト
		header('Location: http://localhost/budget/error');
		exit;
	}
}
?>