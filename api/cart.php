<?php
// api/cart.php  –  REST-style cart API
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$user = currentUser();
if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Please login to manage cart.'], 401);
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? ($_GET['action'] ?? '');
$db     = getDB();

function cartCount(PDO $db, int $userId): int {
    $st = $db->prepare('SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?');
    $st->execute([$userId]);
    return (int)$st->fetchColumn();
}

switch ($action) {

    // ── ADD ──────────────────────────────────────────────────
    case 'add':
        $productId = (int)($body['product_id'] ?? 0);
        $qty       = max(1, (int)($body['quantity'] ?? 1));

        // Validate product
        $st = $db->prepare('SELECT id, stock_quantity FROM products WHERE id=?');
        $st->execute([$productId]);
        $product = $st->fetch();
        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Product not found.'], 404);
        }
        if ($product['stock_quantity'] < $qty) {
            jsonResponse(['success' => false, 'message' => 'Insufficient stock.']);
        }

        // Upsert cart row
        $st = $db->prepare('
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON CONFLICT (user_id, product_id)
            DO UPDATE SET quantity = cart.quantity + EXCLUDED.quantity,
                          created_at = NOW()
        ');
        $st->execute([$user['id'], $productId, $qty]);
        jsonResponse(['success' => true, 'cart_count' => cartCount($db, $user['id'])]);

    // ── UPDATE ───────────────────────────────────────────────
    case 'update':
        $cartId = (int)($body['cart_id'] ?? 0);
        $qty    = max(1, (int)($body['quantity'] ?? 1));
        $st = $db->prepare('UPDATE cart SET quantity=? WHERE id=? AND user_id=?');
        $st->execute([$qty, $cartId, $user['id']]);
        jsonResponse(['success' => true, 'cart_count' => cartCount($db, $user['id'])]);

    // ── REMOVE ───────────────────────────────────────────────
    case 'remove':
        $cartId = (int)($body['cart_id'] ?? 0);
        $st = $db->prepare('DELETE FROM cart WHERE id=? AND user_id=?');
        $st->execute([$cartId, $user['id']]);
        jsonResponse(['success' => true, 'cart_count' => cartCount($db, $user['id'])]);

    // ── GET ──────────────────────────────────────────────────
    case 'get':
        $st = $db->prepare('
            SELECT c.id, c.quantity, p.product_name, p.price, p.image, p.stock_quantity
            FROM cart c JOIN products p ON p.id = c.product_id
            WHERE c.user_id = ?
        ');
        $st->execute([$user['id']]);
        $items = $st->fetchAll();
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
        jsonResponse(['success' => true, 'items' => $items, 'total' => $total]);

    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
}
