<?php
/**
 * DBのvariable_costテーブルを操作するクラス
 */
class VariableCost {

    /**
     * DBのvariable_costテーブルにデータを登録する
     * 
     * 関数に渡す連想配列のキーは列名と一致させる必要がある。
     *
     * @param [array{...}] $variable_costs 登録するデータ
     * @return void
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

        try {
            $dbh = new PDO(DSN, DB_USER, DB_PASSWORD, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $sth = $dbh->prepare($sql);
            $sth->execute($data);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() ."\nsql : ". $sql ."\ndata : ". print_r($data, true));
        } finally {
            $dbh = null;
            $sth = null;
        }
    }

    /**
     * 指定した範囲の日時の変動費を取得する
     * 
     * 指定した開始日時から終了日時までの変動費を取得する
     *
     * @param [type] $start_date_time 取得する範囲（開始日時）
     * @param [type] $end_date_time　取得する範囲（終了日時）
     * @return array{...} 変動費のデータ
     */
    public static function select_by_date_range($start_date_time, $end_date_time) {
        $sql = "SELECT * FROM variable_cost WHERE created_at BETWEEN ? AND ?";
        // プレースホルダに代入する値
        $data = [];
        $data[] = $start_date_time;
        $data[] = $end_date_time;

        try {
            $dbh = new PDO(DSN, DB_USER, DB_PASSWORD, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
            $sth = $dbh->prepare($sql);
            $sth->execute($data);
            $costs = $sth->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() ."\nsql : ". $sql ."\ndata : ". print_r($data, true));
        } finally {
            $dbh = null;
            $sth = null;
        }
        return $costs;
    }
}
?>