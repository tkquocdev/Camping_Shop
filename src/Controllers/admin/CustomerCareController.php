<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class CustomerCareController extends Controller {
   // 1. TRANG CHỦ (DANH SÁCH TICKET & KHÁCH HÀNG)
    // URL: /admin/customercare
    public function index() {
        $model = $this->model('CustomerCare');
        
        $tickets = $model->getAllTickets(); 
        $customers = $model->getAllCustomers();

        $this->view('admin/crm/index', [
            'active'    => 'crm',
            'title'     => 'Trung tâm CSKH',
            'tickets'   => $tickets,
            'customers' => $customers
        ]);
    }

    // 2. HIỂN THỊ CHI TIẾT TICKET (Quy trình xử lý khiếu nại/yêu cầu)
    // URL: /admin/customercare/ticket_detail/4
    public function ticket_detail($id = null) {
        if (!$id) {
            header('Location: /admin/customercare');
            exit;
        }

        $model = $this->model('CustomerCare');

        // 1. Lấy thông tin Ticket
        $ticket = $model->getTicketById($id);

        if (!$ticket) {
            // Nếu không tìm thấy ticket, quay về trang chủ
            header('Location: /admin/customercare'); 
            exit;
        }

        // 2. Lấy thông tin Khách hàng (người tạo ticket)
        $customer = $model->getCustomerById($ticket['customer_id']);

        // 3. Lấy toàn bộ lịch sử chăm sóc của khách hàng này
        $logs = $model->getLogs($ticket['customer_id']);

        // 4. Render View
        $this->view('admin/crm/detail', [
            'active'         => 'crm',
            'current_ticket' => $ticket, // Quan trọng: Biến này để View hiển thị box xử lý Ticket
            'customer'       => $customer,
            'logs'           => $logs,
            'page_title'     => 'Ticket #' . ($ticket['ticket_id'] ?? $ticket['id'])
        ]);
    }

    // 3. HIỂN THỊ HỒ SƠ KHÁCH HÀNG (Chăm sóc chung, không có ticket cụ thể)
    // URL: /admin/customercare/customer/10
    public function customer($id) {
        $model = $this->model('CustomerCare');
        $customer = $model->getCustomerById($id);
        
        if (!$customer) {
            header('Location: /admin/customercare');
            exit;
        }

        $logs = $model->getLogs($id);

        $this->view('admin/crm/detail', [
            'active'         => 'crm',
            'title'          => 'Hồ sơ: ' . $customer['full_name'],
            'customer'       => $customer,
            'logs'           => $logs,
            'current_ticket' => null // Null để View ẩn box xử lý Ticket đi
        ]);
    }

    // 4. AJAX: CẬP NHẬT TRẠNG THÁI TICKET
    // Xử lý: Update bảng logs chính + Ghi thêm 1 dòng log lịch sử
    public function updateTicket() {
        // Quan trọng: Set Header JSON để Javascript nhận diện đúng
        header('Content-Type: application/json');

        // 1. Kiểm tra Method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
            exit;
        }

        // 2. Lấy dữ liệu
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;
        $note = $_POST['note'] ?? '';
        
        // Giả sử lấy ID nhân viên từ Session (sửa key 'user'/'id' theo code login của bạn)
        // Nếu không có session thì gán mặc định hoặc trả lỗi
        $staffId = $_SESSION['user']['id'] ?? $_SESSION['admin_id'] ?? null;

        if (!$id || !$status) {
            echo json_encode(['status' => 'error', 'message' => 'Thiếu ID hoặc Trạng thái']);
            exit;
        }

        if (!$staffId) {
            echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập hết hạn. Vui lòng login lại.']);
            exit;
        }

        $model = $this->model('CustomerCare');

        // 3. Thực hiện Update vào DB (Gọi hàm updateTicketStatus mới trong Model)
        // Hàm này sẽ update status và nối thêm note vào content
        $updateResult = $model->updateTicketStatus($id, $status, $staffId, $note);

        if ($updateResult) {
            // 4. (Tùy chọn) Ghi thêm 1 dòng Log riêng biệt để lưu lịch sử thay đổi trạng thái
            // Giúp hiển thị trong timeline: "Admin A đã đổi trạng thái thành Đã xử lý"
            
            // Lấy lại ticket để biết customer_id
            $ticketInfo = $model->getTicketById($id);
            if ($ticketInfo) {
                $statusText = $this->getStatusText($status);
                $logContent = "Hệ thống: Cập nhật trạng thái thành [$statusText]";
                if($note) $logContent .= " - Ghi chú: $note";

                $model->addLog([
                    'customer_id' => $ticketInfo['customer_id'],
                    'staff_id'    => $staffId,
                    'ticket_id'   => $id,
                    'type'        => 'System',
                    'content'     => $logContent,
                    'status'      => $status
                ]);
            }

            echo json_encode(['status' => 'success', 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: Không thể cập nhật.']);
        }
        
        exit; // Kết thúc script ngay lập tức
    }

    // 5. STORE: THÊM TƯƠNG TÁC MỚI (GỌI ĐIỆN, TƯ VẤN...)
    // URL: /admin/customercare/store/{customer_id}
    public function store($customerId)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $type = $_POST['type'] ?? 'Tuvan';
            $content = $_POST['content'] ?? '';
            $status = $_POST['status'] ?? 'Completed';
            $ticketId = $_POST['ticket_id'] ?? null; // Nếu đang trong trang chi tiết ticket

            $staffId = $_SESSION['user']['id'] ?? $_SESSION['admin_id'] ?? 1; // Fallback ID 1 nếu lỗi session

            if (!empty($content)) {
                $model = $this->model('CustomerCare');
                
                $model->addLog([
                    'customer_id' => $customerId,
                    'staff_id'    => $staffId,
                    'ticket_id'   => $ticketId,
                    'type'        => $type,
                    'content'     => $content,
                    'status'      => $status
                ]);
            }

            // Redirect trở lại trang cũ
            if ($ticketId) {
                header("Location: /admin/customercare/ticket_detail/$ticketId?msg=added");
            } else {
                header("Location: /admin/customercare/customer/$customerId?msg=added");
            }
            exit;
        }
    }

    // 6. UPDATE INFO: CẬP NHẬT SĐT/ĐỊA CHỈ KHÁCH
    // URL: /admin/customercare/update_info/{customer_id}
    public function update_info($customerId)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $phone = $_POST['phone'] ?? '';
            $address = $_POST['address'] ?? '';

            $model = $this->model('CustomerCare');
            
            $model->updateCustomer($customerId, [
                'phone'   => $phone,
                'address' => $address
            ]);

            // Quay lại trang trước đó
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header("Location: /admin/customercare/customer/$customerId");
            }
            exit;
        }
    }

    // HELPER: Chuyển mã trạng thái sang tiếng Việt
    private function getStatusText($status) {
        return match($status) {
            'Pending'   => 'Đang chờ',
            'Processing'=> 'Đang xử lý',
            'Completed' => 'Hoàn thành',
            'Cancelled' => 'Đã hủy',
            default     => $status
        };
    }

    public function delete() {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /admin/customercare'); // Quay về nếu không có ID
            exit;
        }

        $model = $this->model('CustomerCare');
        $model->deleteTicket($id);

        // Quay lại trang danh sách sau khi xóa
        header('Location: /admin/customercare'); 
        exit;
    }
}

