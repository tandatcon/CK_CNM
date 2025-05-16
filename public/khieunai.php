<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Khiếu nại - Đi cùng tôi</title>
  <link rel="stylesheet" href="\WEB_ThueHoTroKhamBenh\public\Assets\styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .header {
      position: sticky;
      top: 0;
      z-index: 50;
      background-color: white;
    }

    .section {
      padding: 4rem 1rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .section-title {
      font-size: 2rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      color: #2a6edb;
    }

    .step {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 2rem;
      padding: 1.5rem;
      background-color: #f9f9f9;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s;
    }

    .step:hover {
      transform: translateY(-5px);
    }

    .step-number {
      width: 50px;
      height: 50px;
      background-color: #2a6edb;
      color: white;
      font-size: 1.5rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .step-content h3 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 0.5rem;
    }

    .step-content p {
      color: #666;
      font-size: 1rem;
      line-height: 1.5;
    }

    .contact-info {
      text-align: center;
      margin-top: 3rem;
      padding: 2rem;
      background-color: #e6f0ff;
      border-radius: 10px;
    }

    .contact-info p {
      font-size: 1.1rem;
      color: #333;
      margin-bottom: 1rem;
    }

    .contact-info a {
      color: #2a6edb;
      text-decoration: none;
      font-weight: 600;
    }

    .contact-info a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .section {
        padding: 2rem 1rem;
      }

      .section-title {
        font-size: 1.5rem;
      }

      .step {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
      }

      .step-number {
        margin-bottom: 1rem;
      }

      .step-content h3 {
        font-size: 1.1rem;
      }

      .step-content p {
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body class="bg-white text-gray-800">
<?php include_once(__DIR__ . '/Assets/header.php'); ?>
  <main>
    <section class="section">
      <h2 class="section-title">Hướng dẫn khiếu nại dịch vụ</h2>
      <p class="text-center text-gray-600 mb-8">
        Chúng tôi rất tiếc nếu bạn không hài lòng với dịch vụ. Dưới đây là 5 bước đơn giản để gửi khiếu nại và nhận hỗ trợ nhanh chóng.
      </p>

      <!-- Hướng dẫn 5 bước -->
      
      <div class="steps">
        
      <div class="step">
          <div class="step-number">0</div>
          <div class="step-content">
            <h3>Thời gian</h3>
            <p>Thời gian giải quyết khiếu nại là 3 ngày kể từ khi đơn dịch vụ hoàn tất. Sau thời gian này các khiếu nại sẽ không được chúng tôi hổ trợ giải quyết.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">1</div>
          <div class="step-content">
            <h3>Xác định vấn đề</h3>
            <p>Xem lại thông tin dịch vụ của bạn trên trang <u><a href="xemDV.php">"Danh sách dịch vụ"</a></u>  để xác định rõ vấn đề gặp phải (ví dụ: thái độ nhân viên, thời gian trễ hẹn, hoặc chất lượng dịch vụ).</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">2</div>
          <div class="step-content">
            <h3>Chuẩn bị thông tin</h3>
            <p>Ghi lại mã đơn hàng (Mã đơn), ngày đặt, nhân viên, và chi tiết vấn đề. Bạn cũng có thể chụp ảnh hoặc lưu bằng chứng nếu có (ví dụ: video, hình ảnh).</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">3</div>
          <div class="step-content">
            <h3>Liên hệ với chúng tôi</h3>
            <p>Để giải quyết khiếu nại một cách nhanh chóng, bạn có thể gửi tin nhắn trực tiếp qua <u> <a href="https://zalo.me/0797008745" target="_blank" class="zalo">Zalo
            </a></u> của chúng tôi hoặc gọi điện trực tiếp qua <u><a href="tel:+84987654321" class="phone">
            Bộ phận CSKH
            </a> </u> để giải quyết khiếu nại một các nhanh chóng.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">4</div>
          <div class="step-content">
            <h3>Chờ phản hồi</h3>
            <p>Đội ngũ hỗ trợ sẽ phản hồi trong vòng 24-48 giờ qua email hoặc số điện thoại bạn cung cấp. Chúng tôi sẽ xác minh và đưa ra giải pháp phù hợp.</p>
          </div>
        </div>

        <div class="step">
          <div class="step-number">5</div>
          <div class="step-content">
            <h3>Nhận giải quyết</h3>
            <p>Sau khi đồng ý với giải pháp (hoàn tiền, hỗ trợ lại, hoặc xin lỗi), chúng tôi sẽ thực hiện ngay. Bạn có thể đánh giá lại dịch vụ sau khi hoàn tất.</p>
          </div>
        </div>
      </div>

      <!-- Thông tin liên hệ -->
      <div class="contact-info">
        <p><i class="fas fa-headset mr-2"></i>Liên hệ hỗ trợ ngay:</p>
        <p><i class="fas fa-phone mr-2"></i><a href="tel:+84987654321">+84 97008745</a></p>
        <p><i class="fas fa-envelope mr-2"></i><a href="mailto:support@dicungtoi.com">support@dicuongtoi.com</a></p>
        <p><i class="fab fa-zalo mr-2"></i><a href="https://zalo.me/0797008745" target="_blank">Chat qua Zalo</a></p>
      </div>
    </section>
  </main>

  <?php include_once(__DIR__ . '/Assets/footer.php'); ?>
</body>

</html>