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

// Sessionのトークンと一致しない場合はエラーメッセージを設定
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです');
}

// POSTされたカートIDを取得する
$cart_id = get_post('cart_id');

// カート情報の削除に成功した場合
if(delete_cart($db, $cart_id)){
  // SESSIONに完了メッセージを設定
  set_message('カートを削除しました。');

// 削除に失敗した場合 
} else {
  // SESSIONにエラーメッセージを設定
  set_error('カートの削除に失敗しました。');
}

// カートページへリダイレクト
redirect_to(CART_URL);