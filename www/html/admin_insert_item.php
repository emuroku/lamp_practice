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

// POSTされた商品名を取得
$name = get_post('name');
// POSTされた価格を取得
$price = get_post('price');
// POSTされたステータスを取得
$status = get_post('status');
// POSTされた在庫数を取得
$stock = get_post('stock');
// 送信された画像データを取得
$image = get_file('image');

// 商品情報の登録を行い、成功した場合
if(regist_item($db, $name, $price, $stock, $status, $image)){
  // SESSIONに完了のメッセージを設定
  set_message('商品を登録しました。');

// 商品情報の登録に失敗した場合 
}else {
  // SESSIONにエラーメッセージを設定
  set_error('商品の登録に失敗しました。');
}

// adminページへリダイレクト
redirect_to(ADMIN_URL);