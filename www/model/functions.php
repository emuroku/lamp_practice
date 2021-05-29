<?php
// 変数や配列の内容を出力する
function dd($var){
  var_dump($var);
  exit();
}

// 引数のURLへリダイレクトする
function redirect_to($url){
  header('Location: ' . $url);
  exit;
}

// $name のGETパラメータを取得する。値が入っていない場合は空の文字列を返す
function get_get($name){
  // $nameのGETパラメータが設定されている場合
  if(isset($_GET[$name]) === true){
    // GETパラメータを返す
    return $_GET[$name];
  };
  // GETパラメータが設定されていない場合は、空の文字列を返す
  return '';
}

// $name のPOSTパラメータを取得する。値が入っていない場合は空の文字列を返す
function get_post($name){
  // $nameのPOSTパラメータが設定されている場合
  if(isset($_POST[$name]) === true){
    // POSTパラメータを返す
    return $_POST[$name];
  };
  // POSTパラメータが設定されていない場合は、空の文字列を返す
  return '';
}

// POSTメソッドによりアップロードされたファイルの内容を返す。値が入っていない場合は空の配列を返す
function get_file($name){
  // _FILESに値がアップロードされている場合
  if(isset($_FILES[$name]) === true){
    // アップロードされた値を返す
    return $_FILES[$name];
  };
  // _FILESに値がない場合は、空の配列を返す
  return array();
}

// セッション変数に設定されている$nameキーの値を取得する。設定されていない場合は空の文字列を返す
function get_session($name){
  // セッション変数に値が設定されている場合
  if(isset($_SESSION[$name]) === true){
    // $nameキーのSESSION情報に保存されている値を返す
    return $_SESSION[$name];
  };
  // 設定されていない場合は、空の文字列を返す
  return '';
}

// SESSIONの$nameキーの値に$valueを設定する
function set_session($name, $value){
  $_SESSION[$name] = $value;
}

// SESSIONの__errorsキーに$errorを追加する
function set_error($error){
  $_SESSION['__errors'][] = $error;
}


function get_errors(){
  // __errorsキーのセッションに設定されている値を取得する
  $errors = get_session('__errors');
  // 取得した$errorが空の場合
  if($errors === ''){
    // 空の配列を返す
    return array();
  }
  // 取得した$errorsが空ではない場合
    // SESSIONの__errorsキーに空の配列を代入する
  set_session('__errors',  array());
    // 取得した$errorsを返す
  return $errors;
}

// エラーメッセージが存在しているかを判定する
function has_error(){
  // SESSION変数に__errorsが設定されており、且つ__errorsに値が1つ以上入っている場合はTRUEを返す
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}

// SESSION変数の__messagesキーの値に$messageを設定する
function set_message($message){
  $_SESSION['__messages'][] = $message;
}

// SESSION変数の__messagesキーに設定されている値を取得する
function get_messages(){
  // SESSION変数の__messagesキーに設定されている値を取得する
  $messages = get_session('__messages');
  // 取得した$messagesが空だった場合、空の配列を返す
  if($messages === ''){
    return array();
  }
  // 取得した$messagesが空でない場合
    // SESSION変数の__messagesキーの値に空の配列を設定する
  set_session('__messages',  array());
    // 取得した$messagesを返す
  return $messages;
}

// ログイン済みかどうかを判定する
function is_logined(){
  // SESSION変数にuser_idが設定されている場合はTRUEを返し、設定されていなければFALSEを返す
  return get_session('user_id') !== '';
}

// 画像データをチェックし、不正の場合は空の文字列を返す。不正でない場合は画像名を生成して返す
function get_upload_filename($file){
  // 画像ファイルをチェックする
  if(is_valid_upload_image($file) === false){
    // チェック結果がfalseの場合、空の文字列を返す
    return '';
  }
  // チェック結果がtrueの場合、ファイルの先頭バイトを読み込み定数を$mimetypeに代入
  $mimetype = exif_imagetype($file['tmp_name']);
  // PERMITTED_IMAGE_TYPESに保存されている$mimetypeの拡張子を取得し、$extに代入
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  
  // 画像名にランダムの文字列を取得し、拡張子を付けて返す
  return get_random_string() . '.' . $ext;
}

// ランダムの文字列を生成して返す
// 文字列の長さのデフォルトは20
function get_random_string($length = 20){
  // ハッシュ関数でランダムの文字列を作成し、指定された文字数で文字列を切り取って返す
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}

// 画像ファイルを適切なディレクトリへ移動する
function save_image($image, $filename){
  // アップロードされた画像ファイルを$filenameの画像名でIMAGE_DIRに移動する
  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
}

function delete_image($filename){
  // 指定のディレクトリに指定のファイル名のファイルが存在する場合
  if(file_exists(IMAGE_DIR . $filename) === true){
    // 対象のファイルを削除する
    unlink(IMAGE_DIR . $filename);
    // trueを返す
    return true;
  }
  // 指定のディレクトリに指定のファイルが存在しなかった場合は、なにもせずfalseを返す
  return false;
}

// 文字数の最小最大を指定し、任意の文字列がその範囲に収まっているかをチェックして結果を返す
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  // $stringの文字列長さを取得する
  $length = mb_strlen($string);
  // 文字数が指定範囲内に収まっていれば、trueを返す
  return ($minimum_length <= $length) && ($length <= $maximum_length);
}

// フォーマット（アルファベット・数字の組み合わせ）を指定し、$stringが正規かチェックして結果を返す
function is_alphanumeric($string){
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

// フォーマット（整数）を指定し、$stringが正規かどうかをチェックして結果を返す
function is_positive_integer($string){
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

// 文字列が指定した形式にマッチするかチェックして結果を返す
function is_valid_format($string, $format){
  // $stringが指定した形式にマッチするか正規表現を使ってチェックし、問題なければtrueを返す
  return preg_match($format, $string) === 1;
}

// 入力した画像ファイルの内容をチェックし、不正な場合はエラーメッセージを設定しfalseを返す。適正な場合はtrueを返す
function is_valid_upload_image($image){
  // $imageがHTTP POSTでアップロードされたファイルでない場合
  if(is_uploaded_file($image['tmp_name']) === false){
    // SESSIONにエラーメッセージを設定する
    set_error('ファイル形式が不正です。');
    // falseを返す
    return false;
  }
  // 画像の先頭バイトを読み、$mimetypeに結果を代入する
  $mimetype = exif_imagetype($image['tmp_name']);
  // 画像のデータ形式がPERMITTED_IMAGE_TYPESの要素に含まれない（不正な）場合
  if( isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){
    // ファイル形式に関するエラーメッセージを、許容されるファイル形式を連結してセットする
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    // falseを返す
    return false;
  }
  // 画像データ形式が適正な場合、trueを返す
  return true;
}

// 引数の文字列にhtmlエスケープを施した値を返す
function h($str){
  return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

// トークンを生成し、セッションにセットして生成したトークンを返す
function get_csrf_token(){
  // ランダムの値を取得
  $token = get_random_string(30);
  // sessionにトークンをセット
  set_session('csrf_token', $token);
  return $token;
}

// トークンを受け取り、セッションにセットされているトークンと一致すればTRUEを返す
function is_valid_csrf_token($token){
  // $tokenが空っぽであればfalseを返す
  if($token === ''){
    return false;
  }
  // // 一致する場合はセッションに新たなトークンを設定
  // if($token === get_session('csrf_token')){
  //   get_csrf_token();
  // }
  
  // sessionにセットされているトークンを取得し、一致するかどうかをbool値で返す
  return $token === get_session('csrf_token');
}