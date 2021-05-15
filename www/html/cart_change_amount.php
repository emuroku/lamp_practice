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

// トークンのチェック
$token = get_post('token');
// dd($token);

// Sessionのトークンと一致しない場合はエラーメッセージを設定
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです');
}

// POSTされたカートIDを取得
$cart_id = get_post('cart_id');
// POSTされた購入数を取得
$amount = get_post('amount');

// 購入数の更新に成功した場合
if(update_cart_amount($db, $cart_id, $amount)){
  // SESSIONにメッセージを設定
  set_message('購入数を更新しました。');

// 更新に失敗した場合 
} else {
  // SESSIONにエラーメッセージを設定
  set_error('購入数の更新に失敗しました。');
}

// カートページへリダイレクト
redirect_to(CART_URL);