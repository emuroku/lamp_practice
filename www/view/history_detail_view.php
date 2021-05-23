<!DOCTYPE html>
<html lang="ja">

<head>
    <?php include VIEW_PATH . 'templates/head.php'; ?>
    <title>購入明細</title>
    <link rel="stylesheet" href="<?php print(STYLESHEET_PATH . 'history_detail.css'); ?>">
</head>

<body>
<!-- <?php var_dump($order_summary); ?> -->
    <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
    <div class="container">
        <h1>購入明細</h1>
        <?php include VIEW_PATH . 'templates/messages.php'; ?>
        <p>注文番号: <?php print(number_format($order_summary['order_id'])); ?></p>
        <p>購入日時: <?php print(h($order_summary['purchased'])); ?></p>
        <p>合計金額: <?php print(number_format($order_summary['sum'])); ?>円</p>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>商品名</th>
                    <th>商品価格</th>
                    <th>購入数</th>
                    <th>小計</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_details as $value) { ?>
                    <tr>
                        <td><?php print(h($value['name'])); ?></td>
                        <td><?php print(number_format($value['price'])); ?>円</td>
                        <td><?php print(number_format($value['amount'])); ?></td>
                        <td><?php print(number_format($value['price']*$value['amount'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>