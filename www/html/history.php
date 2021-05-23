<?php
header('X-Frame-Options:DENY');

// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザーデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// 商品データに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';
// カートデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'cart.php';
// 購入履歴データに関する関数ファイルを読み込み
require_once MODEL_PATH . 'history.php';

// ログインチェックを行うため、セッションを開始
session_start();

// CSRF対策：トークンを生成してセッションに保存
$token = get_csrf_token();

// ログインチェック用関数を利用
if (is_logined() === false) {
  // ログインしていない場合は、ログインページへリダイレクト
  redirect_to(LOGIN_URL);
}

// PDOを取得
$db = get_db_connect();

// PDOを利用してログインユーザーのデータを取得
$user = get_login_user($db);

// ordersテーブルから購入履歴を取得
$orders = get_orders($db, $user);

// indexのページのクライアントソースファイルを読み込む
include_once VIEW_PATH . 'history_view.php';
