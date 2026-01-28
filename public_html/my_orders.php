<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

$sql = "
    SELECT o.id as order_id, o.created_at, o.status,
           oi.id as item_id, oi.dish_id, oi.quantity, oi.unit_price, oi.total_price, oi.ingredients_json,
           d.title as dish_title, r.name as restaurant_name
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN dishes d ON d.id = oi.dish_id
    LEFT JOIN restaurants r ON r.id = d.restaurant_id
    WHERE o.user_id = ?
    ORDER BY o.id DESC, oi.id ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$uid]);
$rows = $stmt->fetchAll();

// преобразуем в дерево orders -> items
$orders = [];
foreach ($rows as $r) {
    $oid = $r['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'created_at' => $r['created_at'],
            'status' => $r['status'],
            'items' => []
        ];
    }
    $orders[$oid]['items'][] = $r;
}

?>
<!doctype html><html><head><meta charset="utf-8"><title>Мои заказы</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="p-4"><div class="container">
<h1>Мои заказы</h1>
<?php if (empty($orders)): ?>
    <div class="alert alert-info">Заказов нет.</div>
<?php else: foreach ($orders as $oid => $ord): ?>
    <div class="card mb-3">
        <div class="card-header">
            Заказ #<?= $oid ?> — <?= h($ord['created_at']) ?> — <strong><?= h($ord['status']) ?></strong>
        </div>
        <div class="card-body">
            <ul>
            <?php foreach ($ord['items'] as $it): 
                $ings = $it['ingredients_json'] ? json_decode($it['ingredients_json'], true) : [];
            ?>
                <li>
                    <?= h($it['dish_title']) ?> — <?= (int)$it['quantity'] ?> × <?= number_format($it['unit_price'],2,'.','') ?> ₽ = <?= number_format($it['total_price'],2,'.','') ?> ₽
                    <?php if (!empty($ings)): ?>
                        <br><small>Ингредиенты: <?= h(implode(', ', array_map(function($i){return $i['name'].'('.$i['price'].'₽)';}, $ings))) ?></small>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endforeach; endif; ?>
</div></body></html>
