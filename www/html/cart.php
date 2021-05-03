<?php
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

// ログインチェックのためセッション開始
session_start();

// ログイン済みでない場合、ログインページへリダイレクト
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

// DB接続
$db = get_db_connect();

// ログイン済みユーザー情報を取得する
$user = get_login_user($db);

// 対象のユーザーIDのカート情報を取得する
$carts = get_user_carts($db, $user['user_id']);

// 合計金額を算出
$total_price = sum_carts($carts);

// cartページのクライアントソースファイルを読み込み
include_once VIEW_PATH . 'cart_view.php';