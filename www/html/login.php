<?php
// 定数ファイルを読み込み
require_once '../conf/const.php';
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// ログインチェックのためセッション開始
session_start();

// ログイン済みユーザーの場合
if(is_logined() === true){
  // ホームページへリダイレクトする
  redirect_to(HOME_URL);
}

// ログインページのクライアントソースファイルを読み込み
include_once VIEW_PATH . 'login_view.php';