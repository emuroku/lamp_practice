<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザーデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

// ログインチェックのためセッション開始
session_start();

// ログイン済みユーザーの場合、ホームページへリダイレクト
if(is_logined() === true){
  redirect_to(HOME_URL);
}

// トークンのチェック
$token = get_post('token');
// dd($token);

// Sessionのトークンと一致しない場合はエラーメッセージを設定
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです');
}

// POSTされたユーザー名を取得
$name = get_post('name');
// POSTされたパスワードを取得
$password = get_post('password');

// DBへ接続
$db = get_db_connect();

// 入力されたユーザー名とパスワードをチェックし、ログイン処理をする
$user = login_as($db, $name, $password);
// ログイン処理に失敗した場合
if( $user === false){
  // エラーメッセージをセットし、ログインページへリダイレクトする
  set_error('ログインに失敗しました。');
  redirect_to(LOGIN_URL);
}

// ログイン処理が通った場合、SESSIONにメッセージを保存する
set_message('ログインしました。');
// ユーザー情報のtypeカラムがadminであった場合
if ($user['type'] === USER_TYPE_ADMIN){
  // adminページへリダイレクトする
  redirect_to(ADMIN_URL);
}
// ホームページへリダイレクトする
redirect_to(HOME_URL);