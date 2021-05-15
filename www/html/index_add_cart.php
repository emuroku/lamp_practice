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

// POSTされたitem_idを取得
$item_id = get_post('item_id');


// カート情報の追加処理に成功した場合
if(add_cart($db,$user['user_id'], $item_id)){
  // SESSIONに完了メッセージを設定
  set_message('カートに商品を追加しました。');

// カート情報の追加処理に失敗した場合  
} else {
  // SESSIONにエラーメッセージを設定
  set_error('カートの更新に失敗しました。');
}

// ホームページへリダイレクト
redirect_to(HOME_URL);