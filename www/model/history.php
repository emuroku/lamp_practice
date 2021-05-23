<?php
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// DB関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

// ユーザー関数ファイルを読み込み
require_once MODEL_PATH . 'user.php';

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
  if(is_admin($user) === TRUE){
    $sql = $sql . "
    GROUP BY orders.order_id;
    ";
  } // adminユーザーでない場合は指定のユーザーIDのデータを取得する
  else{
    $sql = $sql . "
    WHERE
    orders.user_id = {$user['user_id']}
    GROUP BY orders.order_id;
    ";
  }

  // SQLを実行し結果を返す
  return fetch_all_query($db, $sql);
}

// ユーザーIDから購入履歴の一覧情報を取得し、結果を返す
function get_order_details($db, $user_id)
{
  // SQL文の作成
  $sql = "
    SELECT
      orders.order_id, 
      orders.purchased,
      orders.user_id,
      order_details.price * order_details.amount,
    FROM
      orders
    JOIN
      orders.order_id = order_details.order_id
    WHERE
      orders.user_id = {$user_id}
  ";
  // SQLを実行し結果を返す
  return fetch_query($db, $sql);
}
