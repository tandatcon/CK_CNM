<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đồng hồ bấm giờ</title>
  <style>
    #timer {
      font-size: 2rem;
      font-family: monospace;
      color: #0077cc;
    }
  </style>
</head>
<body>
  <h2>Đã trôi qua từ 22:00:</h2>
  <div id="timer">00:00:00</div>

  <script>
    // Cài đặt giờ A (22:00 hôm nay hoặc hôm qua nếu chưa tới 22:00)
    const now = new Date();
    let startTime = new Date();
    startTime.setHours(22, 0, 0, 0); // Giờ A: 22:00:00

    // Nếu hiện tại trước 22:00 hôm nay => lùi về hôm qua
    if (now < startTime) {
      startTime.setDate(startTime.getDate() - 1);
    }

    function updateTimer() {
      const currentTime = new Date();
      const diff = Math.floor((currentTime - startTime) / 1000); // Tổng giây

      const hours = String(Math.floor(diff / 3600)).padStart(2, '0');
      const minutes = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
      const seconds = String(diff % 60).padStart(2, '0');

      document.getElementById('timer').textContent = `${hours}:${minutes}:${seconds}`;
    }

    // Cập nhật mỗi giây
    updateTimer(); // Gọi ngay lần đầu
    setInterval(updateTimer, 1000);
  </script>
</body>
</html>
