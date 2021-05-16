-- 購入情報テーブル
CREATE TABLE orders (
  order_id INT AUTO_INCREMENT,
  user_id INT,
  purchased DATETIME,
  primary key(order_id)
);

-- 購入詳細情報テーブル
CREATE TABLE order_details (
  order_id INT,
  item_id INT,
  name VARCHAR(100) COLLATE utf8_general_ci,
  price INT,
  amount INT DEFAULT 0
);

-- ※ユーザー情報はusersのuser_id, 商品情報はitemsのitem_idと結合して取得