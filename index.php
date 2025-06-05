
<?php require_once 'header.php'; ?>

<section class="hero">
    <div class="container">
        <div style="max-width: 640px;">
            <h1>Fresh from Farm to Your Table</h1>
            <p>
                Your one-stop shop for fresh produce from local farmers. Quality vegetables and fruits,
                delivered with care.
            </p>
            <div class="hero-actions">
                <a href="products.php" class="btn btn-primary">Browse Products</a>
                <a href="about.php" class="btn btn-ghost">Learn more</a>
            </div>
        </div>
    </div>
</section>

<section class="main" id="featured">
    <div class="container">
        <h2 class="mb-8">Featured Products</h2>
        
        <div class="grid grid-cols-3">
            <?php
            // Fetch featured products
            $stmt = $pdo->query("SELECT p.*, u.name as seller_name FROM products p 
                                JOIN users u ON p.seller_id = u.id 
                                ORDER BY purchase_count DESC LIMIT 3");
            $products = $stmt->fetchAll();
            
            // If no products in database, show sample products
            if (empty($products)) {
                $sampleProducts = [
                    [
                        'id' => 1,
                        'title' => 'Fresh Tomatoes',
                        'price' => 20.00,
                        'image' => 'https://images.unsplash.com/photo-1618160702438-9b02ab6515c9',
                        'seller_name' => 'Sample Farm'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Organic Carrots',
                        'price' => 15.00,
                        'image' => 'https://images.unsplash.com/photo-1472396961693-142e6e269027',
                        'seller_name' => 'Organic Gardens'
                    ],
                    [
                        'id' => 3,
                        'title' => 'Fresh Apples',
                        'price' => 25.00,
                        'image' => 'https://images.unsplash.com/photo-1570913149827-d2ac84ab3f9a',
                        'seller_name' => 'Apple Orchard'
                    ],
                ];
                
                $products = $sampleProducts;
            }
            
            foreach ($products as $product):
            ?>
            <div class="card">
                <div class="card-header">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                    <p class="card-price">R<?php echo number_format($product['price'], 2); ?> per kg</p>
                    <p class="text-gray-600">Seller: <?php echo htmlspecialchars($product['seller_name']); ?></p>
                </div>
                <div class="card-footer">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline" style="width: 100%;">
                        View Details
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
