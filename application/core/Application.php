<?php

abstract class Application
{
    protected $debug = false;
    protected $request;
    protected $response;
    protected $session;
    protected $db_manager;
    protected $login_action = array();

    public function __construct($debug = false)
    {
        $this->setDebugMode($debug);
        $this->initialize();
        $this->configure();
    }

    // デバッグモードに応じてエラー表示処理を変更する
    protected function setDebugMode($debug)
    {
        if ($debug) {
            $this->debug = true;
            ini_set('display_errors', 1);
            error_reporting(-1);
        } else {
            $this->debug = false;
            ini_set('display_errors', 0);
        }
    }

    // クラスの初期化処理を行う
    protected function initialize()
    {
        $this->request  = new Request();
        $this->response = new Response();
        $this->session  = new Session();
        $this->db_manager = new DbManager();
        // Routerクラスはインスタンス作成時にルーティング定義配列を渡すよう実装しているため、ルーティング定義配列を返すregisterRoutes()を呼び出す
        $this->router = new Router($this->registerRoutes());
    }

    // initialize()の直後に呼び出される 個別のアプリケーションで様々な設定ができるようからのメソッドとして定義
    protected function configure()
    {
    }

    // ルートディレクトリへのパスを返す 必要があればディレクトリ構造を変更できるようにするため
    abstract public function getRootDir();

    abstract protected function registerRoutes();

    public function isDebugMode()
    {
        return $this->debug;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getDbManager()
    {
        return $this->db_manager;
    }

    public function getControllerDir()
    {
        return $this->getRootDir() . '/controllers';
    }

    public function getViewDir()
    {
        return $this->getRootDir() . '/views';
    }

    public function getModelDir()
    {
        return $this->getRootDir() . '/models';
    }

    public function getWebDir()
    {
        return $this->getRootDir() . '/web';
    }

    // Routerクラスのresolve()を呼び出してルーティングパラメータを取得し、コントローラ名とアクション名を特定する
    public function run()
    {
        try {
            $params = $this->router->resolve($this->request->getPathInfo());
            if ($params === false) {
                throw new HttpNotFoundException('No route found for' . $this->request->getPathInfo());
            }

            $controller = $params['controller'];
            $action = $params['action'];

            $this->runAction($controller, $action, $params);

            // なかった場合404エラーを表示させる
        } catch (HttpNotFoundException $e) {
            $this->render404Page($e);

            // ログイン画面のアクションを自動的に呼び出す
        } catch (UnauthorizedActionException $e) {
            list($controller, $action) = $this->login_action;
            $this->runAction($controller, $action);
        }

        $this->response->send();
    }

    // アクションを実行する
    public function runAction($controller_name, $action, $params = array())
    {
        // unfirst関数で先頭を大文字にする
        $controller_class = ucfirst($controller_name) . 'Controller';

        $controller = $this->findController($controller_class);
        if ($controller === false) {
            throw new HttpNotFoundException($controller_class . ' controller is not found.');
        }

        $content = $controller->run($action, $params);

        $this->response->setContent($content);
    }

    // コントローラクラスが読み込まれてない場合、クラスファイルの読み込みを行う
    protected function findController($controller_class)
    {
        if (!class_exists($controller_class)) {
            $controller_file = $this->getControllerDir() . '/' . $controller_class . '.php';

            if (!is_readable($controller_file)) {
                return false;
            } else {
                require_once $controller_file;

                if (!class_exists($controller_class)) {
                    return false;
                }
            }
        }

        return new controller_class($this);
    }

    protected function render404Page($e)
    {
        $this->response->setStatusCode(404, 'Not Found');
        $message = $this->isDebugMode() ? $e->getMessage() : 'Page not found.';
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        $this->response->setContent(<<<EOF
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset=utf-8>
        <title>404</title>
    </head>
    <body>
        {$message}
    </body>
    </html>
    EOF
        );
    }
}