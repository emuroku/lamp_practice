<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// ログインチェックのためセッション開始
session_start();

// SESSION変数の初期化
$_SESSION = array();

// セッションクッキーのパラメータを取得し$paramsへ代入
$params = session_get_cookie_params();
// Cookieにsession_nameを設定し、有効期限切れになるようtimeを設定
setcookie(session_name(), '', time() - 42000,
  // pathパラメータをセット
  $params["path"], 
  // domainパラメータをセット
  $params["domain"],
  // secureパラメータをセット
  $params["secure"], 
  // httponlyパラメータをセット
  $params["httponly"]
);

// セッションを破棄する
session_destroy();

// ログインページへリダイレクト
redirect_to(LOGIN_URL);

