<?php

// セッション情報を管理する
class Session
{
    protected static $sessionStarted = false;
    protected static $sessionIdRegenerated = false;

    public function __construct()
    {
        // コンストラクタ実行のタイミングでsession_start()を実行
        if (!self::$sessionStarted) {
            session_start();

            self::$sessionStarted = true;
        }
    }

    // $_SESSIONの設定
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    // $_SESSIONの取得
    public function get($name, $default = null) 
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return $default;
    }

    // $_SESSIONから指定した値を削除
    public function remove($name)
    {
        unset($_SESSION[$name]);
    }

    // $_SESSIONを空にする
    public function clear()
    {
        $_SESSION = array();
    }

    // セッションIDを新しく発行するためのsession_regenerate_id()を実行
    public function regenerate($destroy = true)
    {
        if (!self::$sessionIdRegenerated) {
            session_regenerate_id($destroy);

            self::$sessionIdRegenerated = true;
        }
    }

    // ユーザーがログイン状態を制御する
    // _authenticatedキーでログインのフラグを格納し、判定する
    public function setAuthenticated($bool)
    {
        $this->set('_authenticated', (bool)$bool);

        $this->regenerate();
    }

    public function isAuthenticated()
    {
        return $this->get('_authenticated', false);
    }
}