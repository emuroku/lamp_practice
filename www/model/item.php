<?php
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// DB接続に関する関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

// itemsテーブルから、指定のidの商品情報
// 商品id、商品名、在庫数、価格、画像、ステータス を取得し、結果を配列で返す
function get_item($db, $item_id){
  // SQL文の作成
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = {$item_id}
  ";
  // SQLを実行し結果を返す
  return fetch_query($db, $sql);
}

// 公開商品のみを抽出するかを指定の上、商品情報を取得して返す
function get_items($db, $is_open = false){
  // SQL文の作成：itemsテーブルから商品id、商品名、在庫数、価格、画像、ステータスを取得する
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';

  // is_openがTrue＝公開商品のみを指定する場合
  if($is_open === true){
    // SQL文に、status=1の行のみを取得する条件を追加する
    $sql .= '
      WHERE status = 1
    ';
  } 

  // fetchAllで取得結果のデータを配列で取得して返す
  return fetch_all_query($db, $sql);
}

// ステータスを問わず全ての商品情報を取得して返す
function get_all_items($db){
  return get_items($db);
}

// ステータスが公開の商品情報を取得して返す
function get_open_items($db){
  return get_items($db, true);
}

// 商品を新規登録する
function regist_item($db, $name, $price, $stock, $status, $image){
  // 画像データをチェックし、不正でない場合は画像名を取得する
  $filename = get_upload_filename($image);
  // 入力情報をチェックし、1つ以上チェックNGの場合はfalseを返す
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  // 全てのチェックが通った場合は、商品情報を登録して結果を返す
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

// 商品情報をトランザクション処理を用い新規登録する
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){

  $db->beginTransaction();
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
  
}

function insert_item($db, $name, $price, $stock, $filename, $status){
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES('{$name}', {$price}, {$stock}, '{$filename}', {$status_value});
  ";

  return execute_query($db, $sql);
}

// 指定の商品IDの商品のステータスを更新する
function update_item_status($db, $item_id, $status){
  // SQLの作成：itemsテーブルの、$item_idのレコードのstatusを入力値にセット
  $sql = "
    UPDATE
      items
    SET
      status = {$status}
    WHERE
      item_id = {$item_id}
    LIMIT 1
  ";
  // SQLを実行し、結果を返す
  return execute_query($db, $sql);
}

// 指定の商品IDの商品の在庫数を更新する
function update_item_stock($db, $item_id, $stock){
  // SQLの作成：itemsテーブルの、$item_idのレコードのstockを入力値にセット
  $sql = "
    UPDATE
      items
    SET
      stock = {$stock}
    WHERE
      item_id = {$item_id}
    LIMIT 1
  ";
  // SQLを実行し、結果を返す
  return execute_query($db, $sql);
}

// 指定のIDの商品情報を削除し、結果をbool値で返す
function destroy_item($db, $item_id){
  // itemsテーブルから、指定のidの商品情報を取得する
  $item = get_item($db, $item_id);
  // 商品情報が取得できなかった場合
  if($item === false){
    // falseを返す
    return false;
  }
  
  // トランザクション処理を用い商品のレコードを削除
  $db->beginTransaction();
  // 商品の情報および画像データを削除に成功した場合
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    // トランザクション成功、コミット  
    $db->commit();
    // trueを返す
    return true;
  }
  // データ処理に失敗した場合はロールバック処理
  $db->rollback();
  // falseを返す
  return false;
}

// 指定のIDの商品のレコードを削除する
function delete_item($db, $item_id){
  // SQLを作成：itemsテーブルから item_idが指定値の行を削除
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = {$item_id}
    LIMIT 1
  ";
  
  // SQLを実行し、結果を返す
  return execute_query($db, $sql);
}


// 非DB

function is_open($item){
  return $item['status'] === 1;
}

// 入力された新規商品情報の各項目が正規なものかをチェックし、全て問題なければTRUEを返す
function validate_item($name, $price, $stock, $filename, $status){
  // 商品名のチェック
  $is_valid_item_name = is_valid_item_name($name);
  // 価格のチェック
  $is_valid_item_price = is_valid_item_price($price);
  // 在庫数のチェック
  $is_valid_item_stock = is_valid_item_stock($stock);
  // 画像のチェック
  $is_valid_item_filename = is_valid_item_filename($filename);
  // ステータスのチェック
  $is_valid_item_status = is_valid_item_status($status);

  // 全てチェックが通っていればTRUEを返す
  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

function is_valid_item_name($name){
  $is_valid = true;
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_price($price){
  $is_valid = true;
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_stock($stock){
  $is_valid = true;
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_status($status){
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}