<?php

class ClassLoader
{
    protected $dirs;

    // オートローダークラスを登録する
    public function register()
    {
        sql_autoload_register(array($this, 'loadClass'));
    }

    // core と models からクラスファイルの読み込みを行う
    // オートロード対象のディレクトリへのフルパスを$dirsに格納
    public function registerDir($dir)
    {
        $this->dirs[] = $dir;
    }

    // クラスファイル(クラス名.php)を探し、読み込みを行う
    public function loadClass($class)
    {
        foreach ($this->dirs as $dir) {
            $file = $dir . '/' . $class . '.php';
            if(is_readable($file)) {
                require $file;

                return;
            }
        }
    }
}
