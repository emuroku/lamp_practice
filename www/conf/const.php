<?php

// 関数ファイルのディレクトリを指定
define('MODEL_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../model/');
// Viewファイルのディレクトリを指定
define('VIEW_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../view/');

// 商品画像ファイルのパスを指定
define('IMAGE_PATH', '/assets/images/');
// CSSファイルのディレクトリを指定
define('STYLESHEET_PATH', '/assets/css/');
// 商品画像ファイルのディレクトリを指定
define('IMAGE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' );

// DBアクセスの際のユーザー情報を指定
define('DB_HOST', 'mysql');
define('DB_NAME', 'sample');
define('DB_USER', 'testuser');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8');

// 新規登録処理のリンクを指定
define('SIGNUP_URL', '/signup.php');
// ログイン処理のリンクを指定
define('LOGIN_URL', '/login.php');
// ログアウト処理のリンクを指定
define('LOGOUT_URL', '/logout.php');
// ホームページのリンクを指定
define('HOME_URL', '/index.php');
// カートページのリンクを指定
define('CART_URL', '/cart.php');
// 購入完了ページのリンクを指定
define('FINISH_URL', '/finish.php');
// Adminページのリンクを指定
define('ADMIN_URL', '/admin.php');
// 購入履歴ページのリンクを指定
define('HISTORY_URL', '/history.php');
// 購入明細ページのリンクを指定
define('HISTORY_DETAIL_URL', '/history_detail.php');

// 半角英数の組み合わせの正規表現を指定
define('REGEXP_ALPHANUMERIC', '/\A[0-9a-zA-Z]+\z/');
// 利用可能な整数の正規表現を指定
define('REGEXP_POSITIVE_INTEGER', '/\A([1-9][0-9]*|0)\z/');

// ユーザー名の最低文字数を指定
define('USER_NAME_LENGTH_MIN', 6);
// ユーザー名の最長文字数を指定
define('USER_NAME_LENGTH_MAX', 100);
// パスワードの最小文字数を指定
define('USER_PASSWORD_LENGTH_MIN', 6);
// パスワードの最長文字数を指定
define('USER_PASSWORD_LENGTH_MAX', 100);

// adminユーザーのTypeの値を設定
define('USER_TYPE_ADMIN', 1);
// 通常ユーザーのTypeの値を設定
define('USER_TYPE_NORMAL', 2);

// 商品名の最低文字数を指定
define('ITEM_NAME_LENGTH_MIN', 1);
// 商品名の最長文字数を指定
define('ITEM_NAME_LENGTH_MAX', 100);

// 公開商品のstatusカラムの値を指定
define('ITEM_STATUS_OPEN', 1);
// 非公開商品のstatusカラムの値を指定
define('ITEM_STATUS_CLOSE', 0);

// 商品の公開ステータスの配列を設定
define('PERMITTED_ITEM_STATUSES', array(
  // openキーには1, closeキーには0 を設定
  'open' => 1,
  'close' => 0,
));

// 登録可能な画像フォーマットの配列を設定
define('PERMITTED_IMAGE_TYPES', array(
  // jpgとpngを指定
  IMAGETYPE_JPEG => 'jpg',
  IMAGETYPE_PNG => 'png',
));