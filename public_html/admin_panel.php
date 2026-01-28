<?php
// Самая первая строчка — вызов охраны!
require 'check_admin.php'; 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <div class="container">
        <div class="alert alert-success">
            <h1>Панель Администратора</h1>
            <p>Добро пожаловать, Повелитель!</p>
            <p>Здесь вы будете управлять: <?php echo "Ваша тема курсовой"; ?></p>
        </div>
        
        <a href="logout.php" class="btn btn-danger">Выйти</a>
    </div>
</body>
</html>