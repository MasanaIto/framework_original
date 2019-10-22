<?php

class Router
{
    protected $routes;

    public function __construct($definitions)
    {
        $this->routes = $this->compileRoutes($definitions);
    }

    // ルーティング定義配列を変換
    public function compileRoutes($definitions)
    {
        $routes = array();

        foreach ($definitions as $url => $params) {

            // explode関数...URLのスラッシュごとに分割（第一引数）
            $tokens = explode('/', ltrim($url, '/'));
            foreach ($tokens as $i => $token) {
                if (0 === strpos($token, ':')) {
                    $name = substr($token, 1);
                    // コロンで始まる文字列があった場合、正規表現の形式に変換
                    $token = '(?P<' . $name . '>[^/]+)';
                }
                $tokens[$i] = $token;
            }

            // 分割したURLを再度スラッシュでつなげ、変換済みの値として$routesに格納
            $pattern = '/' . implode('/', $tokens);
            $routes[$pattern] = $params;
        }

        return $routes;
    }

    // マッチングを行う
    public function resolve($path_info)
    {
        // $path_infoの先頭がスラッシュでない場合、先頭にスラッシュを付与
        if ('/' !== substr($path_info, 0, 1)) {
            $path_info = '/' . $path_info;
        }

        foreach ($this->routes as $pattern => $params) {
            if(preg_match('#^' . $pattern . '$#', $path_info, $matches)) {
                $params = array_merge($params, $matches);

                return $params;
            }
        }

        return false;
    }
}