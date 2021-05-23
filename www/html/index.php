<?php
header('X-Frame-Options:DENY');

// 定数ファイルを読み込み
require_once '../conf/const.php';

// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// userデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

// itemデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';

// ログインチェックを行うため、セッションを開始
session_start();

// CSRF対策：トークンを生成してセッションに保存
$token = get_csrf_token();
// dd($token);
// dd($_SESSION);

// ログインチェック用関数を利用
if(is_logined() === false){
  // ログインしていない場合は、ログインページへリダイレクト
  redirect_to(LOGIN_URL);
}

// PDOを取得
$db = get_db_connect();

// PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);

// PDOを利用して公開されている商品データを取得
$items = get_open_items($db);

// ページャー実装：現在のページ数をgetで取得
  // 公開済み商品の件数を取得
  // 必要ページ数を算出
  // 現在のページで取得する8つの商品を何番目から取得するかを決定

// indexのページのクライアントソースファイルを読み込む
include_once VIEW_PATH . 'index_view.php';