<?php

// HTMLファイルの読み込みとHTMLに変数を渡す役割
class View
{
    protected $base_dir;
    protected $defaults;
    protected $layout_variables = array();

    // $base_dir...ビューファイルを格納しているviewsディレクトリへの絶対パスを指定
    // $defaults...ビューファイルに変数を渡す際、デフォルトで渡す変数を設定できるようにする
    public function __construct($base_dir, $defaults = array())
    {
        $this->base_dir = $base_dir;
        $this->defaults = $defaults;
    }

    // $layout_variablesプロパティに値を設定する...render()内でレイアウトファイルを読み込む際に渡す変数
    public function setLayoutVar($name, $value)
    {
        $this->layout_variables[$name] = $value;
    }

    // 実際にビューファイルの読み込みを行う
    // render(ビューファイルへのパス, ビューファイルに渡す変数[連想配列], レイアウトファイル名[Controllerクラスから呼び出された場合])
    public function render($_path, $_variables = array(), $_layout = false)
    {
        $_file = $this->base_dir . '/' . $_path . '.php';

        // extract()...連想配列を指定し、連想配列のキーに変数名に、連想配列の値を変数の値として展開する関数
        extract(array_merge($this->defaults, $_variables));

        // アウトプットバッファリング...ob_start() 内部に出力情報をバッファリング
        // バッファリングとは、複数の機器やソフトウェアの間でデータをやり取りするときに、
        // 処理速度や転送速度の差を補うためにデータを専用に設けられた記憶領域一時的に保存しておくこと。
        ob_start();
        // バッファの自動フラッシュを制御する関数、0を渡して無効に。
        ob_implict_flush(0);

        require $_file;

        // ob_get_clean()...バッファに格納された文字列を取得する関数 $contentに格納
        $content = ob_get_clean();

        // レイアウトファイル名が指定されている場合、再度render()実行してレイアウトファイルの読み込みを行う
        if ($_layout) {
            $content = $this->render($_layout,
                array_merge($this->layout_variables, array(
                    '_content' => $content,
                )
            ));
        }

        return $content;
    }

    // $this->escape($var)で、HTML特殊文字エスケープを呼び出せる(htmlspecialchaersは長いので)
    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}