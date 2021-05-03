<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// ログインチェックのためセッション開始
session_start();

// ログイン済みの場合、ホームページへリダイレクト
if(is_logined() === true){
  redirect_to(HOME_URL);
}

// 新規登録ページのクライアントソースファイルを読み込み
include_once VIEW_PATH . 'signup_view.php';



