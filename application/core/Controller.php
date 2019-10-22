<?php

abstract class Controller
{
    protected $controller_name;
    protected $action_name;
    protected $application;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $auth_actions = array();

    public function __construct($application)
    {
        // get_class()関数...指定したオブジェクトのクラス名を取得する          UserController
        // substr()の第三引数の-10(文字)は、後ろの'Controller'を除くため指定   User
        // strtolower()で渡された文字列を小文字に変換                         user
        $this->controller_name = strtolower(substr(get_class($this), 0, -10));

        // コンストラクタの引数として受け取ったApplicationクラスのインスタンスから
        // 下4つクラスのインスタンスを取得してControllerクラスのプロパティに設定
        $this->application = $application;
        $this->request     = $application->getRequest();
        $this->response    = $application->getResponse();
        $this->session     = $application->getSession();
        $this->db_manager  = $application->getDbManager();
    }

    public function run($action, $params = array())
    {
        $this->action_name = $action;

        // アクションとなるメソッドの名前を$action_methodに格納
        $action_method = $action . 'Action';
        // method_exists()でメソッドが存在するか判定
        if (!method_exists($this, $action_method)) {
            $this->forward404();
        }

        // needsAuthentication()の戻り値がtrueでかつ未ログイン時に、ログイン必須通知を伝える
        if ($this->needsAuthentication($action) && !$this->session->isAuthenticated()) {
            throw new UnauthorizedActionException();
        }

        // アクション実行 run()の第二引数として受け取ったルーティングパラメータをアクションの引数として渡す(可変関数)
        $content = $this->$action_method($params);

        return $content;
    }

    protected function needsAuthentication($action)
    {
        if ($this->auth_actions === true || (is_array($this->auth_actions) && in_array($action, $this->auth_actions))) {
            return true;
        }

        return false;
    }

    // ビューファイルの読み込み処理
    protected function render($variables = array(), $template = null, $layout = 'layout')
    {
        // Viewクラスのコンストラクタの第二引数に指定するデフォルト値の連想配列を指定
        $defaults = array(
            'request'  => $this->request,
            'base_url' => $this->request->getBaseUrl(),
            'session'  => $this->session,
        );

        // Viewクラスのインスタンスを作成 ディレクトリのパスはgetViewDir()で取得
        $view = new View($this->application->getViewDir(), $defaults);

        //$templateがnull、指定されなかった場合はアクション名をファイル名として利用
        if (is_null($template)) {
            $template = $this->action_name;
        }

        // コントローラ名とテンプレート名の先頭に付与
        $path = $this->controller_name . '/' . $template;

        // ビューファイルの読み込み 結果をそのままControllerクラスのrender()に返り値としている
        return $view->render($path, $variables, $layout);
    }

    // 404エラー画面に遷移させる
    protected function forward404()
    {
        throw new HttpNotFoundException('Forwarded 404 page from ' . $this->controller_name . '/' . $this->action_name);
    }

    // URLを引数として受け取り、Responseオブジェクトにリダイレクトをするよう設定
    protected function redirect($url)
    {
        if (!preg_match('#https?://#', $url)) {
            $protocol = $this->request->isSsl() ? 'https://' : 'http://';
            $host = $this->request->getHost();
            $base_url = $this->request->getBaseUrl();

            $url = $protocol . $host . $base_url . $url;
        }

        // Locationヘッダ指定すると同時に、ステータスコードを302に設定
        $this->response->setStatusCode(302, 'Found');
        $this->response->setHttpHeader('Location', $url);
    }

    // トークンを生成し、サーバ上に保持するためにセッションを格納する
    protected function generateCsrfToken($form_name)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, array());
        // 最大で10個のトークンを保持し、array_shift()で古いものから削除する
        if (count($tokens) >= 10) {
            array_shift($tokens);
        }

        // トークンを生成している箇所 セッションIDと生成時間を繋げた文字列をハッシュ化した文字列をトークンとして格納
        // micritime()...現在のUNIXタイムスタンプをマイクロ秒まで返す
        $token = sha1($form_name . session_id() . microtime());
        $tokens[] = $token;

        $this->session->set($key, $tokens);

        return $token;
    }

    // セッション上から格納されたトークンからPOSTされたトークンを探す
    protected function checkCsrfToken($form_name, $token)
    {
        $key = 'csrf_tokens/' . $form_name;
        $tokens = $this->session->get($key, array());

        // array_search()で実際にセッション上にトークンが格納されているか判定
        if (false !== ($pos = array_search($token, $tokens, true))) {
            unset($tokens[$pos]);
            $this->session->set($key, $tokens);

            return true;
        }

        return false;
    }
}
