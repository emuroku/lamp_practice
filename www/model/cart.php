<?php 
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';
// DB操作に関する関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

// 指定のユーザーIDのカート情報を取得し、結果を配列で返す
function get_user_carts($db, $user_id){
  // SQL文の作成
  // cartsテーブルの指定のuser_idのレコードから、item_idでitemsテーブルと連結して、
  // 商品id、商品名、価格、在庫数、ステータス、画像、カートID、ユーザーID、購入数　を取得する
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = {$user_id}
  ";
  // SQLを実行し、結果を配列で返す
  return fetch_all_query($db, $sql);
}

// ユーザーIDと商品IDを指定し、カート情報を取得して結果を配列で返す
function get_user_cart($db, $user_id, $item_id){

  // SQL文の作成
  // cartsテーブルの指定のuser_idかつitem_idのレコードから、item_idでitemsテーブルと連結して、
  // 商品id、商品名、価格、在庫数、ステータス、画像、カートID、ユーザーID、購入数　を取得する
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = {$user_id}
    AND
      items.item_id = {$item_id}
  ";

  // 結果を配列で取得して返す
  return fetch_query($db, $sql);

}

// 指定のitem_idとuser_idでカート情報を追加する
function add_cart($db, $user_id, $item_id ) {
  // 指定のユーザーID、商品IDのカート情報を取得
  $cart = get_user_cart($db, $user_id, $item_id);
  // 取得できなかった場合（対象のユーザーIDと商品IDの組み合わせのカートレコードがなかった場合）
  if($cart === false){
    // カート情報をINSERTする
    return insert_cart($db, $user_id, $item_id);
  }
  // 取得に成功した場合（既に対象のユーザーIDと商品IDの組み合わせのカートレコードが存在する場合）
  // カート情報の購入数を+1になるよう更新する
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

// カート情報をcartsテーブルにINSERTし、結果を返す
function insert_cart($db, $user_id, $item_id, $amount = 1){
  // SQL文の作成：cartsテーブルに、ユーザーID、商品ID、購入数をセットしてINSERTする
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES( ?, ?, ? )
  ";

  // SQLインジェクション対策のため、バインドする値を用意
  $values = array(
    '1' => $item_id,
    '2' => $user_id,
    '3' => $amount
  );
  
  // SQLを実行し、結果を返す
  return execute_query($db, $sql, $values);
}

// cartsテーブルの指定のcart_idの購入数を更新する
function update_cart_amount($db, $cart_id, $amount){
  // SQLの作成：cartsテーブルの$cart_idのレコードのamountを入力値にセット
  $sql = "
    UPDATE
      carts
    SET
      amount = ?
    WHERE
      cart_id = ?
    LIMIT 1
  ";
  // SQLインジェクション対策のため、バインドする値を用意
  $values = array(
    '1' => $amount,
    '2' => $cart_id
  );
  // SQLを実行し、結果を返す
  return execute_query($db, $sql, $values);
}

// cartsテーブルから指定のIDのレコードを削除する
function delete_cart($db, $cart_id){
  // SQL文を作成：cartsテーブルの$cart_idのレコードを削除
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = ?
    LIMIT 1
  ";

  $values = array(
    '1' => $cart_id
  );

  // SQLを実行し、結果を返す
  return execute_query($db, $sql, $values);
}

// カート情報から各商品が購入可能かをチェックし、購入できなければfalseを返す。購入可能商品の場合、在庫更新チェックをし、問題なければカート情報を削除する
function purchase_carts($db, $carts){
  // 選択した商品が購入できるかどうかチェックし、できない場合falseを返す
  if(validate_cart_purchase($carts) === false){
    return false;
  }

  // 選択した商品が購入できる場合、$carts情報の各行に対して
  foreach($carts as $cart){

    // 商品の在庫数更新に失敗した場合
    if(update_item_stock(
        $db, 
        $cart['item_id'], 
        $cart['stock'] - $cart['amount']
      ) === false){
        // SESSIONに商品名を入れたエラーメッセージを設定
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  
  // ユーザーのカート情報を削除する
  delete_user_carts($db, $carts[0]['user_id']);
}

// 対象ユーザーのカート情報をすべて削除する
function delete_user_carts($db, $user_id){
  // SQL文の作成：cartsテーブルから、指定のユーザーIDのレコードをすべて削除する
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = ?
  ";

  $values = array(
    '1' => $user_id
  );

  // SQLを実行する
  execute_query($db, $sql, $values);
}

// cartデータから合計金額を計算して返す
function sum_carts($carts){
  // 結果の変数を初期化
  $total_price = 0;
  // $cartsの各行で
  foreach($carts as $cart){
    // $total_priceに価格*購入数　を加算
    $total_price += $cart['price'] * $cart['amount'];
  }
  // 合計値を返す
  return $total_price;
}

function validate_cart_purchase($carts){
  // $cartsデータが空っぽの場合
  if(count($carts) === 0){
    // SESSIONにエラーメッセージを設定
    set_error('カートに商品が入っていません。');
    // falseを返す
    return false;
  }

  // $cartsデータが空っぽでない場合、データの各行に対して
  foreach($carts as $cart){
    // 商品が公開状態出ない場合
    if(is_open($cart) === false){
      // SESSIONにエラーメッセージを設定
      set_error($cart['name'] . 'は現在購入できません。');
    }
    // 商品が公開状態だが、在庫数-購入数が0未満となる場合
    if($cart['stock'] - $cart['amount'] < 0){
      // SESSIONにエラーメッセージを設定
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  // エラーが1つ以上存在する場合、falseを返す
  if(has_error() === true){
    return false;
  }

  // エラーが一つもない場合、trueを返す
  return true;
}

