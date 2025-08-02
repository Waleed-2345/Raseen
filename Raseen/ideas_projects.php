<?php
session_start();
if (!isset($_SESSION['entrepreneur_id'])) {
    header("Location: signin.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$entrepreneur_id = $_SESSION['entrepreneur_id'];

// حذف فكرة
if (isset($_POST['delete_idea_id'])) {
    $id = (int)$_POST['delete_idea_id'];
    $conn->query("DELETE FROM ideas WHERE id = $id AND entrepreneur_id = $entrepreneur_id");
}

// حذف مشروع
if (isset($_POST['delete_project_id'])) {
    $id = (int)$_POST['delete_project_id'];
    $conn->query("DELETE FROM projects WHERE id = $id AND entrepreneur_id = $entrepreneur_id");
}

// جلب الأفكار والمشاريع
$ideas = $conn->query("SELECT * FROM ideas WHERE entrepreneur_id = $entrepreneur_id");
$projects = $conn->query("SELECT * FROM projects WHERE entrepreneur_id = $entrepreneur_id");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>أفكاري ومشاريعي</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f8;
      padding: 20px;
    }

    .container {
      display: flex;
      gap: 30px;
    }

    .section {
      flex: 1;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .item {
      background: #f9f9f9;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 10px;
    }

    h2 {
      color: #007b5e;
    }

    .btns {
      margin-top: 10px;
    }

    .btns form {
      display: inline-block;
    }

    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .edit {
      background: #4caf50;
      color: white;
    }

    .delete {
      background: #e53935;
      color: white;
    }
  </style>
</head>
<body>

<h1>📂 أفكاري ومشاريعي</h1>

<div class="container">
  <div class="section">
    <h2>🧠 أفكاري</h2>
    <?php while ($idea = $ideas->fetch_assoc()): ?>
      <div class="item">
        <strong><?= htmlspecialchars($idea['idea_name']) ?></strong>
        <div class="btns">
          <a class="btn edit" href="edit_idea.php?id=<?= $idea['id'] ?>">✏️ تعديل</a>
          <form method="post" onsubmit="return confirm('هل أنت متأكد من حذف الفكرة؟');">
            <input type="hidden" name="delete_idea_id" value="<?= $idea['id'] ?>">
            <button class="btn delete" type="submit">🗑️ حذف</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <div class="section">
    <h2>🏗️ مشاريعي</h2>
    <?php while ($project = $projects->fetch_assoc()): ?>
      <div class="item">
        <strong><?= htmlspecialchars($project['project_name']) ?></strong>
        <div class="btns">
          <a class="btn edit" href="edit_project.php?id=<?= $project['id'] ?>">✏️ تعديل</a>
          <form method="post" onsubmit="return confirm('هل أنت متأكد من حذف المشروع؟');">
            <input type="hidden" name="delete_project_id" value="<?= $project['id'] ?>">
            <button class="btn delete" type="submit">🗑️ حذف</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

</body>
</html>
