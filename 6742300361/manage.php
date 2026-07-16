<?php
require 'db.php';

$id = $_GET['id'] ?? null;

$food = [
    'name_th' => '',
    'category' => '',
    'image' => ''
];
$recipes = [];

// 1. ดึงข้อมูลเก่าขึ้นมาก่อน (ถ้ามี $id) เพื่อให้มีข้อมูลรูปภาพเดิมอยู่ใน $food['image']
if ($id) {
    $s = $pdo->prepare("SELECT * FROM foods WHERE id=?");
    $s->execute([$id]);
    $fetchedFood = $s->fetch();
    if ($fetchedFood) {
        $food = $fetchedFood;
    }

    $s = $pdo->prepare("SELECT * FROM recipes WHERE food_id=?");
    $s->execute([$id]);
    $recipes = $s->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name_th'];
    $cat = $_POST['category'];

    // 2. ใช้รูปภาพเดิมจาก Database ถ้าไม่มีการอัปโหลดไฟล์ใหม่
    $image = $food['image'] ?: '';

    if (!empty($_FILES['image']['name'])) {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    }

    if ($id) {
        $pdo->prepare("
            UPDATE foods
            SET name_th=?, category=?, image=?
            WHERE id=?
        ")->execute([$name, $cat, $image, $id]);

        $pdo->prepare("
            DELETE FROM recipes
            WHERE food_id=?
        ")->execute([$id]);

        $fid = $id;
    } else {
        $pdo->prepare("
            INSERT INTO foods(name_th, category, image)
            VALUES(?, ?, ?)
        ")->execute([$name, $cat, $image]);

        $fid = $pdo->lastInsertId();
    }

    $st = $pdo->prepare("
        INSERT INTO recipes (food_id, recipe_name, quantity, unit_name)
        VALUES (?, ?, ?, ?)
    ");

    if (isset($_POST['recipes']) && is_array($_POST['recipes'])) {
        foreach ($_POST['recipes'] as $r) {
            // ป้องกันค่าเป็นช่องว่างหรือส่งมาไม่ครบ
            if (!empty($r['recipe_name'])) {
                $st->execute([
                    $fid,
                    $r['recipe_name'],
                    $r['quantity'] ?? 0,
                    $r['unit_name'] ?? ''
                ]);
            }
        }
    }
    header("Location: index.php");
    exit;
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>สูตรอาหาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <form method="post" enctype="multipart/form-data">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>สูตรอาหาร / วัตถุดิบ</h5>
                <button type="button" id="addRecipe" class="btn btn-success btn-sm">+ เพิ่มวัตถุดิบ</button>
            </div>

            <input class="form-control mb-2" name="name_th" placeholder="ชื่ออาหาร"
                value="<?= htmlspecialchars($food['name_th']) ?>" required>

            <select class="form-select mb-2" name="category">
                <option <?= $food['category'] == 'อาหารคาว' ? 'selected' : '' ?>>อาหารคาว</option>
                <option <?= $food['category'] == 'อาหารหวาน' ? 'selected' : '' ?>>อาหารหวาน</option>
                <option <?= $food['category'] == 'เครื่องดื่ม' ? 'selected' : '' ?>>เครื่องดื่ม</option>
            </select>

            <div class="mb-3">
                <label class="form-label">รูปอาหาร</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if (!empty($food['image'])) { ?>
                    <div class="mt-2"><img src="uploads/<?= htmlspecialchars($food['image']) ?>" width="150"></div>
                <?php } ?>
            </div>

            <div id="rows">
                <?php if (empty($recipes)) { ?>
                    <div class="row mb-2 recipe-row">
                        <div class="col"><input class="form-control" name="recipes[0][recipe_name]" placeholder="วัตถุดิบ">
                        </div>
                        <div class="col"><input class="form-control" name="recipes[0][quantity]" placeholder="จำนวน"></div>
                        <div class="col"><input class="form-control" name="recipes[0][unit_name]" placeholder="หน่วย"></div>
                        <div class="col-1"><button type="button" class="btn btn-danger remove">X</button></div>
                    </div>
                <?php } else {
                    foreach ($recipes as $i => $r) { ?>
                        <div class="row mb-2 recipe-row">
                            <div class="col"><input class="form-control" name="recipes[<?= $i ?>][recipe_name]"
                                    value="<?= htmlspecialchars($r['recipe_name']) ?>"></div>
                            <div class="col"><input class="form-control" name="recipes[<?= $i ?>][quantity]"
                                    value="<?= htmlspecialchars($r['quantity']) ?>"></div>
                            <div class="col"><input class="form-control" name="recipes[<?= $i ?>][unit_name]"
                                    value="<?= htmlspecialchars($r['unit_name']) ?>"></div>
                            <div class="col-1"><button type="button" class="btn btn-danger remove">X</button></div>
                        </div>
                    <?php }
                } ?>
            </div>

            <button class="btn btn-success">บันทึก</button>
            <a href="index.php" class="btn btn-secondary">กลับ</a>
        </form>

        <script>
            // ปรับตัวนับ Index ให้ปลอดภัยขึ้นโดยอิงจากจำนวนแถวที่มีอยู่จริงบนหน้าจอ
            let recipeIndex = document.querySelectorAll('.recipe-row').length;

            document.getElementById("addRecipe").addEventListener("click", function () {
                let container = document.getElementById("rows");
                let row = document.createElement("div");
                row.className = "row mb-2 recipe-row";
                row.innerHTML = `
                    <div class="col">
                        <input type="text" name="recipes[${recipeIndex}][recipe_name]" class="form-control" placeholder="วัตถุดิบ">
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="recipes[${recipeIndex}][quantity]" class="form-control" placeholder="จำนวน">
                    </div>
                    <div class="col">
                        <input type="text" name="recipes[${recipeIndex}][unit_name]" class="form-control" placeholder="หน่วย">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-danger remove">X</button>
                    </div>
                `;
                container.appendChild(row);
                recipeIndex++;
            });

            document.addEventListener("click", function (e) {
                if (e.target.classList.contains("remove")) {
                    e.target.closest(".recipe-row").remove();
                }
            });
        </script>
    </div>
</body>

</html>