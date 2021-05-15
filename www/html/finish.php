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

// トークンのチェック
$token = get_post('token');

// Sessionのトークンと一致しない場合はエラーメッセージを設定
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです');
  // カートページへリダイレクト
  redirect_to(CART_URL);  
}

// ログイン済みでない場合、ログインページへリダイレクト
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

// DB接続
$db = get_db_connect();

// ログイン済みユーザー情報を取得する
$user = get_login_user($db);

// 指定のユーザーIDのカート情報を取得
$carts = get_user_carts($db, $user['user_id']);

// 商品が購入可能かをチェックし、falseの場合
if(purchase_carts($db, $carts) === false){
  // SESSIONにエラーメッセージを設定
  set_error('商品が購入できませんでした。');
  // カートページへリダイレクト
  redirect_to(CART_URL);
} 

// 合計金額を取得
$total_price = sum_carts($carts);

// 購入完了ページのクライアントソースファイルを読み込み
include_once '../view/finish_view.php';