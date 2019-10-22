<?php

// データベースへのアクセスを行う
abstract class DbRepository
{
    protected $con;

    // DbManagerクラスからPDOクラスのインスタンスを受け取って内部に保持し、それに対してSQL文を実行する
    public function __construct($con)
    {
        $this->setConnection($con);
    }

    // 同上
    public function setConnection($con)
    {
        $this->con = $con;
    }

    // PDOStatementクラスのインスタンスを取得する
    public function execute($sql, $params = array())
    {
        $stmt = $this->con->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    // SELECT文を実行した場合、実行結果の取得はPDOStatementクラスのインスタンスから行う
    // 一行のみ取得
    public function fetch($sql, $params = array())
    {
        // PDO::FETCH_ASSOC定数は、取得結果を連想配列で受け取る指定 取得した配列のキーが数値の連番となるため、指定しやすい
        return $this->execute($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    // 全ての行を取得
    public function fetchAll($sql, $params = array())
    {
        return $this->execute($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}