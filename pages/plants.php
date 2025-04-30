<?php
require_once '../config/init.php';
require_once '../includes/header.php';
?>

<div class="container">
    <h1>Our Plants</h1>
    
    <div class="plant-grid">
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM plants");
            while ($plant = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
        <div class="plant-card">
            <img src="../assets/images/<?= basename($plant['image_url']) ?>" alt="<?= htmlspecialchars($plant['name']) ?>">
            <h3><?= htmlspecialchars($plant['name']) ?></h3>
            <p class="price">$<?= number_format($plant['price'], 2) ?></p>
            <p><?= htmlspecialchars($plant['short_description']) ?></p>
            
            <form action="../actions/add_to_cart.php" method="post">
                <input type="hidden" name="plant_id" value="<?= $plant['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" class="quantity-input">
                <button type="submit" class="add-to-cart">Add to Cart</button>
            </form>
        </div>
        <?php
            endwhile;
        } catch (PDOException $e) {
            echo "<p>Error loading plants: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
</div>

<style>
.plant-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 30px;
}
.plant-card {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 5px;
}
.plant-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
}
.price {
    font-weight: bold;
    color: #2e7d32;
    font-size: 1.2em;
}
.quantity-input {
    width: 60px;
    padding: 5px;
    margin-right: 10px;
}
.add-to-cart {
    background: #2e7d32;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<?php require_once '../includes/footer.php'; ?>