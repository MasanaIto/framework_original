<?php

// ユーザーのリクエスト処理を制御する
// HTTPメソッド、値、URLの取得など
class Request
{
    // HTTPメソッドがPOSTかどうか判定
    public function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }

        return false;
    }

    // $_GET変数から値を取得するメソッド
    public function getGet($name, $default = null)
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }

        return $default;
    }

    // $_POST変数から値を取得するメソッド
    public function getPost($name, $default = null)
    {
        if (isset($_POST[$name])) {
            return $_POST[$name];
        }

        return $default;
    }

    // サーバのホスト名を取得する,リダイレクトを行う際に利用
    public function getHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        return $_SERVER['SERVER_NAME'];
    }

    // HTTPS接続か判定...HTTPSにはonという文字が含まれる
    public function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return true;
        }

        return false;
    }

    // リクエストされたURLの情報を$_SERVER['REQUEST_URI']に格納
    public function getRequestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    // ベースURLを取得
    public function getBaseUrl()
    {
        $script_name = $_SERVER['SCRIPT_NAME'];

        $request_uri = $this->getRequestUri();

        // strpos関数....第一引数に師弟した文字列から、第二引数に指定した文字列が最初に出現する位置を調べる
        // dirname関数...ファイルのパスからディレクトリ部分を抜き出す
        // rtrim関数.....右側に続くスラッシュを削除
        if (0 === strpos($request_uri, $script_name)) {
            return $script_name;
        } else if (0 === strpos($request_uri, dirname($script_name))) {
            return rtrim(dirname($script_name), '/');
        }

        return '';
    }

    // PATH_INFOを取得 = REQUEST_URI - ベースURL
    // substr関数...第一引数で指定した文字列のうち、第二引数から第三引数で指定した文字数分取得する
    public function getPathInfo()
    {
        $base_url = $this->getBaseUrl();
        $request_uri = $this->getRequestUri();

        if (false !== ($pos = strpos($request_uri, '?'))) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        $path_info = (string)substr($request_uri, strlen($base_url));

        return $path_info;
    }
}