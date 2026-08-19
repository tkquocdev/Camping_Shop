<?php
/**
 * Database Seeds - Tạo dữ liệu mẫu
 * 
 * Chạy: php db/seeds.php
 * Tác dụng: Xóa toàn bộ dữ liệu, reset ID từ 1, tạo 10 danh mục với 10 sản phẩm mỗi cái
 */

require_once __DIR__ . '/../vendor/autoload.php';

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

use App\Config\Database;

try {
    $db = (new Database())->getConnection();
    
    echo "========================================\n";
    echo "STARTING DATABASE SEED\n";
    echo "========================================\n\n";
    
    // ========================================================================
    // 1. DELETE ALL DATA (in order to respect foreign key constraints)
    // ========================================================================
    echo "Step 1: Deleting existing data...\n";
    
    $tables = [
        'cart_items',
        'notification_reads',
        'notifications',
        'customer_care_logs',
        'point_history',
        'loyalty_rewards',
        'lucky_history',
        'lucky_prizes',
        'news',
        'password_resets',
        'reviews',
        'order_items',
        'orders',
        'flash_sale_items',
        'flash_sales',
        'stock_issue_details',
        'stock_issues',
        'stock_import_items',
        'stock_imports',
        'user_addresses',
        'suppliers',
        'coupons',
        'products',
        'categories',
        'settings',
        'users'
    ];
    
    foreach ($tables as $table) {
        try {
            $db->exec("DELETE FROM $table");
        } catch (\Exception $e) {
            // Table might not exist, skip
        }
    }
    
    echo " ✓ All data deleted\n\n";
    
    // ========================================================================
    // 2. RESET SEQUENCES (IDs start from 1)
    // ========================================================================
    echo "Step 2: Resetting sequences...\n";
    
    $sequences = [
        'users_id_seq',
        'categories_id_seq',
        'products_id_seq',
        'coupons_id_seq',
        'orders_id_seq',
        'order_items_id_seq',
        'reviews_id_seq',
        'cart_items_id_seq',
        'flash_sales_id_seq',
        'flash_sale_items_id_seq',
        'suppliers_id_seq',
        'stock_imports_id_seq',
        'stock_import_items_id_seq',
        'stock_issues_id_seq',
        'stock_issue_details_id_seq',
        'news_id_seq',
        'notifications_id_seq',
        'notification_reads_id_seq',
        'lucky_prizes_id_seq',
        'lucky_history_id_seq',
        'point_history_id_seq',
        'loyalty_rewards_id_seq',
        'password_resets_id_seq',
        'user_addresses_id_seq',
        'customer_care_logs_id_seq',
        'settings_id_seq'
    ];
    
    foreach ($sequences as $seq) {
        try {
            $db->exec("ALTER SEQUENCE IF EXISTS $seq RESTART WITH 1");
        } catch (\Exception $e) {
            // Sequence might not exist, skip
        }
    }
    
    echo " ✓ All sequences reset (IDs will start from 1)\n\n";
    
    // ========================================================================
    // 3. CREATE ADMIN USER
    // ========================================================================
    echo "Step 3: Creating admin user...\n";
    
    $adminUsername = getenv('ADMIN_USERNAME') ?: 'admin';
    $adminFullName = getenv('ADMIN_FULL_NAME') ?: 'Administrator';
    $adminEmail = getenv('ADMIN_EMAIL');
    $adminPlainPassword = getenv('ADMIN_PASSWORD');
    if (!$adminEmail || !$adminPlainPassword) {
        throw new RuntimeException('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env before seeding.');
    }

    $adminPassword = password_hash($adminPlainPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare(
        "INSERT INTO users (username, full_name, email, password, phone, role, status, points) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $adminUsername,
        $adminFullName,
        $adminEmail,
        $adminPassword,
        '0123456789',
        'admin',
        'active',
        0
    ]);
    
    echo " ✓ Admin user created\n\n";
    
    // ========================================================================
    // 4. CREATE 10 CATEGORIES
    // ========================================================================
    echo "Step 4: Creating 10 categories...\n";
    
    $categories = [
        [
            'name' => 'Lều cắm trại',
            'description' => 'Các loại lều chuyên dụng cho cắm trại, đủ kích cỡ từ 1-10 người. Chống thấm nước, thoáng khí.'
        ],
        [
            'name' => 'Túi ngủ',
            'description' => 'Túi ngủ chất lượng cao cho các mùa khác nhau. Vải lót êm ái, giữ ấm hiệu quả.'
        ],
        [
            'name' => 'Bếp dã ngoại',
            'description' => 'Nồi, chảo, bếp gas mini cho nấu ăn trong rừng. Nhẹ, gọn và an toàn.'
        ],
        [
            'name' => 'Đèn pin & Đèn pin',
            'description' => 'Đèn pin LED, đèn năng lượng mặt trời. Pin trâu, sáng mạnh, tiết kiệm điện.'
        ],
        [
            'name' => 'Dụng cụ sinh tồn',
            'description' => 'Dao, rìu, tuốc nơ vít, kìm đa năng. Công cụ thiết yếu cho cắm trại.'
        ],
        [
            'name' => 'Ghế gấp & Bàn dã ngoại',
            'description' => 'Ghế gấp nhẹ, bàn tấm ghi đốp. Dễ di chuyển, bền bỉ.'
        ],
        [
            'name' => 'Balo trekking',
            'description' => 'Balo size lớn 50-80L cho trekking. Khung thép, thoáng khí, chất lượng cao.'
        ],
        [
            'name' => 'Giày boots & Dép',
            'description' => 'Giày leo núi, boots chống thấm. Bền, thoáng, thoải mái cho chuyến đi dài.'
        ],
        [
            'name' => 'Áo quần & Đồ bảo hộ',
            'description' => 'Áo chống nước, quần trekking. Bảo vệ khỏi côn trùng, hạn chế chấn thương.'
        ],
        [
            'name' => 'Phụ kiện khác',
            'description' => 'Vòi nước, bình nước, kính trời, bao chống thấm. Những phụ kiện tiện ích khác.'
        ]
    ];
    
    $categoryIds = [];
    foreach ($categories as $cat) {
        $stmt = $db->prepare(
            "INSERT INTO categories (name, description) VALUES (?, ?)"
        );
        $stmt->execute([$cat['name'], $cat['description']]);
        $categoryIds[] = $db->lastInsertId();
    }
    
    echo " ✓ 10 categories created\n\n";
    
    // ========================================================================
    // 5. CREATE 10 PRODUCTS FOR EACH CATEGORY
    // ========================================================================
    echo "Step 5: Creating 10 products per category (100 products total)...\n";
    
    $productTemplates = [
        ['name' => 'Sản phẩm cơ bản', 'suffix' => '- Dòng cơ bản', 'price' => 200000, 'stock' => 50],
        ['name' => 'Sản phẩm nâng cao', 'suffix' => '- Version X', 'price' => 500000, 'stock' => 30],
        ['name' => 'Sản phẩm cao cấp', 'suffix' => '- Premium', 'price' => 800000, 'stock' => 20],
        ['name' => 'Combo tiết kiệm', 'suffix' => '- Set 2 chiếc', 'price' => 1200000, 'stock' => 15],
        ['name' => 'Sản phẩm chuyên nghiệp', 'suffix' => '- Pro Series', 'price' => 1500000, 'stock' => 10],
        ['name' => 'Sản phẩm gia đình', 'suffix' => '- Family Pack', 'price' => 600000, 'stock' => 25],
        ['name' => 'Sản phẩm du lịch', 'suffix' => '- Travel Size', 'price' => 300000, 'stock' => 40],
        ['name' => 'Sản phẩm mùa đông', 'suffix' => '- Winter Edition', 'price' => 700000, 'stock' => 35],
        ['name' => 'Sản phẩm đa năng', 'suffix' => '- Multi-purpose', 'price' => 400000, 'stock' => 45],
        ['name' => 'Sản phẩm đặc biệt', 'suffix' => '- Limited Edition', 'price' => 1000000, 'stock' => 5]
    ];
    
    $productCount = 0;
    foreach ($categoryIds as $catIndex => $catId) {
        $catName = $categories[$catIndex]['name'];
        
        foreach ($productTemplates as $template) {
            $productName = $template['name'] . ' ' . $catName . ' ' . $template['suffix'];
            $description = sprintf(
                "Sản phẩm chất lượng cao cho danh mục %s. Thiết kế hiện đại, an toàn và bền bỉ. "
                . "Phù hợp cho cả người mới bắt đầu và người có kinh nghiệm. "
                . "Thương hiệu uy tín, đã phục vụ hàng ngàn khách hàng.",
                $catName
            );
            $price = $template['price'];
            $stock = $template['stock'];
            $image = "https://via.placeholder.com/300x300?text=" . urlencode($productName);
            
            $stmt = $db->prepare(
                "INSERT INTO products (category_id, name, description, price, stock, image) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$catId, $productName, $description, $price, $stock, $image]);
            
            $productCount++;
        }
    }
    
    echo " ✓ $productCount products created (10 per category)\n\n";
    
    // ========================================================================
    // 6. CREATE SAMPLE NEWS
    // ========================================================================
    echo "Step 6: Creating sample news...\n";
    
    $newsList = [
        [
            'title' => 'Khám phá top 5 địa điểm cắm trại tuyệt đẹp tại Việt Nam',
            'summary' => 'Những địa điểm cắm trại thiên nhiên ngoạn mục không nên bỏ lỡ',
            'content' => 'Cắm trại là một hoạt động tuyệt vời để tận hưởng thiên nhiên. Hãy khám phá những địa điểm đẹp nhất để cắm trại tại Việt Nam.'
        ],
        [
            'title' => 'Hướng dẫn chọn lều cắm trại phù hợp',
            'summary' => 'Những tiêu chí quan trọng khi chọn lều cho chuyến đi',
            'content' => 'Lều là nơi trú ẩn quan trọng nhất trong cắm trại. Hãy tìm hiểu cách chọn lều phù hợp với nhu cầu của bạn.'
        ]
    ];
    
    foreach ($newsList as $news) {
        $stmt = $db->prepare(
            "INSERT INTO news (title, summary, content, image, created_at, updated_at) 
             VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)"
        );
        $stmt->execute([
            $news['title'],
            $news['summary'],
            $news['content'],
            'https://via.placeholder.com/600x400?text=' . urlencode($news['title'])
        ]);
    }
    
    echo " ✓ Sample news created\n\n";
    
    // ========================================================================
    // 7. CREATE SAMPLE LUCKY PRIZES
    // ========================================================================

    
    // ========================================================================
    // 8. CREATE SAMPLE LOYALTY REWARDS
    // ========================================================================

    
    // ========================================================================
    // 9. CREATE SAMPLE SUPPLIERS
    // ========================================================================
    
    // ========================================================================
    // SUCCESS MESSAGE
    // ========================================================================

    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
