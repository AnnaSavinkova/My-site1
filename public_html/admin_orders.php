<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once 'check_admin.php';

// Получаем заказы + позиции
$sql = "
    SELECT 
        o.id AS order_id,
        o.created_at,
        o.status,
        u.email,
        d.title AS dish_title,
        oi.quantity,
        oi.unit_price,
        oi.total_price,
        oi.ingredients_json
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON oi.order_id = o.id
    JOIN dishes d ON oi.dish_id = d.id
    ORDER BY o.id DESC
";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();

/**
 * Группируем:
 * [order_id] => [ info + items[] ]
 */
$orders = [];
foreach ($rows as $row) {
    $id = $row['order_id'];

    if (!isset($orders[$id])) {
        $orders[$id] = [
            'created_at' => $row['created_at'],
            'status' => $row['status'],
            'email' => $row['email'],
            'items' => []
        ];
    }

    $orders[$id]['items'][] = $row;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ — заказы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
    <h1 class="mb-4">📦 Все заказы</h1>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">Заказов пока нет</div>
    <?php endif; ?>

    <?php foreach ($orders as $order_id => $order): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Заказ #<?= $order_id ?></strong><br>
                    Клиент: <?= h($order['email']) ?><br>
                    Дата: <?= h($order['created_at']) ?>
                </div>

                <!-- Смена статуса -->
                <form method="GET" action="update_order_status.php" class="d-flex gap-2">
                    <input type="hidden" name="id" value="<?= $order_id ?>">
                    <select name="status" class="form-select">
                        <option value="new" <?= $order['status'] === 'new' ? 'selected' : '' ?>>Новый</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                        <option value="done" <?= $order['status'] === 'done' ? 'selected' : '' ?>>Выполнен</option>
                    </select>
                    <button class="btn btn-primary">OK</button>
                </form>
            </div>

            <div class="card-body">
                <p>
                    <strong>Статус:</strong>
                    <span class="badge bg-secondary"><?= h($order['status']) ?></span>
                </p>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Блюдо</th>
                            <th>Ингредиенты</th>
                            <th>Кол-во</th>
                            <th>Цена/шт</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td><?= h($item['dish_title']) ?></td>
                            <td>
                                <?php
                                $ings = $item['ingredients_json']
                                    ? json_decode($item['ingredients_json'], true)
                                    : [];
                                ?>
                                <?php if ($ings): ?>
                                    <ul class="mb-0">
                                        <?php foreach ($ings as $ing): ?>
                                            <li><?= h($ing['name']) ?> (<?= $ing['price'] ?> ₽)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$item['quantity'] ?></td>
                            <td><?= number_format($item['unit_price'], 2, '.', '') ?> ₽</td>
                            <td><?= number_format($item['total_price'], 2, '.', '') ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
