<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đi cùng tôi</title>
  <link rel="stylesheet" href="\WEB_ThueHoTroKhamBenh\public\Assets\styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Link Slick CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

  <!-- Link jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Link Slick JS -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>


  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .header {
      position: sticky;
      top: 0;
      z-index: 50;
      background-color: white;
    }
  </style>
</head>

<body class="bg-white text-gray-800">
  <?php
    include_once("public\Assets\header.php");
  ?>
  <main>
    <!-- Banner -->
    <section class="banner" style="background-image: url('IMG/banner.jpg');">
      <div class="banner-overlay">
        <h1 class="banner-title">Dịch vụ hỗ trợ đi khám bệnh</h1>
        <p class="banner-subtitle">Kết nối người cần giúp đỡ với người hỗ trợ tận tâm</p>
        <a href="#dat" class="cta-button">Đặt dịch vụ ngay</a>
       
      </div>
    </section>
    <div>
      <h3 class="center"><i>" Vì lợi ích cộng đồng "</i></h3>
    </div>
    <!-- Lợi ích -->
    <section id="loiich" class="section">
      <h2 class="section-title">Tại sao chọn "Đi cùng tôi"?</h2>
      <div class="features">
        <div class="feature-box">
          <h3 class="feature-title">An toàn</h3>
          <p>Nhân viên được xác minh rõ ràng, đảm bảo an toàn cho người bệnh.</p>
        </div>
        <div class="feature-box">
          <h3 class="feature-title">Chi phí</h3>
          <p>22.000/giờ, hỗ trợ cho người tham gia bảo hiểm y tế.</p>
        </div>
        <div class="feature-box">
          <h3 class="feature-title">Thân thiện</h3>
          <p>Người đồng hành chu đáo, tận tâm và có kỹ năng hỗ trợ y tế cơ bản.</p>
        </div>
      </div>
      

      <!-- Phần hình ảnh chuyển động tự động -->
      <div class="image-carousel">
        <img src="IMG/meo1.jpeg" alt="Image 1">
        <img src="IMG/meo2.jpeg" alt="Image 2">
        <img src="IMG/meo3.jpeg" alt="Image 3">
      </div>
    </section>

    <section id="dat" class="section light">
      <h2 class="section-title">Sẵn sàng đồng hành cùng bạn!</h2>
      <p class="mb-6">Chúng tôi hỗ trợ tại các bệnh viện lớn trong khu vực TP. Hồ Chí Minh:</p>
      <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-w-4xl mx-auto text-left">
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Chợ Rẫy</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Đại học Y Dược TP.HCM</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Nhân dân 115</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Hòa Hảo - Medic</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Từ Dũ</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Nhi Đồng 1</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện FV</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Quận 1</li>
        <li class="p-4 border rounded-lg shadow-sm bg-white">Bệnh viện Thống Nhất</li>
      </ul>
      <div class="mt-8">
        <a href="#" class="cta-button">Đặt dịch vụ</a>
      </div>
    </section>

    <?php
     include("public\Assets/footer.php");  
    ?>
  </main>

  <script>
    $(document).ready(function () {
      $('.image-carousel').slick({
        autoplay: true, // Tự động chuyển hình
        autoplaySpeed: 2000, // Thời gian chuyển đổi giữa các hình (2 giây)
        dots: true, // Hiển thị các chấm chỉ dẫn
        arrows: false, // Ẩn mũi tên điều hướng
      });
    });
  </script>


</body>

</html>