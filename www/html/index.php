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

// // PDOを利用して公開されている商品データを取得
// $items = get_open_items($db);

// ページャー実装
  // 現在のページ数をgetで取得
$current_page = get_current_page();
  // 公開商品の総数を取得
$assortment = get_assortment($db);
  // 必要ページ数を算出
$pages = get_pages($db,$assortment['assortment']);
  // 現在のページ数で表示する商品の情報を取得
$items = get_display_items($db, $current_page);  

// indexのページのクライアントソースファイルを読み込む
include_once VIEW_PATH . 'index_view.php';