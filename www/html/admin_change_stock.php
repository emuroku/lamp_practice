<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザーデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';
// 商品データに関する関数ファイルを読み込み
require_once MODEL_PATH . 'item.php';

// ログインチェックのためセッション開始
session_start();

// ログイン済み出ない場合、ログインページへリダイレクト
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

// DB接続
$db = get_db_connect();

// ログイン済みユーザー情報を取得する
$user = get_login_user($db);

// adminユーザー出ない場合は、ログインページへリダイレクト
if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

// トークンのチェック
$token = get_post('token');
// Sessionのトークンと一致しない場合はエラーメッセージを設定
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです');
  // adminページへリダイレクト
  redirect_to(ADMIN_URL);
}

// POSTされた商品IDを取得
$item_id = get_post('item_id');
// POSTされた在庫数を取得
$stock = get_post('stock');

// 在庫数の更新に成功した場合
if(update_item_stock($db, $item_id, $stock)){
  // SESSIONにメッセージを設定
  set_message('在庫数を変更しました。');

// 更新に失敗した場合
} else {
  // SESSIONにエラーメッセージを設定
  set_error('在庫数の変更に失敗しました。');
}

// adminページへリダイレクト
redirect_to(ADMIN_URL);