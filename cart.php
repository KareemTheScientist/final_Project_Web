<?php
session_start();
include 'navbar.php';
include 'db.php';

// Handle quantity updates
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $id => $quantity) {
        foreach ($_SESSION['cart']['items'] as &$item) {
            if ($item['id'] == $id) {
                $item['quantity'] = max(1, (int)$quantity);
                break;
            }
        }
    }
    
    // Recalculate totals
    $_SESSION['cart']['total'] = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);
    
    $_SESSION['cart']['count'] = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
        return $sum + $item['quantity'];
    }, 0);
    
    $_SESSION['success'] = "Cart updated!";
    header("Location: cart.php");
    exit();
}

// Handle item removal
if (isset($_GET['remove'])) {
    $_SESSION['cart']['items'] = array_filter($_SESSION['cart']['items'], function($item) {
        return $item['id'] != $_GET['remove'];
    });
    
    // Recalculate totals
    $_SESSION['cart']['total'] = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
        return $sum + ($item['price'] * $item['quantity']);
    }, 0);
    
    $_SESSION['cart']['count'] = array_reduce($_SESSION['cart']['items'], function($sum, $item) {
        return $sum + $item['quantity'];
    }, 0);
    
    $_SESSION['success'] = "Item removed from cart";
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Nabta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reuse your existing styles from plants.php */
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--dark);
            line-height: 1.6;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Cart Page Specific Styles */
        .cart-page {
            padding: 40px 0;
        }
        
        .cart-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary);
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-cart i {
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        .empty-cart p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .cart-table th {
            text-align: left;
            padding: 15px;
            background: var(--light);
            border-bottom: 2px solid var(--primary);
        }
        
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .product-info img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .remove-btn {
            color: #f44336;
            text-decoration: none;
            font-size: 1.2rem;
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: var(--primary-light);
        }
        
        .continue-btn {
            background: var(--gray);
        }
        
        .update-btn {
            background: #FF9800;
        }
        
        .checkout-btn {
            background: var(--primary);
        }
        
        @media (max-width: 768px) {
            .cart-table thead {
                display: none;
            }
            
            .cart-table tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #eee;
                border-radius: 8px;
            }
            
            .cart-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #eee;
            }
            
            .cart-table td::before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 20px;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="cart-page">
        <div class="container">
            <h1 class="cart-title">Your Shopping Cart</h1>
            
            <?php if (empty($_SESSION['cart']['items'])): ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart fa-3x"></i>
                    <p>Your cart is empty</p>
                    <a href="plants.php" class="btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <form method="post" action="cart.php">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart']['items'] as $item): ?>
                            <tr>
                                <td data-label="Product" class="product-info">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                                    <span><?php echo $item['name']; ?></span>
                                </td>
                                <td data-label="Price" class="price">EGP <?php echo number_format($item['price'], 2); ?></td>
                                <td data-label="Quantity">
                                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                                </td>
                                <td data-label="Total" class="total">EGP <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td data-label="Remove">
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
                                <td colspan="2">EGP <?php echo number_format($_SESSION['cart']['total'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="cart-actions">
                        <a href="plants.php" class="btn continue-btn">Continue Shopping</a>
                        <button type="submit" name="update_cart" class="btn update-btn">Update Cart</button>
                        <a href="checkout.php" class="btn checkout-btn">Proceed to Checkout</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>