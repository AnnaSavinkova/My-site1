<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) $total += $item['total_price'];
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Корзина</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4">
<div class="container">
    <h1>Корзина</h1>
    <?php if (empty($cart)): ?>
        <div class="alert alert-info">Ваша корзина пуста.</div>
        <a href="index.php" class="btn btn-primary">На витрину</a>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Блюдо</th><th>Ингредиенты</th><th>Кол-во</th><th>Цена/шт</th><th>Итог</th></tr></thead>
            <tbody>
            <?php foreach ($cart as $i=>$it): ?>
                <tr>
                    <td><?= h($it['title']) ?></td>
                    <td>
                        <?php if (!empty($it['ingredients'])): ?>
                            <ul class="mb-0">
                            <?php foreach ($it['ingredients'] as $ing): ?>
                                <li><?= h($ing['name']) ?> (<?= $ing['price'] ?> ₽)</li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= number_format($it['unit_price'],2,'.','') ?> ₽</td>
                    <td><?= number_format($it['total_price'],2,'.','') ?> ₽</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <h4>Итого: <?= number_format($total,2,'.','') ?> ₽</h4>

        <form method="POST" action="make_order.php">
            <button type="submit" class="btn btn-success">Оформить заказ</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
