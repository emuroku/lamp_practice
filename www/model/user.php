<?php
// 汎用関数ファイルを読み込み
require_once MODEL_PATH . 'functions.php';

// DB関数ファイルを読み込み
require_once MODEL_PATH . 'db.php';

// usersテーブルから指定のユーザーIDのユーザーデータ
// id, ユーザー名、パスワード、ユーザータイプを取得する
function get_user($db, $user_id){

  // SQLの作成
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      user_id = {$user_id}
    LIMIT 1
  ";
  // SQL文を実行して取得してきた結果データを返す
  return fetch_query($db, $sql);
}

// usersテーブルから指定のユーザー名のユーザーデータ
// id, ユーザー名、パスワード、ユーザータイプを取得する
function get_user_by_name($db, $name){

  // SQL文の作成
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      name = '{$name}'
    LIMIT 1
  ";

  // SQL文を実行して取得してきた結果データを返す
  return fetch_query($db, $sql);
}

// 入力されたユーザー名とパスワードをチェックし、ログイン処理をする。成功した場合はユーザーデータを返し、失敗した場合はfalseを返す
function login_as($db, $name, $password){
  // ユーザー名からユーザーデータを取得
  $user = get_user_by_name($db, $name);
  // ユーザーデータ取得に失敗した場合、もしくは取得してきたユーザーのパスワードが入力値と一致しない場合
  if($user === false || $user['password'] !== $password){
    // falseを返す
    return false;
  }
  // falseにならなかった場合は、SESSIONのuser_idに該当のユーザーIDをセットする
  set_session('user_id', $user['user_id']);
  // ユーザーデータの入った配列を返す
  return $user;
}

// ログイン済みユーザーの情報をusersテーブルから取得する
function get_login_user($db){
  // SESSION情報からuser_idキーに設定されている値を取得する
  $login_user_id = get_session('user_id');

  // 取得したuser_idからユーザーデータをusersテーブルより取得し、配列を返す
  return get_user($db, $login_user_id);
}

// 登録可能なユーザー情報かをチェックし、すべて問題なければ新規登録を行い、結果の配列を取得して返す。失敗した場合はfalseを返す
function regist_user($db, $name, $password, $password_confirmation) {
  // 登録可能なユーザー情報でない場合
  if( is_valid_user($name, $password, $password_confirmation) === false){
    // falseを返す
    return false;
  }
  // チェックが通った（登録可能なユーザー情報であった）場合、新規登録をし結果の配列を取得する
  return insert_user($db, $name, $password);
}

// adminユーザーかどうかを判定し、結果を返す
function is_admin($user){
  // $user配列の'type'キーの値がADMINに相当する場合、trueを返す
  return $user['type'] === USER_TYPE_ADMIN;
}

function is_valid_user($name, $password, $password_confirmation){
  // 短絡評価を避けるため一旦代入。
  // 登録可能なユーザー名かどうかの判定結果を取得する
  $is_valid_user_name = is_valid_user_name($name);
  // 登録可能なパスワードかどうかの判定結果を取得する
  $is_valid_password = is_valid_password($password, $password_confirmation);
  // 判定結果を返す
  return $is_valid_user_name && $is_valid_password ;
}

// 登録可能なユーザー名かどうかを判定し、結果をbool値で返す
function is_valid_user_name($name) {
  // 結果の初期化
  $is_valid = true;
  // 入力された$nameの文字数が条件を満たしていない場合
  if(is_valid_length($name, USER_NAME_LENGTH_MIN, USER_NAME_LENGTH_MAX) === false){
    // SESSIONにエラーメッセージをセットする
    set_error('ユーザー名は'. USER_NAME_LENGTH_MIN . '文字以上、' . USER_NAME_LENGTH_MAX . '文字以内にしてください。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 入力された$nameが指定の形式を満たしていない場合
  if(is_alphanumeric($name) === false){
    // SESSIONにエラーメッセージをセットする
    set_error('ユーザー名は半角英数字で入力してください。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 登録可能なパスワードかどうかを判定し、結果をbool値で返す
function is_valid_password($password, $password_confirmation){
  // 結果の初期化
  $is_valid = true;
  // 入力された$passwordの文字数が条件を満たしていない場合
  if(is_valid_length($password, USER_PASSWORD_LENGTH_MIN, USER_PASSWORD_LENGTH_MAX) === false){
    // SESSIONにエラーメッセージをセットする
    set_error('パスワードは'. USER_PASSWORD_LENGTH_MIN . '文字以上、' . USER_PASSWORD_LENGTH_MAX . '文字以内にしてください。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 入力された$passwordが指定の形式を満たしていない場合
  if(is_alphanumeric($password) === false){
    // SESSIONにエラーメッセージをセットする
    set_error('パスワードは半角英数字で入力してください。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 確認用パスワードと一致しない場合
  if($password !== $password_confirmation){
    // SESSIONにエラーメッセージをセットする
    set_error('パスワードがパスワード(確認用)と一致しません。');
    // 結果をfalseに更新
    $is_valid = false;
  }
  // 結果を返す
  return $is_valid;
}

// 新規登録ユーザーのデータをusersテーブルにINSERTする
function insert_user($db, $name, $password){
// SQLの作成：usersのname, passwordカラムにユーザー名とパスワードをINSERTする
  $sql = "
    INSERT INTO
      users(name, password)
    VALUES ( ?, ? );
  ";
  // SQLインジェクション対策のため、バインドする値を用意
  $values = array(
    '1' => $name,
    '2' => $password
  );
  // SQLを実行して取得したデータを返す
  return execute_query($db, $sql, $values);
}

