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

// ログイン済みでない場合、ログインページへリダイレクトする
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

// DBへ接続
$db = get_db_connect();

// ユーザーの情報をusersテーブルから取得する
$user = get_login_user($db);

// Adminユーザーでない場合、ログインページへリダイレクトする
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

// POSTされたitem_idを取得
$item_id = get_post('item_id');
// POSTされたchanges_toパラメータ（ステータス更新）を取得
$changes_to = get_post('changes_to');

// changes_toパラメータがopenの場合
if($changes_to === 'open'){
  // itemsテーブルの指定idのレコードのstatusを公開に設定する
  update_item_status($db, $item_id, ITEM_STATUS_OPEN);
  // SESSIONにメッセージを設定する
  set_message('ステータスを変更しました。');

// changes_toパラメータがcloseの場合   
}else if($changes_to === 'close'){
  // itemsテーブルの指定idのレコードのstatusを非公開に設定する
  update_item_status($db, $item_id, ITEM_STATUS_CLOSE);
  // SESSIONにメッセージを設定する
  set_message('ステータスを変更しました。');

// changes_toパラメータがopenでもcloseでもない場合
}else {
  // SESSIONにエラーメッセージを設定する
  set_error('不正なリクエストです。');
}

// adminページへリダイレクト
redirect_to(ADMIN_URL);