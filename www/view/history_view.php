<!DOCTYPE html>
<html lang="ja">

<head>
    <?php include VIEW_PATH . 'templates/head.php'; ?>
    <title>購入履歴</title>
    <link rel="stylesheet" href="<?php print(STYLESHEET_PATH . 'history.css'); ?>">
</head>

<body>
    <?php var_dump($orders); ?>
    <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
    <h1>購入履歴</h1>
    <div class="container">

        <?php include VIEW_PATH . 'templates/messages.php'; ?>

        <?php if (count($orders) > 0) { ?>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>注文番号</th>
                        <th>合計金額</th>
                        <th>購入日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order) { ?>
                        <tr>
                            <td><?php print $order['order_id']; ?></td>
                            <td><?php print(number_format($order['sum'])); ?>円</td>
                            <td><?php print(h($order['purchased'])); ?></td>
                            <td>
                                <form method="post" action="history_get_detail.php">
                                    <input type="submit" value="購入明細表示" class="btn btn-danger delete">
                                    <input type="hidden" name="order_id" value="<?php print($order['order_id']); ?>">
                                    <!-- CSRF対策：トークンの送信 -->
                                    <input type="hidden" name="token" value="<?php print $token; ?>">
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>購入履歴がありません。</p>
        <?php } ?>
    </div>
</body>

</html>