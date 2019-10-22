<?php

// core/ClassLoaderクラスのregisterDir()メソッドを呼び出す
require 'core/ClassLoader.php';

// coreとmodelsをオートロードの対象ディレクトリに設定
$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__).'/core');
$loader->registerDir(dirname(__FILE__).'/models');

// オートロードへ登録
$loader->register();
