<?php
// 1. Подключаем БД и проверку на админа
require_once __DIR__ . '/../config/db.php';
require 'check_admin.php'; // Эту страницу видит только админ!

$message = '';

// Получаем список ресторанов для выпадающего списка
$restaurants = [];
try {
    $restaurant_stmt = $pdo->query("SELECT id, name FROM restaurants ORDER BY name");
    $restaurants = $restaurant_stmt->fetchAll();
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger">Ошибка загрузки ресторанов: ' . $e->getMessage() . '</div>';
}

// 2. Если нажата кнопка "Сохранить"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant_id = $_POST['restaurant_id'];
    $title = trim($_POST['title']);
    $base_price = $_POST['base_price'];
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $image_url = trim($_POST['image_url']);
    $is_builder = isset($_POST['is_builder']) ? 1 : 0;

    // Валидация
    if (empty($restaurant_id) || empty($title) || empty($base_price)) {
        $message = '<div class="alert alert-danger">Заполните обязательные поля: ресторан, название и цену!</div>';
    } elseif (!is_numeric($base_price) || $base_price <= 0) {
        $message = '<div class="alert alert-danger">Цена должна быть положительным числом!</div>';
    } else {
        // 3. Сохраняем в Базу Данных
        $sql = "INSERT INTO dishes (restaurant_id, title, description, base_price, category, image_url, is_builder) 
                VALUES (:rid, :t, :d, :p, :cat, :img, :builder)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':rid' => $restaurant_id,
                ':t' => $title,
                ':d' => $description,
                ':p' => $base_price,
                ':cat' => $category,
                ':img' => $image_url,
                ':builder' => $is_builder
            ]);
            $message = '<div class="alert alert-success">Блюдо успешно добавлено!</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка БД: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить блюдо</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        .form-check-label {
            font-weight: 500;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h1>Добавление нового блюда</h1>
        <a href="index.php" class="btn btn-secondary mb-3">← На главную</a>
        
        <?= $message ?>

        <form method="POST" class="card p-4 shadow-sm">
            <!-- Выбор ресторана -->
            <div class="mb-3">
                <label class="required">Ресторан:</label>
                <select name="restaurant_id" class="form-select" required>
                    <option value="">-- Выберите ресторан --</option>
                    <?php foreach ($restaurants as $restaurant): ?>
                        <option value="<?= htmlspecialchars($restaurant['id']) ?>" 
                            <?= isset($_POST['restaurant_id']) && $_POST['restaurant_id'] == $restaurant['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($restaurant['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($restaurants)): ?>
                    <small class="text-danger">Нет ресторанов в базе. Сначала добавьте ресторан через админку.</small>
                <?php endif; ?>
            </div>
            
            <!-- Название блюда -->
            <div class="mb-3">
                <label class="required">Название блюда:</label>
                <input type="text" name="title" class="form-control" 
                       value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                       required placeholder="Например: Пицца Маргарита">
            </div>
            
            <!-- Категория -->
            <div class="mb-3">
                <label class="required">Категория:</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Выберите категорию --</option>
                    <option value="pizza" <?= isset($_POST['category']) && $_POST['category'] == 'pizza' ? 'selected' : '' ?>>Пицца</option>
                    <option value="wok" <?= isset($_POST['category']) && $_POST['category'] == 'wok' ? 'selected' : '' ?>>Вок</option>
                    <option value="sushi" <?= isset($_POST['category']) && $_POST['category'] == 'sushi' ? 'selected' : '' ?>>Суши/Роллы</option>
                    <option value="burger" <?= isset($_POST['category']) && $_POST['category'] == 'burger' ? 'selected' : '' ?>>Бургеры</option>
                    <option value="salad" <?= isset($_POST['category']) && $_POST['category'] == 'salad' ? 'selected' : '' ?>>Салаты</option>
                    <option value="soup" <?= isset($_POST['category']) && $_POST['category'] == 'soup' ? 'selected' : '' ?>>Супы</option>
                    <option value="drink" <?= isset($_POST['category']) && $_POST['category'] == 'drink' ? 'selected' : '' ?>>Напитки</option>
                    <option value="dessert" <?= isset($_POST['category']) && $_POST['category'] == 'dessert' ? 'selected' : '' ?>>Десерты</option>
                    <option value="other" <?= isset($_POST['category']) && $_POST['category'] == 'other' ? 'selected' : '' ?>>Другое</option>
                </select>
            </div>
            
            <!-- Базовая цена -->
            <div class="mb-3">
                <label class="required">Базовая цена (руб):</label>
                <div class="input-group">
                    <input type="number" name="base_price" class="form-control" 
                           value="<?= isset($_POST['base_price']) ? htmlspecialchars($_POST['base_price']) : '' ?>" 
                           step="0.01" min="0" required placeholder="350.00">
                    <span class="input-group-text">₽</span>
                </div>
                <small class="text-muted">Цена без дополнительных ингредиентов</small>
            </div>

            <!-- Картинка -->
            <div class="mb-3">
                <label>Ссылка на картинку (URL):</label>
                <input type="url" name="image_url" class="form-control" 
                       value="<?= isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : '' ?>" 
                       placeholder="https://example.com/image.jpg">
                <small class="text-muted">Оставьте пустым, чтобы использовать изображение по умолчанию</small>
            </div>

            <!-- Конструктор -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_builder" id="is_builder" value="1"
                           <?= isset($_POST['is_builder']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_builder">
                        <strong>Доступен конструктор</strong>
                    </label>
                    <small class="d-block text-muted">Отметьте, если для этого блюда можно выбирать ингредиенты (пицца, вок)</small>
                </div>
            </div>

            <!-- Описание -->
            <div class="mb-3">
                <label>Описание:</label>
                <textarea name="description" class="form-control" rows="3" 
                          placeholder="Состав, особенности блюда..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <!-- Кнопки -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success flex-grow-1">Сохранить блюдо</button>
                <a href="add_restaurant.php" class="btn btn-outline-primary">Добавить ресторан</a>
            </div>
        </form>
    </div>

    <script>
        // Автоподстановка категории при выборе конструктора
        document.getElementById('is_builder').addEventListener('change', function() {
            if (this.checked) {
                const categorySelect = document.querySelector('select[name="category"]');
                if (categorySelect.value === '' || categorySelect.value === 'other') {
                    categorySelect.value = 'pizza';
                }
            }
        });
    </script>
</body>
</html>