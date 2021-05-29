<!DOCTYPE html>
<html lang="ja">

<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>

  <title>商品一覧</title>
  <link rel="stylesheet" href="<?php print(STYLESHEET_PATH . 'index.css'); ?>">
</head>

<body>
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
  <div class="container">
    <h1>商品一覧</h1>
    <?php include VIEW_PATH . 'templates/messages.php'; ?>
    <!-- 表示中商品の番号を表示 -->
    <!-- 最後のページの場合 -->
    <?php if($current_page == $pages){ ?>
    <p><?php print $assortment["COUNT(*)"]; ?>件中 <?php print ($current_page-1)*DISPLAYED_ITEMS+1; ?> - <?php print $assortment["COUNT(*)"]; ?>件目の商品</p> 
    <?php }else{ ?>
    <!-- 最後のページでない場合 -->
    <p><?php print $assortment["COUNT(*)"]; ?>件中 <?php print ($current_page-1)*DISPLAYED_ITEMS+1; ?> - <?php print ($current_page)*DISPLAYED_ITEMS; ?>件目の商品</p>
    <?php } ?>
    <div class="card-deck">
      <div class="row">
        <?php foreach ($items as $item) { ?>
          <div class="col-6 item">
            <div class="card h-100 text-center">
              <div class="card-header">
                <?php print(h($item['name'])); ?>
              </div>
              <figure class="card-body">
                <img class="card-img" src="<?php print(IMAGE_PATH . $item['image']); ?>">
                <figcaption>
                  <?php print(number_format($item['price'])); ?>円
                  <?php if ($item['stock'] > 0) { ?>
                    <form action="index_add_cart.php" method="post">
                      <input type="submit" value="カートに追加" class="btn btn-primary btn-block">
                      <input type="hidden" name="item_id" value="<?php print($item['item_id']); ?>">
                      <!-- CSRF対策：トークンの送信 -->
                      <input type="hidden" name="token" value="<?php print $token; ?>">
                    </form>
                  <?php } else { ?>
                    <p class="text-danger">現在売り切れです。</p>
                  <?php } ?>
                </figcaption>
              </figure>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>

    <!-- ページャー -->
    <div class="pager">
      <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
          <!-- <li class="page-item">
            <a class="page-link" href="<?php print (HOME_URL) . '?page=' . $current_page - 1; ?>">Previous</a>
          </li> -->
          <?php for ($i = 1; $i <= $pages; $i++) {
            if ($i == $current_page) { ?>
              <!-- 現在のページ番号の場合 -->
              <li class="page-item active">
                <a class="page-link" href="<?php print (HOME_URL) . '?page=' . $i; ?>"><?php print $i; ?><span class="sr-only">(current)</span></a>
              </li>
            <?php } else { ?>
              <!-- 現在のページ番号ではない場合 -->
              <li class="page-item">
                <a class="page-link" href="<?php print (HOME_URL) . '?page=' . $i; ?>"><?php print $i; ?></a>
              </li>
          <?php }
          } ?>
          <!-- <li class="page-item">
            <a class="page-link" href="#">Next</a>
          </li> -->
        </ul>
      </nav>
    </div>
  </div>

</body>

</html>