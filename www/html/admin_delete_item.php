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

// ログイン済みユーザーの情報を取得する
$user = get_login_user($db);

// adminユーザーでない場合、ログインページへリダイレクト
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

// postされた商品IDを取得する
$item_id = get_post('item_id');

// 商品情報の削除に成功した場合
if(destroy_item($db, $item_id) === true){
  // SESSIONにメッセージを設定
  set_message('商品を削除しました。');
}
// 削除に失敗した場合
else {
  // SESSIONにエラーメッセージを設定
  set_error('商品削除に失敗しました。');
}

// adminページへリダイレクト
redirect_to(ADMIN_URL);