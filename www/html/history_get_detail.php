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
// 購入履歴データに関する関数ファイルを読み込み
require_once MODEL_PATH . 'history.php';


// ログインチェックのためセッション開始
session_start();

// トークンのチェック
$token = get_post('token');

// Sessionのトークンと一致しない場合はエラーメッセージを設定
if (is_valid_csrf_token($token) === false) {
    set_error('不正なリクエストです');
    // カートページへリダイレクト
    redirect_to(CART_URL);
}

// ログイン済みでない場合、ログインページへリダイレクト
if (is_logined() === false) {
    redirect_to(LOGIN_URL);
}

// DB接続
$db = get_db_connect();

// ログイン済みユーザー情報を取得する
$user = get_login_user($db);

// POSTされた注文番号を取得する
$order_id = get_post('order_id');

// order_idがSESSIONに保存されたユーザーIDのものかをチェックする
// adminユーザーでない場合のみ
if (is_admin($user) !== TRUE) {
    if (is_valid_order($db, $order_id) !== TRUE) {
        // 照合しない場合は、エラーメッセージをセット
        set_error('明細情報が取得できませんでした。');
        // 購入履歴画面へリダイレクト
        redirect_to(HISTORY_URL);
    }
}
// order_idに紐づく購入商品情報を取得する
$order_details = get_order_details($db, $order_id);

// 明細情報からヘッダー情報を取得する
$order_summary = get_order_summary($order_details);

// 購入明細ページのクライアントソースファイルを読み込み
include_once '../view/history_detail_view.php';
