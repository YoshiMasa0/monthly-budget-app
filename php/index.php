<?php
require_once("const.php");
require_once("db/variable_cost.php");
require_once("db/fixed_cost.php");
require_once("db/income.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	try {
		$budget = get_monthly_budget();

		$response = json_encode(
			[
				"result" => true,
				"budget" => $budget
			]
		);
		echo($response);
	} catch (Exception $e) {
		$log_message = $ERROR_LOG_TMPL. $e->getMessage();
		error_log($log_message, 3, ERROR_LOG_PATH);
		$response = json_encode(
			[
				"result" => false,
				"budget" => ""
			]
		);
		echo($response);
	}

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

	try {
		// TODO ポストされてきた値のチェック
		$costs_length = count($_POST["cost"]);
		$variable_costs = [];
		for ($i = 0; $i < $costs_length; $i++) {
			$variable_cost = ["cost" => $_POST["cost"][$i]];
			$variable_costs[] = $variable_cost;
		}
		// DBにデータ登録をする
		VariableCost::insert($variable_costs);
		$budget = get_monthly_budget();
		
		$response = json_encode(
			[
				"result" => true,
				"budget" => $budget
			]
		);
		echo($response);
	} catch (Exception $e) {
		$log_message = $ERROR_LOG_TMPL. $e->getMessage();
		error_log($log_message, 3, ERROR_LOG_PATH);
		$response = json_encode(
			[
				"result" => false,
				"budget" => ""
			]
		);
		echo($response);
	}
}


/**
 * 残り月の予算を取得する
 *
 * @return int　残りの月の予算
 */
function get_monthly_budget() {
	// 変動費を取得する期間
	$now  = new DateTime("now");
	$start_date_time = $now->format("Y-m-01 00:00:00"); // 月初
	$end_date_time = $now->modify("last day of")->format("Y-m-d 23:59:59"); // 月末

	// それぞれの金額をDBから取得
	$variable_costs = VariableCost::select_by_date_range($start_date_time, $end_date_time);
	$fixed_costs = FixedCost::select_all();
	$incomes = Income::select_all();

	$total_variable_cost = get_total_key_value("cost", $variable_costs);
	$total_fixed_cost = get_total_key_value("cost", $fixed_costs);
	$total_income = get_total_key_value("income", $incomes);

	return $total_income - $total_fixed_cost - $total_variable_cost;
}

/**
 * 指定したキーの合計の値を取得する
 * 
 * 配列に格納されている連想配列において、指定したキーの合計値を取得する。
 * 指定したキーがない場合、値がint型ではない場合は Exception をスローする。
 *
 * @param [string] $key_name 連想配列のキー
 * @param [array] $array 連想配列を格納している配列
 * @return int 指定したキーの合計値
 */
function get_total_key_value($key_name, $array) {
	$total = 0;
	foreach ($array as $map) {
		if (!array_key_exists($key_name, $map)) {
			throw new Exception("関数". __FUNCTION__ ." : 指定した配列にキー「". $key_name ."」が存在しません");
		}
		if (!is_int($map[$key_name])) {
			throw new Exception("関数". __FUNCTION__ ." : int型ではない値「". $map[$key_name] ."」が入っています");
		}
		$total += $map[$key_name];
	}
	return $total;
}
?>