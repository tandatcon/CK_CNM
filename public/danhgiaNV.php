<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh Giá Nhân Viên - Đi cùng tôi</title>
    <link rel="stylesheet" href="Assets/styles.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="Assets/scripts.js?v=1"></script>
    <style>
        .order-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .star-rating i {
            cursor: pointer;
            transition: all 0.2s;
        }
        .star-rating i:hover {
            transform: scale(1.2);
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .status-pending { background: #facc15; color: black; }
        .status-confirmed { background: #3b82f6; color: white; }
        .status-in-progress { background: #f97316; color: white; }
        .status-completed { background: #4ade80; color: white; }
        .status-cancelled { background: #ef4444; color: white; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <?php include_once(__DIR__ . '/Assets/header.php'); ?>

    <div class="container mx-auto my-6 px-4 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <!-- Header với tên nhân viên -->
            <div class="bg-blue-500 text-white p-4 flex items-center">
                <div class="w-12 h-12 rounded-full bg-white bg-opacity-20 flex items-center justify-center mr-3">
                    <i class="fas fa-user text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold" id="employeeNameHeader">Đang tải thông tin nhân viên...</h1>
                    <p class="text-sm opacity-90">Đánh giá nhân viên hỗ trợ</p>
                </div>
            </div>
            
            <!-- Form đánh giá -->
            <div class="p-6">
                <form id="ratingForm">
                    <input type="hidden" id="id_nhanvien" name="id_nhanvien">
                    <input type="hidden" id="id_khachhang" name="id_khachhang">

                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-2">Mức độ hài lòng</label>
                        <div class="star-rating flex justify-center space-x-2">
                            <i class="far fa-star text-3xl text-yellow-400" data-rating="1"></i>
                            <i class="far fa-star text-3xl text-yellow-400" data-rating="2"></i>
                            <i class="far fa-star text-3xl text-yellow-400" data-rating="3"></i>
                            <i class="far fa-star text-3xl text-yellow-400" data-rating="4"></i>
                            <i class="far fa-star text-3xl text-yellow-400" data-rating="5"></i>
                        </div>
                        <input type="hidden" id="sao" name="sao" value="0">
                        <p id="ratingText" class="text-center text-sm text-gray-500 mt-1">Chưa đánh giá</p>
                    </div>

                    <div class="mb-6">
                        <label for="danhgia" class="block text-sm font-medium mb-2">Nhận xét của bạn</label>
                        <textarea id="danhgia" name="danhgia" rows="4" 
                            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500"
                            placeholder="Hãy chia sẻ trải nghiệm của bạn về nhân viên..."></textarea>
                    </div>

                    <div class="flex justify-between">
                        <a href="javascript:history.back()" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">
                            <i class="fas fa-arrow-left mr-2"></i>Quay lại
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            <i class="fas fa-check mr-2"></i>Gửi đánh giá
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Thông tin đơn hàng -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gray-100 p-4 border-b">
                <h2 class="text-lg font-semibold">Thông tin đơn hàng liên quan</h2>
            </div>
            <div id="orderDetails" class="p-4">
                <p class="text-sm text-gray-500">Đang tải chi tiết đơn hàng...</p>
            </div>
        </div>
    </div>

    <?php include_once(__DIR__ . '/Assets/footer.php'); ?>

    <script>
    $(document).ready(function () {
        const urlParams = new URLSearchParams(window.location.search);
        const nvID = urlParams.get('idNV');

        if (!nvID || isNaN(nvID)) {
            showError('Mã nhân viên không hợp lệ');
            return;
        }

        // Lấy thông tin nhân viên
        function fetchEmployeeInfo() {
            fetch(`http://localhost/WEB_ThueHoTroKhamBenh/api/xemDGNVAPI.php?idNV=${nvID}`, {
                method: 'GET',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const emp = data.data;
                    $('#employeeNameHeader').text(emp.hoten || 'Nhân viên');
                    $('#id_nhanvien').val(nvID);
                    
                    // Cập nhật thông tin khách hàng (từ JWT hoặc session)
                    $('#id_khachhang').val(1); // Thay bằng ID thực tế
                } else {
                    showError(data.message || 'Không tìm thấy thông tin nhân viên');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Lỗi khi tải thông tin nhân viên');
            });
        }

        // Lấy thông tin đơn hàng
        function fetchOrderDetails() {
            fetch(`http://localhost/WEB_ThueHoTroKhamBenh/api/xemDGNVAPI.php?idNV=${nvID}`, {
                method: 'GET',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.success && data.data) {
                    const item = data.data;
                    html = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm"><i class="fas fa-venus-mars mr-2 text-blue-500"></i><strong>Giới tính:</strong> ${item.gtnv == 0 ? 'Nam' : item.gtnv == 1 ? 'Nữ' : 'N/A'}</p>
                                <p class="text-sm"><i class="fas fa-birthday-cake mr-2 text-blue-500"></i><strong>Năm sinh:</strong> ${item.namsinhnv || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm"><i class="fas fa-thumbs-up mr-2 text-blue-500"></i><strong>Đánh giá trung bình:</strong> 
                                    ${item.sao ? `${item.sao}/5 <i class="fas fa-star text-yellow-400"></i>` : 'Chưa có đánh giá'}
                                </p>
                            </div>
                        </div>
                    `;
                } else {
                    html = `
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            ${data.message || 'Không có thông tin đơn hàng'}
                        </div>
                    `;
                }
                $('#orderDetails').html(html);
            })
            .catch(error => {
                console.error('Error:', error);
                $('#orderDetails').html('<p class="text-sm text-red-500">Lỗi khi tải thông tin đơn hàng</p>');
            });
        }

        // Xử lý đánh giá sao
        $(document).on('click', '.star-rating i', function() {
            const rating = $(this).data('rating');
            $('#sao').val(rating);
            
            $('.star-rating i').each(function() {
                const starValue = $(this).data('rating');
                $(this).toggleClass('fas far', starValue <= rating);
            });
            
            const ratings = ['Rất tệ', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Rất hài lòng'];
            $('#ratingText').text(ratings[rating - 1]);
        });

        // Gửi đánh giá
        $('#ratingForm').submit(function(e) {
            e.preventDefault();
            
            if ($('#sao').val() == 0) {
                alert('Vui lòng chọn số sao đánh giá');
                return;
            }

            const formData = {
                id_nhanvien: $('#id_nhanvien').val(),
                id_khachhang: $('#id_khachhang').val(),
                sao: $('#sao').val(),
                danhgia: $('#danhgia').val()
            };

            $('button[type="submit"]').html('<i class="fas fa-spinner fa-spin mr-2"></i> Đang gửi...').prop('disabled', true);

            fetch('http://localhost/WEB_ThueHoTroKhamBenh/api/submitDanhGia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cảm ơn bạn đã đánh giá!');
                    window.location.href = 'xemDH.php';
                } else {
                    alert(data.message || 'Gửi đánh giá không thành công');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối server');
            })
            .finally(() => {
                $('button[type="submit"]').html('<i class="fas fa-check mr-2"></i>Gửi đánh giá').prop('disabled', false);
            });
        });

        // Khởi chạy
        fetchEmployeeInfo();
        fetchOrderDetails();
    });

    function showError(message) {
        $('#ratingContainer').html(`
            <div class="text-center p-6 text-red-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${message}
            </div>
        `);
    }
    </script>
</body>
</html>