
<?php require_once 'includes/header.php'; ?>

<main class="main">
    <div class="container">
        <h1 class="mb-8">All Products</h1>
        
        <div class="grid grid-cols-4">
            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Filters</h2>
                    <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">All Categories</option>
                                <option value="Vegetables" <?php echo isset($_GET['category']) && $_GET['category'] === 'Vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                                <option value="Fruits" <?php echo isset($_GET['category']) && $_GET['category'] === 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
                                <option value="Dairy" <?php echo isset($_GET['category']) && $_GET['category'] === 'Dairy' ? 'selected' : ''; ?>>Dairy</option>
                                <option value="Nuts" <?php echo isset($_GET['category']) && $_GET['category'] === 'Nuts' ? 'selected' : ''; ?>>Nuts & Seeds</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="min_price">Min Price</label>
                            <input type="number" id="min_price" name="min_price" class="form-control" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="max_price">Max Price</label>
                            <input type="number" id="max_price" name="max_price" class="form-control" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
                    </form>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div style="grid-column: span 3;">
                <div class="grid grid-cols-3">
                    <?php
                    // Build the query based on filters
                    $query = "SELECT p.*, u.name as seller_name FROM products p 
                             JOIN users u ON p.seller_id = u.id 
                             WHERE 1=1";
                    $params = [];
                    
                    if (isset($_GET['category']) && !empty($_GET['category'])) {
                        $query .= " AND p.category = ?";
                        $params[] = $_GET['category'];
                    }
                    
                    if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                        $query .= " AND p.price >= ?";
                        $params[] = $_GET['min_price'];
                    }
                    
                    if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                        $query .= " AND p.price <= ?";
                        $params[] = $_GET['max_price'];
                    }
                    
                    $query .= " ORDER BY p.title ASC";
                    
                    // Execute query
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($params);
                    $products = $stmt->fetchAll();
                    
                    // If no products in database, show sample products
                    if (empty($products)) {
                        $sampleProducts = [
                            [
                                'id' => 1,
                                'title' => 'Fresh Tomatoes',
                                'price' => 20.00,
                                'image' => 'https://images.unsplash.com/photo-1618160702438-9b02ab6515c9',
                                'seller_name' => 'Sample Farm',
                                'category' => 'Vegetables'
                            ],
                            [
                                'id' => 2,
                                'title' => 'Organic Carrots',
                                'price' => 15.00,
                                'image' => 'https://images.unsplash.com/photo-1472396961693-142e6e269027',
                                'seller_name' => 'Organic Gardens',
                                'category' => 'Vegetables'
                            ],
                            [
                                'id' => 3,
                                'title' => 'Fresh Apples',
                                'price' => 25.00,
                                'image' => 'https://images.unsplash.com/photo-1570913149827-d2ac84ab3f9a',
                                'seller_name' => 'Apple Orchard',
                                'category' => 'Fruits'
                            ],
                        ];
                        
                        // Apply filters to sample products if needed
                        if (isset($_GET['category']) && !empty($_GET['category'])) {
                            $sampleProducts = array_filter($sampleProducts, function($product) {
                                return $product['category'] === $_GET['category'];
                            });
                        }
                        
                        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                            $sampleProducts = array_filter($sampleProducts, function($product) {
                                return $product['price'] >= $_GET['min_price'];
                            });
                        }
                        
                        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                            $sampleProducts = array_filter($sampleProducts, function($product) {
                                return $product['price'] <= $_GET['max_price'];
                            });
                        }
                        
                        $products = $sampleProducts;
                    }
                    
                    if (empty($products)) {
                        echo '<div style="grid-column: span 3;"><p>No products found matching your criteria.</p></div>';
                    } else {
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
                        <?php endforeach;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
