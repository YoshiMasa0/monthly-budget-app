<?php
class FixedCost {
    /**
     * DBから固定費を全て取得する
     *
     * @return array{...} 固定費のデータ
     */
    public static function select_all() {
        $sql = "SELECT * FROM fixed_cost";

        try {
            $dbh = new PDO(DSN, DB_USER, DB_PASSWORD, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
            $sth = $dbh->query($sql);
            $costs = $sth->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() ."\nsql : ". $sql);
        } finally {
            $dbh = null;
            $sth = null;
        }
        return $costs;
    }

    /**
     * 渡された固定費データを Upsert 処理をする
     *
     * @param [type] $incomes 固定費情報
     */
    public static function upsert($dbh, $fixed_costs) {
        // 連想配列のキーを配列形式で取得
        $keys = array_keys($fixed_costs[0]);
        // 下記の「」で囲まれている箇所を作成
        // INSERT INTO テーブル名 「(列名1, 列名2)」 VALUES (値1, 値2) AS new ON DUPLICATE KEY UPDATE 値2 = new.値2
        $column_sql = "(cost_no, ". implode(", ", $keys). ")";

        // 下記の「」で囲まれている箇所を作成
        // INSERT INTO テーブル名 (列名1, 列名2) VALUES 「(値1, 値2)」 AS new ON DUPLICATE KEY UPDATE 値2 = new.値2
        // 作成する流れは配列を作成し、その要素をカンマで区切った文字列を作成する
        $value_num = count($fixed_costs[0]);
        $placeholder_array = array_fill(0, $value_num, "?"); // 値が「?」の配列を作成
        $value_sql = "(?, ". implode(", ", $placeholder_array). ")"; // (値1, 値2)を作成
        // 登録するデーターの数だけ(値1, 値2)を作成
        $fixed_costs_length = count($fixed_costs);
        $value_sql_array = array_fill(0, $fixed_costs_length, $value_sql);
        $placeholder_sql = implode(", ", $value_sql_array);

        // 下記の「」で囲まれている箇所を作成
        // INSERT INTO テーブル名 (列名1, 列名2) VALUES (値1, 値2) AS new ON DUPLICATE KEY UPDATE 「値2 = new.値2」
        $update_column_array = [];
        foreach ($keys as $key) {
            // $update_column_array[] = $key ." = new.". $key;
            $update_column_array[] = $key ." = VALUES(". $key .")";
        }
        $update_column_sql = implode(", ", $update_column_array);

        $sql ="INSERT INTO fixed_cost ". $column_sql ." VALUES ". $placeholder_sql ." ON DUPLICATE KEY UPDATE ". $update_column_sql;
        // $sql ="INSERT INTO income ". $column_sql ." VALUES ". $placeholder_sql ." AS new ON DUPLICATE KEY UPDATE ". $update_column_sql;

        // プレースホルダに代入する値
        $data = [];
        for ($i = 0; $i < $fixed_costs_length; $i++) {
            $data[] = $i + 1;
            foreach ($fixed_costs[$i] as $value) {
                $data[] = $value;
            }
        }

        try {
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
     * 指定の登録NOより大きい番号の固定費を削除する
     *
     * @param [type] $cost_no 収入登録No
     */
    public static function delete($dbh, $cost_no) {
        $sql = "DELETE FROM income WHERE income_no > ?";
        // プレースホルダに代入する値
        $data = [];
        $data[] = $cost_no;

        try {
            $sth = $dbh->prepare($sql);
            $sth->execute($data);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() ."\nsql : ". $sql ."\ndata : ". print_r($data, true));
        } finally {
            $dbh = null;
            $sth = null;
        }
    }
}
?>