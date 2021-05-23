<?php
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// DB関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

// ユーザー関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

// 購入が成功したcart情報をordersテーブルにINSERTし、結果を返す
function insert_order($db, $user_id, $datetime)
{
  // SQL文の作成：ordersテーブルに、ユーザーID、日時情報をセットしてINSERTする
  $sql = "
    INSERT INTO
      orders(
        user_id,
        purchased
      )
    VALUES( ?, ? )
  ";

  // SQLインジェクション対策のため、executeの引数にセットする配列を準備
  $values = [$user_id, $datetime];

  return execute_query($db, $sql, $values);
}

// 購入が成功したcart情報をorder_detailsテーブルにINSERTする
function insert_order_details($db, $order_id, $carts)
{
  // cartsの各行で、item_idからitemsテーブルの情報を取得し、order_detailsにINSERTする
  foreach ($carts as $cart) {
    // order_detailsテーブルへ商品id、商品名、購入数、購入時価格を追加
    // SQL文の作成：ordersテーブルに、ユーザーID、日時情報をセットしてINSERTする
    $sql = "
    INSERT INTO
      order_details(
        order_id,  
        item_id,
        name,
        price,
        amount
      )
    VALUES( ?, ?, ?, ?, ? )
  ";
    // SQLインジェクション対策のため、executeの引数にセットする配列を準備
    $values = [$order_id, $cart['item_id'], $cart['name'], $cart['price'], $cart['amount']];
    // SQLを実行し、結果を返す
    if (execute_query($db, $sql, $values) === false) {
      return false;
    };
  }
}

// ユーザーIDから購入履歴の一覧情報を取得し、結果を返す
function get_orders($db, $user)
{
  // SQL文の作成
  // order_idでordersとorder_detailsを結合し、注文番号と日時、価格を計算して集計する。order_idでグルーピングする。
  $sql = "
    SELECT
      orders.order_id,
      orders.purchased,
      orders.user_id,
      SUM(order_details.price * order_details.amount) AS sum
    FROM
      orders
    JOIN
      order_details
    ON 
      orders.order_id = order_details.order_id
  ";
  // adminユーザーの場合は全ユーザーのデータを取得
  if (is_admin($user) === TRUE) {
    $sql = $sql . "
    GROUP BY orders.order_id
    ORDER BY orders.purchased DESC;
    ";
  } // adminユーザーでない場合は指定のユーザーIDのデータを取得する
  else {
    $sql = $sql . "
    WHERE
    orders.user_id = {$user['user_id']}
    GROUP BY orders.order_id
    ORDER BY orders.purchased DESC;
    ";
  }

  // SQLを実行し結果を返す
  return fetch_all_query($db, $sql);
}

// order_idから購入履歴の明細情報を取得し、結果を返す
function get_order_details($db, $order_id)
{
  // SQL文の作成
  $sql = "
    SELECT
      orders.order_id, 
      orders.purchased,
      orders.user_id,
      order_details.name,
      order_details.price,
      order_details.amount
    FROM
      order_details
    JOIN
      orders
    ON 
      orders.order_id = order_details.order_id
    WHERE
      order_details.order_id = ?;
  ";
  // SQLにセットするidを指定
  $value = [$order_id];
  // SQLを実行し結果を返す
  return fetch_all_query($db, $sql, $value);
}

// 注文番号を受け取り、セッションにセットされているユーザーIDによる注文かどうかを確認して結果をbool値で返す
function is_valid_order($db, $order_id)
{
  // 指定のorder_idのレコードにセットされているユーザーIDを取得
  // SQLを作成
  $sql = "
  SELECT
    user_id
  FROM
    orders
  WHERE
    order_id = ?;  
  ";
  // SQLにセットするvalueを指定
  $value = [$order_id];
  // 指定のorder_idを購入したユーザーIDを取得
  $result = fetch_query($db, $sql, $value);
  // sessionにセットされているユーザーIDを取得し、DBから取得したユーザーIDと一致するかどうかをbool値で返す
  return $result['user_id'] === get_session('user_id');
}

// 取得した明細情報から、明細画面のトップに掲載する注文番号、購入日時、合計金額を取得して配列で返す
function get_order_summary($order_details)
{
  // 結果を初期化
  $summary = array();

  // 注文番号を設定
  $summary['order_id'] = $order_details[0]['order_id'];
  // 購入日時を設定
  $summary['purchased'] = $order_details[0]['purchased'];
  // 合計金額を設定
  // 金額の算出
  $sum = 0;
  foreach ($order_details as $value) {
    $sum += $value['price'] * $value['amount'];
  }
  $summary['sum'] = $sum;

  return $summary;
}
