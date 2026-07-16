<?php
require 'db.php';

if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM foods WHERE id=?")->execute([$_GET['delete_id']]);
    header("Location:index.php");
    exit;
}

$foods = $pdo->query("SELECT * FROM foods ORDER BY id DESC")->fetchAll();
?>

<!doctype html>
<html lang="th">

<head>

    <meta charset="utf-8">

    <title>Food Recipe</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container mt-4">

        <div class="d-flex justify-content-between mb-3">

            <h2>🍽 รายการอาหาร</h2>

            <a href="manage.php" class="btn btn-primary">
                + เพิ่มเมนูอาหาร
            </a>

        </div>

        <table class="table table-bordered table-hover align-middle">

            <thead class="table-dark">

                <tr>

                    <th width="120">รูป</th>

                    <th>ชื่ออาหาร</th>

                    <th>หมวดหมู่</th>

                    <th>สูตรอาหาร</th>

                    <th width="150">จัดการ</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($foods as $food): ?>

                    <?php

                    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE food_id=?");

                    $stmt->execute([$food['id']]);

                    ?>

                    <tr>

                        <td class="text-center">

                            <?php if (!empty($food['image'])) { ?>

                                <img src="uploads/<?php echo htmlspecialchars($food['image']); ?>" width="100" height="80"
                                    style="object-fit:cover;border-radius:10px;">

                            <?php } else { ?>

                                <span class="text-danger">ไม่มีรูป</span>

                            <?php } ?>

                        </td>

                        <td>

                            <strong>

                                <?php echo htmlspecialchars($food['name_th']); ?>

                            </strong>

                        </td>

                        <td>

                            <?php echo htmlspecialchars($food['category']); ?>

                        </td>

                        <td>

                            <ul class="mb-0">

                                <?php while ($r = $stmt->fetch()) { ?>

                                    <li>

                                        <?php

                                        echo htmlspecialchars($r['recipe_name']);

                                        ?>

                                        -

                                        <?php

                                        echo $r['quantity'];

                                        ?>

                                        <?php

                                        echo htmlspecialchars($r['unit_name']);

                                        ?>

                                    </li>

                                <?php } ?>

                            </ul>

                        </td>

                        <td>

                            <a href="manage.php?id=<?php echo $food['id']; ?>" class="btn btn-warning btn-sm">

                                แก้ไข

                            </a>

                            <a href="?delete_id=<?php echo $food['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('ต้องการลบหรือไม่');">

                                ลบ

                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</body>

</html>