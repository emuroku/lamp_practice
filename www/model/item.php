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

// 商品情報を新規登録する
function insert_item($db, $name, $price, $stock, $filename, $status){
  // ステータスに設定されている値を代入する
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  // SQL文の作成： itemsテーブルに、商品名、価格、在庫数、画像、ステータスをINSERTする
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES( ?, ?, ?, ?, ? );
  ";

  // SQLインジェクション対策のため、executeの引数にセットする配列を準備
  $values = [$name, $price, $stock, $filename, $status_value];
  // SQLを実行し、結果を返す      
  return execute_query($db, $sql, $values);
}

// 指定の商品IDの商品のステータスを更新する
function update_item_status($db, $item_id, $status){
  // SQLの作成：itemsテーブルの、$item_idのレコードのstatusを入力値にセット
  $sql = "
    UPDATE
      items
    SET
      status = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";

  // SQLインジェクション対策のため、executeの引数にセットする配列を準備
  $values = [$status, $item_id];
  // SQLを実行し、結果を返す
  return execute_query($db, $sql, $values);
}

// 指定の商品IDの商品の在庫数を更新する
function update_item_stock($db, $item_id, $stock){
  // SQLの作成：itemsテーブルの、$item_idのレコードのstockを入力値にセット
  $sql = "
    UPDATE
      items
    SET
      stock = ?
    WHERE
      item_id = ?
    LIMIT 1
  ";

  // SQLインジェクション対策のため、executeの引数にセットする配列を準備
  $values = [$stock, $item_id];

  // SQLを実行し、結果を返す
  return execute_query($db, $sql, $values);
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
      item_id = ?
    LIMIT 1
  ";

  // SQLインジェクション対策のため、executeの引数にセットする配列を準備
  $values = [$item_id];
  
  // SQLを実行し、結果を返す
  return execute_query($db, $sql, $values);
}


// 非DB

// 商品が公開状態かどうかを判定して結果を返す
function is_open($item){
  // $itemデータのstatusカラムが公開（1）であればTrueを返す
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

// 入力された新規商品情報の商品名が正規なものかをチェックし、問題なければTRUEを返す
function is_valid_item_name($name){
  // 結果の初期化
  $is_valid = true;
  // 商品名の文字数が指定の範囲内に収まっていない場合
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    // SESSIONにエラーメッセージを設定
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    // 結果を更新
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 入力された新規商品情報の価格が正規なものかをチェックし、結果をbool値で返す
function is_valid_item_price($price){
  // 結果の初期化
  $is_valid = true;
  // 価格が正規の値で入力されていない場合
  if(is_positive_integer($price) === false){
    // SESSIONにエラーメッセージを設定
    set_error('価格は0以上の整数で入力してください。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 入力された新規商品情報の在庫数が正規なものかをチェックし、結果をbool値で返す
function is_valid_item_stock($stock){
  // 結果の初期化
  $is_valid = true;
  // 在庫数が正規の値で入力されていない場合
  if(is_positive_integer($stock) === false){
    // SESSIONにエラーメッセージを設定
    set_error('在庫数は0以上の整数で入力してください。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 入力された画像データが正規かをチェックし、結果をbool値で返す
function is_valid_item_filename($filename){
  // 結果の初期化
  $is_valid = true;
  // ファイル名が空の場合、falseを返す
  if($filename === ''){
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 入力されたステータスの値が正規化をチェックし、結果をbool値で返す
function is_valid_item_status($status){
  // 結果の初期化
  $is_valid = true;
  // ステータスの値がセットされていない場合
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 現在のページ番号を取得する
function get_current_page(){
  // デフォルトで1ページ目を設定
  $page = 1;
  // GETパラメータがなにもない場合は、1ページ目を取得
  if(get_getparam('page') === ''){
    return $page;
  } 
  // 空でない場合はGETに入っている値を代入して返す
  $page = get_getparam('page');
  return $page;
}

// 公開中の商品の総数を取得する
function get_assortment($db){
  // SQL文の作成
  $sql = "
    SELECT
     COUNT(*)
    FROM
      items
    WHERE
      status = 1;
  ";
  // SQLを実行し結果を返す
  return fetch_query($db, $sql);
}

// 必要な商品一覧ページ数を計算する
function get_pages($db, $assortment){
  return ceil($assortment / DISPLAYED_ITEMS);
}

// ページ数から表示する商品の情報を取得する
function get_display_items($db, $current_page){
  // 何行目からデータを読むかを算出
  $min = ($current_page-1)*DISPLAYED_ITEMS;
  // 商品情報を取得して返す
  return get_items_limited($db, $min);
}

// ページ番号に応じた指定数の商品情報を取得して返す
function get_items_limited($db, $min){
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
    WHERE 
      status = 1
    LIMIT ?, ?; 
    ';
  // SQLインジェクション対策のため、executeの引数にセットする配列を準備
  $values = [$min, DISPLAYED_ITEMS];
  // fetchAllで取得結果のデータを配列で取得して返す
  return fetch_all_query($db, $sql, $values);
}

