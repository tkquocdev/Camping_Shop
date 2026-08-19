<?php
namespace App\Controllers\Client;

use App\Core\Controller;

class HomeController extends Controller {
    
    public function index() {
        // 1. Khởi tạo các Model
        $productModel = $this->model('Product');
        $fsModel = $this->model('FlashSaleModel'); // Gọi thêm Model FlashSale

        // 2. Lấy dữ liệu sản phẩm thông thường (Code cũ của bạn)
        // Lấy 4 sản phẩm mới nhất (New Arrivals)
        $newProducts = $productModel->getNewestProducts(4);

        // Lấy 8 sản phẩm ngẫu nhiên (Featured/Gợi ý)
        $featuredProducts = $productModel->getRandomProducts(8);

        // 3. Xử lý logic Flash Sale
        // Lấy đợt sale đang diễn ra (nếu có)
        $activeFlashSale = $fsModel->getActiveFlashSale();
        $flashSaleItems = [];

        // Nếu tìm thấy đợt sale hợp lệ, lấy tiếp danh sách sản phẩm trong đó
        if ($activeFlashSale) {
            $flashSaleItems = $fsModel->getFlashSaleItems($activeFlashSale['id']);
        }

        // 4. Đóng gói dữ liệu gửi ra View
        $data = [
            'page_title'        => 'Camping Shop - Trang Chủ',
            'banner_title'      => 'Khám Phá Thiên Nhiên',
            'new_products'      => $newProducts,       
            'featured_products' => $featuredProducts,
            // Truyền dữ liệu Flash Sale ra view
            'flash_sale'        => $activeFlashSale,      // Dùng để hiện đồng hồ đếm ngược
            'flash_sale_items'  => $flashSaleItems        // Dùng để hiện list sản phẩm sale
        ];

        // 5. Gọi View
        $this->view('home', $data);
    }
}