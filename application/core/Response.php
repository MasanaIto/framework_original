<?php

class Response
{
    protected $content;
    protected $status_code = 200;
    protected $status_text = 'OK';
    protected $http_headers = array();

    // 各プロパティに設定された値をもとにレスポンスの送信を行う
    public function send()
    {
        // ステータスコードの指定 通常は"HTTP/1.1 200 OK"になる
        header('HTTP/1.1' . $this->status_code . ' ' . $this->status_text);

        // $http_headersにHTTPレスポンスヘッダの指定があればheader()で送信
        foreach ($this->http_headers as $name => $value) {
            header($name . ':' . $value);
        }

        // レスポンス内容を送信
        echo $this->content;
    }

    // HTMLなど実際にクライアントに返す内容を格納する
    public function setContent($content)
    {
        $this->content = $content;
    }

    // HTTPのステータスコードを格納 500, 404 など
    public function setStatusCode($status_code, $status_text = '')
    {
        $this->status_code = $status_code;
        $this->status_text = $status_text;
    }

    // HTTPヘッダを格納する ヘッダの名前をキーに、ヘッダの内容を値にして連想配列形式で格納する
    public function setHttpHeader($name, $value)
    {
        $this->http_headers[$name] = $value;
    }
}