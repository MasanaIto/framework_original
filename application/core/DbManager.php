<?php

class DbManager
{
    // PDOクラスのインスタンスを配列で保持
    protected $connections = array();
    protected $repository_connection_map = array();
    protected $repositories = array();

    public function connect($name, $params)
    {
        $params = array_merge(array(
            'dsn'      => 'mysql:dbname= ;host=localhost',
            'user'     => 'root',
            'password' => 'root',
            'options'  => array(),
        ), $params);

        // PDO作成
        $con = new PDO(
            $params['dsn'],
            $params['user'],
            $params['password'],
            $params['options']
        );

        // PDO::ATTR_ERRMODE属性をPDO::ERRMODE_EXCEPTIONに設定
        // PDO内部でエラーが発生した場合に例外処理を発生
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->connections[$name] = $con;
    }

    // connect()で接続したコネクションを取得
    public function getConnection($name = null)
    {
        if (is_null($name)) {
            return current($this->connections);
        }

        return $this->connections[$name];
    }

    public function setRepositoryConnectionMap($repository_name, $name)
    {
        $this->repository_connection_map[$repository_name] = $name;
    }

    public function getConnectionForRepository($repository_name)
    {
        if (isset($this->repository_connection_map[$repository_name])) {
            $name = $this->repository_connection_map[$repository_name];
            $con  = $this->getConnection($name);
        } else {
            $con = $this->getConnection();
        }

        return $con;
    }

    // Repositoryクラスの管理を行う、実際にインスタンスの生成を担うメソッド
    public function get($repository_name)
    {
        if(!isset($this->repositories[$repository_name])) {
            // Repositoryのクラス名を指定
            $repository_class = $repository_name . 'Repository';
            // $getConnectionForRepositoryメソッドでコネクションを取得
            $con = $this->getConnectionForRepository($repository_name);

            // インスタンス生成を行う
            $repository = new $repository_class($con);

            // 生成されたインスタンスを格納
            $this->repositories[$repository_name] = $repository;
        }

        return $this->repositories[$repository_name];
    }

    // データベース接続の解放を行う
    // __destruct()は、__construct()とは反対に、インスタンスが放棄された際に自動的にPHPが呼び出すメソッド
    public function __destruct()
    {
        foreach ($this->repositories as $repository) {
            unset($repository);
        }

        foreach ($this->connections as $con) {
            unset($con);
        }
    }
}