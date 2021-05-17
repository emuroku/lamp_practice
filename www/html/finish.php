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

// 指定のユーザーIDのカート情報を取得
$carts = get_user_carts($db, $user['user_id']);

// 購入処理のため、トランザクション開始
// -----ここからトランザクション処理-----
$db->beginTransaction();

// try-catch開始
try {
  //現在日時を取得
  $datetime = date('Y-m-d H:i:s');

  // 商品が購入可能かをチェックし、falseの場合
  if (purchase_carts($db, $carts) === false) {
    // SESSIONにエラーメッセージを設定
    set_error('商品が購入できませんでした。');
    // カートページへリダイレクト
    redirect_to(CART_URL);
  }
  // 合計金額を取得
  $total_price = sum_carts($carts);

  // ordersテーブルの更新
  // Sessionのuser_idを取得
  $user_id = get_session('user_id');
  if (insert_order($db, $user_id, $datetime) === false) {
    // 商品情報の登録に失敗した場合、SESSIONにエラーメッセージを設定
    set_error('商品の登録に失敗しました。');
    // カートページへリダイレクト
    redirect_to(CART_URL);
  } else {
    // order_detailsテーブルの更新
    // 直前にordersテーブルへinsertしたorder_idを取得
    $order_id = $db->lastInsertId();

    //$order_idからorder_detailsテーブルの更新
    if (insert_order_details($db, $order_id, $carts) === false) {
      // 商品情報の登録に失敗した場合、SESSIONにエラーメッセージを設定
      set_error('商品の登録に失敗しました。');
      // カートページへリダイレクト
      redirect_to(CART_URL);
    }
  }
  // コミット処理
  $db->commit();
  // 購入失敗時にエラーを設定する
} catch (PDOException $e) {
  set_error('購入処理中のエラーが発生しました。');
  // ロールバック処理
  $db->rollback();
  // 例外をスロー
  throw $e;
  redirect_to(CART_URL);
}

// 購入完了ページのクライアントソースファイルを読み込み
include_once '../view/finish_view.php';
