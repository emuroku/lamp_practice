<?php

function get_db_connect(){
  // MySQL用のDSN文字列
  $dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST .';charset='.DB_CHARSET;
 
  try {
    // データベースに接続
    $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    exit('接続できませんでした。理由：'.$e->getMessage() );
  }
  return $dbh;
}

// 指定のSQL文を実行し、実行結果を取得して返す。処理中にエラーが発生した場合はfalseを返す
function fetch_query($db, $sql, $params = array()){
  try{
    // statementに指定のSQL文をセット
    $statement = $db->prepare($sql);
    // SQLを実行する
    $statement->execute($params);
    // 実行結果を取得して返す
    return $statement->fetch();
  }catch(PDOException $e){
    // try処理内でエラーが発生した場合は、エラーメッセージを設定する
    set_error('データ取得に失敗しました。');
  }
  // エラーが発生した場合はfalseを返す
  return false;
}

function fetch_all_query($db, $sql, $params = array()){
  try{
    $statement = $db->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
  }catch(PDOException $e){
    set_error('データ取得に失敗しました。');
  }
  return false;
}

function execute_query($db, $sql, $params = array()){
  try{
    $statement = $db->prepare($sql);
    return $statement->execute($params);
  }catch(PDOException $e){
    set_error('更新に失敗しました。');
  }
  return false;
}