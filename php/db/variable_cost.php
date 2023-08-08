<?php
class VariableCost {

    /**
     * // TODO コメント作成
     */
    public static function insert($variable_costs) {
        // 連想配列のキーを配列形式で取得
        $keys = array_keys($variable_costs[0]);
        // 下記の「」で囲まれている箇所を作成
        // INSERT INTO テーブル名 「(列名1, 列名2)」 VALUES (値1, 値2)
        $column_sql = "(". implode(", ", $keys). ", created_at)";

        $value_num = count($variable_costs[0]);
        $variable_costs_length = count($variable_costs);

        // 下記の「」で囲まれている箇所を作成
        // INSERT INTO テーブル名 (列名1, 列名2) VALUES 「(値1, 値2)」
        // 作成する流れは配列を作成し、その要素をカンマで区切った文字列を作成する
        $placeholder_array = array_fill(0, $value_num, "?"); // 値が「?」の配列を作成
        $value_sql = "(". implode(", ", $placeholder_array). ", now())"; // (値1, 値2)を作成
        // 登録するデーターの数だけ(値1, 値2)を作成
        $value_sql_array = array_fill(0, $variable_costs_length, $value_sql);
        $placeholder_sql = implode(", ", $value_sql_array);

        // プレースホルダに代入する値
        $data = [];
        $sql ="INSERT INTO variable_cost " . $column_sql . " VALUES ". $placeholder_sql;
        foreach($variable_costs as $variable_cost){
            foreach($variable_cost as $value){
                $data[] = $value;
            }
        }

        $dbh = new PDO(DSN, DB_USER, DB_PASSWORD);
        $sth = $dbh->prepare($sql);
        $sth->execute($data);

        $dbh = null;
        $sth = null;
    }

    // TODO コメント作成
    public static function select_by_date_range($start_date_time, $end_date_time) {
        $sql = "SELECT * FROM variable_cost WHERE created_at BETWEEN ? AND ?";
        // プレースホルダに代入する値
        $data = [];
        $data[] = $start_date_time;
        $data[] = $end_date_time;

        $dbh = new PDO(DSN, DB_USER, DB_PASSWORD, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $sth = $dbh->prepare($sql);
        $sth->execute($data);
        $costs = $sth->fetchAll();

        $dbh = null;
        $sth = null;

        return $costs;
    }
}
?>