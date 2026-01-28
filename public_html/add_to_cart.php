<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$dish_id = (int)($_POST['dish_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$ingredient_ids = $_POST['ingredients'] ?? []; // массив id

// 1) Получить базовую цену блюда
$stmt = $pdo->prepare("SELECT id, title, base_price FROM dishes WHERE id = ?");
$stmt->execute([$dish_id]);
$dish = $stmt->fetch();

if (!$dish) {
    exit('Такое блюдо не найдено.');
}

// 2) Получить цены ингредиентов (если есть)
$ingredients = [];
$total_ing_price = 0.0;
if (!empty($ingredient_ids)) {
    // Сформируем плейсхолдеры
    $placeholders = implode(',', array_fill(0, count($ingredient_ids), '?'));
    $sql = "SELECT id, name, price FROM ingredients WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ingredient_ids);
    $rows = $stmt->fetchAll();
    foreach ($rows as $r) {
        $ingredients[] = [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'price' => (float)$r['price']
        ];
        $total_ing_price += (float)$r['price'];
    }
}

// 3) Рассчитать unit_price
$unit_price = round((float)$dish['base_price'] + $total_ing_price, 2);
$total_price = round($unit_price * $quantity, 2);

// 4) Сохранить в сессии (корзина)
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$_SESSION['cart'][] = [
    'dish_id' => (int)$dish['id'],
    'title' => $dish['title'],
    'quantity' => $quantity,
    'unit_price' => $unit_price,
    'total_price' => $total_price,
    'ingredients' => $ingredients
];

// 5) Редирект обратно (или вернуть JSON)
header('Location: cart.php');
exit;
