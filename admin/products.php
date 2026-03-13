<?php
// admin/products.php – Add / Edit / Delete products (Binary Image Storage)

error_reporting(E_ALL);
ini_set('display_errors',1);

require_once __DIR__ . '/../config/database.php';
requireAdmin();
$db = getDB();

$error   = '';
$success = '';
$editing = null;

// ── DELETE ─────────────────────────────────────────────
if (isset($_GET['delete'])) {
  $pid = (int)$_GET['delete'];
  $db->prepare('DELETE FROM products WHERE id=?')->execute([$pid]);
  $success = 'Product deleted.';
}

// ── EDIT FETCH ─────────────────────────────────────────
if (isset($_GET['edit'])) {
  $st = $db->prepare('SELECT * FROM products WHERE id=?');
  $st->execute([(int)$_GET['edit']]);
  $editing = $st->fetch();
}

// ── SAVE (add / update) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id          = (int)($_POST['id'] ?? 0);
  $name        = trim($_POST['product_name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price       = (float)($_POST['price'] ?? 0);
  $stock       = (int)($_POST['stock_quantity'] ?? 0);
  $catId       = (int)($_POST['category_id'] ?? 0);
  $featured    = isset($_POST['is_featured']) ? 'TRUE' : 'FALSE';

  $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . time();

  $imageData = null;

  if (!empty($_FILES['image']['tmp_name'])) {

    echo "<pre>UPLOAD DEBUG\n";
    print_r($_FILES['image']);
    echo "</pre>";

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];

    if (!in_array($ext,$allowed)) {
      $error = 'Invalid image format.';
    } else {
      $imageData = file_get_contents($_FILES['image']['tmp_name']);
      echo "<pre>IMAGE SIZE: ".strlen($imageData)." bytes</pre>";
    }

  } else {

    if ($id) {
      $st = $db->prepare("SELECT image FROM products WHERE id=?");
      $st->execute([$id]);
      $imageData = $st->fetchColumn();
    }

  }

  if (!$error) {

    if ($id) {

      $stmt = $db->prepare("
        UPDATE products 
        SET product_name=?,slug=?,description=?,price=?,stock_quantity=?,category_id=?,image=?,is_featured=? 
        WHERE id=?
      ");

      $stmt->bindParam(1,$name);
      $stmt->bindParam(2,$slug);
      $stmt->bindParam(3,$description);
      $stmt->bindParam(4,$price);
      $stmt->bindParam(5,$stock);
      $stmt->bindParam(6,$catId);
      $stmt->bindParam(7,$imageData,PDO::PARAM_LOB);
      $stmt->bindParam(8,$featured);
      $stmt->bindParam(9,$id);

      $stmt->execute();

      $success = 'Product updated.';

    } else {

      $stmt = $db->prepare("
        INSERT INTO products 
        (product_name,slug,description,price,stock_quantity,category_id,image,is_featured) 
        VALUES (?,?,?,?,?,?,?,?)
      ");

      $stmt->bindParam(1,$name);
      $stmt->bindParam(2,$slug);
      $stmt->bindParam(3,$description);
      $stmt->bindParam(4,$price);
      $stmt->bindParam(5,$stock);
      $stmt->bindParam(6,$catId);
      $stmt->bindParam(7,$imageData,PDO::PARAM_LOB);
      $stmt->bindParam(8,$featured);

      $stmt->execute();

      $success = 'Product added.';
    }

    $editing = null;
  }
}

$products = $db->query("
  SELECT p.*, c.category_name 
  FROM products p 
  LEFT JOIN categories c ON c.id=p.category_id 
  ORDER BY p.created_at DESC
")->fetchAll();

$categories = $db->query("
  SELECT * FROM categories 
  ORDER BY category_name
")->fetchAll();


/* =========================
   GLOBAL DEBUG
========================= */

echo "<pre style='background:#111;color:#0f0;padding:10px'>";
echo "TOTAL PRODUCTS: ".count($products)."\n\n";

if(!empty($products)){
    $first = $products[0];

    echo "FIRST PRODUCT STRUCTURE:\n";
    print_r($first);

    echo "\nIMAGE DEBUG:\n";

    if(isset($first['image'])){
        echo "TYPE: ".gettype($first['image'])."\n";

        if(is_string($first['image'])){
            echo "LENGTH: ".strlen($first['image'])."\n";
            echo "FIRST 20 CHARS: ".substr($first['image'],0,20)."\n";
        }
    }
}

echo "</pre>";

?>

<tbody>

<?php foreach ($products as $p): ?>

<?php
echo "<pre style='background:#222;color:#fff'>";
echo "ROW DEBUG\n";
echo "Product ID: ".$p['id']."\n";
echo "Image Type: ".gettype($p['image'])."\n";

if(is_string($p['image'])){
    echo "Image Length: ".strlen($p['image'])."\n";
    echo "First Bytes: ".substr($p['image'],0,20)."\n";
}

echo "</pre>";
?>

<tr>

<td>

<?php if (!empty($p['image'])): ?>

<?php
$img = $p['image'];

if (is_string($img) && substr($img,0,2) === '\\x') {
    $img = hex2bin(substr($img,2));
}
?>

<img src="data:image/jpeg;base64,<?= base64_encode($img) ?>"
style="width:50px;height:50px;object-fit:cover;border-radius:8px">

<?php else: ?>

<span style="font-size:2rem">🐟</span>

<?php endif; ?>

</td>

<td class="fw-semibold"><?= htmlspecialchars($p['product_name']) ?></td>

<td>Rs <?= number_format($p['price'],0,',','.') ?></td>

<td>
<span class="badge <?= $p['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger' ?>">
<?= $p['stock_quantity'] ?>
</span>
</td>

<td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>

<td><?= $p['is_featured'] ? '⭐' : '—' ?></td>

<td>

<a href="products.php?edit=<?= $p['id'] ?>"
class="btn btn-sm btn-outline-primary me-1">Edit</a>

<a href="products.php?delete=<?= $p['id'] ?>"
class="btn btn-sm btn-outline-danger confirm-delete">Delete</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>
