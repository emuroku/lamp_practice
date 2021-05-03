<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// ユーザーデータに関する関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

// ログインチェックのためセッション開始
session_start();

// ログイン済みの場合、ホームページへリダイレクトする
if(is_logined() === true){
  redirect_to(HOME_URL);
}

// POSTされたユーザー名を取得
$name = get_post('name');
// POSTされたパスワードを取得
$password = get_post('password');
// POSTされた確認用パスワードを取得
$password_confirmation = get_post('password_confirmation');

// DBへ接続
$db = get_db_connect();

// try～catch処理でユーザー新規登録を行う
try{
  // ユーザー登録を行い、結果を取得する
  $result = regist_user($db, $name, $password, $password_confirmation);
  // ユーザー登録に失敗した場合
  if( $result=== false){
    // SESSIONにエラーメッセージを設定する
    set_error('ユーザー登録に失敗しました。');
    // 新規登録ページへリダイレクトする
    redirect_to(SIGNUP_URL);
  }
  // try処理中にエラーが発生した場合
}catch(PDOException $e){
  // SESSIONにエラーメッセージを設定する
  set_error('ユーザー登録に失敗しました。');
  // 新規登録ページへリダイレクトする
  redirect_to(SIGNUP_URL);
}

// エラーが発生しなければ、登録完了のメッセージをセットする
set_message('ユーザー登録が完了しました。');

// 入力されたユーザー名とパスワードをチェックし、ログイン処理する
login_as($db, $name, $password);
// ホームページへリダイレクトする
redirect_to(HOME_URL);