<?php
require_once("const.php");
require_once("db/variable_cost.php");
require_once("db/fixed_cost.php");
require_once("db/income.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
	// TODO 例外処理の作成
	try {
		$budget = get_monthly_budget();
		echo(json_encode(["budget" => $budget]));
	} catch (Exception $e) {
		error_log($e->getMessage()."\n", 3, 'indexphp.log');
	}
	

} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // TODO 例外処理の作成

	// TODO ポストされてきた値のチェック
    $costs_length = count($_POST["cost"]);
    $variable_costs = [];
    for ($i = 0; $i < $costs_length; $i++) {
        $variable_cost = ["cost" => $_POST["cost"][$i]];
        $variable_costs[] = $variable_cost;
    }
	
    // DBにデータ登録をする
    $insert_result = VariableCost::insert($variable_costs);
	$budget = get_monthly_budget();
	echo(json_encode(["budget" => $budget]));
}

// TODO コメント作成
function get_monthly_budget() {
	// 変動費を取得する期間
	$now  = new DateTime("now");
	$start_date_time = $now->format("Y-m-01 00:00:00"); // 月初
	$end_date_time = $now->modify("last day of")->format("Y-m-d 23:59:59"); // 月末

	// それぞれの金額をDBから取得
	$variable_costs = VariableCost::select_by_date_range($start_date_time, $end_date_time);
	$fixed_costs = FixedCost::select_all();
	$incomes = Income::select_all();

	// TODO DBに収入が登録されていない場合　収入登録画面にリダイレクト
	// TODO DBに固定費が登録されていない場合　固定費登録画面にリダイレクト

	$total_variable_cost = get_total_key_value("cost", $variable_costs);
	$total_fixed_cost = get_total_key_value("cost", $fixed_costs);
	$total_income = get_total_key_value("income", $incomes);

	return $total_income - $total_fixed_cost - $total_variable_cost;
}

// TODO コメント作成
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