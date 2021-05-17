<?php
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// DB関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

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

    // cart情報からitem_idの商品名と価格を取得
    $cart_details = get_purchased_item($db, $cart['item_id']);
    // SQLインジェクション対策のため、executeの引数にセットする配列を準備
    $values = [$order_id, $cart['item_id'], $cart_details['name'], $cart_details['price'], $cart['amount']];
    // SQLを実行し、結果を返す
    execute_query($db, $sql, $values);
  }
}

// itemsテーブルから、指定のidのhistoryテーブルに必要な商品情報を取得
// 商品idから当時の商品名、価格を取得し、結果を配列で返す
function get_purchased_item($db, $item_id)
{
  // SQL文の作成
  $sql = "
      SELECT
        name,
        price
      FROM
        items
      WHERE
        item_id = {$item_id}
    ";

  // SQLを実行し結果を返す
  return fetch_query($db, $sql);
}
