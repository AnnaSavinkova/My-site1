<?php
session_start();
require_once __DIR__ . '/../config/db.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* Получаем все блюда с ресторанами */
$stmt = $pdo->query("
    SELECT dishes.id, dishes.title, dishes.base_price, restaurants.name AS restaurant_name
    FROM dishes
    LEFT JOIN restaurants ON dishes.restaurant_id = restaurants.id
    ORDER BY dishes.id DESC
");
$dishes = $stmt->fetchAll();

/* Количество товаров в корзине (из сессии) */
$cart_count = isset($_SESSION['cart'])
    ? array_sum(array_column($_SESSION['cart'], 'quantity'))
    : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Доставка еды</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">🍕 Доставка еды</a>

        <div class="d-flex align-items-center">
            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="cart.php" class="btn btn-outline-primary position-relative me-3">
                    🛒 Корзина
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a href="add_item.php" class="btn btn-success btn-sm me-2">+ Добавить блюдо</a>
                <?php endif; ?>

                <span class="me-3">
                    Привет, <?= htmlspecialchars($_SESSION['user_email'] ?? 'Пользователь') ?>!
                </span>

                <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>

            <?php else: ?>

                <a href="login.php" class="btn btn-primary btn-sm me-2">Войти</a>
                <a href="register.php" class="btn btn-outline-primary btn-sm">Регистрация</a>

            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">🍽️ Меню</h2>

    <div class="row">
        <?php foreach ($dishes as $dish): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2">
                            <?= htmlspecialchars($dish['restaurant_name'] ?? 'Не указан') ?>
                        </span>

                        <h5 class="card-title">
                            <?= htmlspecialchars($dish['title']) ?>
                        </h5>

                        <p class="card-text fw-bold text-primary">
                            <?= number_format($dish['base_price'], 2) ?> ₽
                        </p>
                    </div>

                    <div class="card-footer bg-white border-top-0">
                        <a href="add_to_cart.php?id=<?= $dish['id'] ?>"
                           class="btn btn-primary w-100">
                            🛒 В корзину
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($dishes) === 0): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <h5>Блюд пока нет</h5>
                    <p>Добавьте первое блюдо через админ-панель</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
