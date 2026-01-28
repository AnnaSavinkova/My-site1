<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Важно: пользователь должен быть залогинен
if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

$user_id = (int)$_SESSION['user_id'];

// Если оформляем из корзины
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    // Альтернативно: поддерживаем make_order.php?id=xx если пользователь кликнул buy сразу
    $dish_id = (int)($_GET['id'] ?? 0);
    if ($dish_id <= 0) {
        exit("Корзина пуста и не указан товар.");
    }
    // Сделаем простую вставку как раньше
    $check = $pdo->prepare("SELECT id FROM dishes WHERE id = ?");
    $check->execute([$dish_id]);
    if (!$check->fetch()) exit("Ошибка: такое блюдо не найдено.");
    // Создаём заказ и одну позицию
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        $order_id = $pdo->lastInsertId();

        // взять цену блюда
        $stmt2 = $pdo->prepare("SELECT base_price FROM dishes WHERE id = ?");
        $stmt2->execute([$dish_id]);
        $dish = $stmt2->fetch();
        $unit_price = (float)$dish['base_price'];
        $stmt3 = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, quantity, unit_price, ingredients_json, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt3->execute([$order_id, $dish_id, 1, $unit_price, null, $unit_price]);
        $pdo->commit();
        echo "Заказ оформлен.";
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        exit("Ошибка: " . $e->getMessage());
    }
}

// Если есть корзина — вставляем всё в транзакции
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
    $order_id = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, quantity, unit_price, ingredients_json, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cart as $it) {
        // Доп. проверки: существует ли блюдо
        $check = $pdo->prepare("SELECT id FROM dishes WHERE id = ?");
        $check->execute([$it['dish_id']]);
        if (!$check->fetch()) throw new Exception("Блюдо с ID {$it['dish_id']} не найдено.");

        $ingredients_json = !empty($it['ingredients']) ? json_encode($it['ingredients'], JSON_UNESCAPED_UNICODE) : null;
        $stmtItem->execute([
            $order_id,
            $it['dish_id'],
            (int)$it['quantity'],
            (float)$it['unit_price'],
            $ingredients_json,
            (float)$it['total_price']
        ]);
    }

    $pdo->commit();

    // Очистим корзину
    unset($_SESSION['cart']);

    echo "✅ Заказ успешно оформлен! <a href='my_orders.php'>Мои заказы</a> | <a href='index.php'>На главную</a>";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Ошибка оформления заказа: " . h($e->getMessage());
}
