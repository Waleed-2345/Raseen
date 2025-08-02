<?php
$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$query = "SELECT id, project_name, project_summary, trust_score, requested_amount, city FROM projects";
$result = $conn->query($query);

$projects = [];
while ($row = $result->fetch_assoc()) {
    $row['city'] = trim(preg_replace('/\s+/', ' ', $row['city']));
    $projects[] = $row;
}

$citiesInfo = [];
$result2 = $conn->query("SELECT name_ar, one_line_summary, total_population, male_percentage, female_percentage FROM cities");
while ($row2 = $result2->fetch_assoc()) {
    $citiesInfo[$row2['name_ar']] = [
        "summary"    => $row2['one_line_summary'],
        "population" => $row2['total_population'],
        "males"      => $row2['male_percentage'],
        "females"    => $row2['female_percentage'],
    ];
}
$conn->close();
?>

<html>
<head>
  <meta charset="UTF-8" />
  <title>استكشاف الفرص الاستثمارية في المدن السعودية</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="common.css">
  <style>
    .city-info-panel { display: none; }
    .city-info-panel.active { display: block; }
	.city-marker {
  position: absolute;
  background-color: #008080;
  color: #fff;
  border: none;
  padding: 5px 8px;
  border-radius: 4px;
  cursor: pointer;
}
#city-info-panel {
  background: #ffffff; /* لون خلفية الصندوق */
  border-radius: 12px; /* استدارة الحواف */
  padding: 20px; /* مسافة داخلية */
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); /* ظل الصندوق */
  font-family: "Tajawal", sans-serif; /* خط عربي جميل */
  width: 350px; /* العرض */
  line-height: 1.8; /* ارتفاع الأسطر */
}

#city-info-panel h3 {
  margin-bottom: 10px;
  font-size: 22px;
  color: #004c6d; /* لون العنوان */
}

#city-info-panel ul {
  list-style: none;
  padding: 0;
  margin: 0 0 15px 0;
}

#city-info-panel ul li {
  font-size: 16px;
  margin-bottom: 5px;
}

.project-card {
  border-top: 1px solid #eee;
  padding-top: 10px;
  margin-top: 10px;
}

.project-card h4 {
  margin: 5px 0;
  font-size: 18px;
  color: #333;
}

.project-card p {
  font-size: 14px;
  color: #555;
  margin: 4px 0;
}

.project-actions {
  margin-top: 8px;
}

.project-actions .action-btn {
  background-color: #005a87;
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  margin: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s;
}

.project-actions .action-btn:hover {
  background-color: #003f5c;
}
#city-info-panel {
  position: absolute;
  top: 50px;
  right: 30px; /* يجعل الصندوق ثابتًا على يمين الصفحة */
  width: 350px;
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  font-family: "Tajawal", sans-serif;
  line-height: 1.8;
  display: none;
  z-index: 9999;
}

  </style>
</head>
<body class="explore-cities">
  <h2>استكشاف الفرص الاستثمارية في المدن السعودية</h2>
  <div class="main-flex">
    <div id="chooseCityCard" class="choose-city-card">
      <h3>اختر مدينة</h3>
      <p>اضغط على أي مدينة بالخريطة لعرض بياناتها<br>والمشاريع المرتبطة بها.</p>
    </div>
    <div id="city-info-panel" class="city-info-panel"></div>

    <div class="map-container">
      <img src="SA.svg" class="map-img" alt="خريطة السعودية">
      <?php
      $citiesMarkers = [
        "ينبع" => "top:59.6%; left:26.5%;",
        "جدة" => "top:65.3%; left:29.7%;",
        "النماص" => "top:74.3%; left:39.0%;",
        "أبها" => "top:76.3%; left:40%;",
        "نجران" => "top:78.4%; left:50.8%;",
        "حائل" => "top:29.4%; left:35.3%;",
        "عنيزة" => "top:38.9%; left:39.7%;",
        "الرياض" => "top:43.8%; left:52.0%;",
        "الخبر" => "top:32.7%; left:65.7%;",
        "الدمام" => "top:39.7%; left:68.9%;"
      ];

      foreach ($citiesMarkers as $city => $position) {
        echo "<button class='city-marker' style='{$position}' data-city='{$city}'>{$city}</button>";
      }
      ?>
    </div>
  </div>

<script>
const allProjects = <?php echo json_encode($projects, JSON_UNESCAPED_UNICODE); ?>;
const citiesInfo = <?php echo json_encode($citiesInfo, JSON_UNESCAPED_UNICODE); ?>;

document.querySelectorAll('.city-marker').forEach(marker => {
  marker.onclick = function() {
    const city = this.dataset.city;
    const info = citiesInfo[city] || {};
    const cityProjects = allProjects.filter(p => p.city === city);

    let html = `<h3>${city}</h3>`;

    if (info.summary || info.population) {
      html += `
        <ul>
          <li>${info.summary ?? ''}</li>
          <li><b>عدد السكان:</b> ${info.population ?? '-'}</li>
          <li><b>نسبة الذكور:</b> ${info.males ?? '-'}</li>
          <li><b>نسبة الإناث:</b> ${info.females ?? '-'}</li>
        </ul>`;
    } else {
      html += `<div class="no-data">لا توجد بيانات متاحة لهذه المدينة بعد.</div>`;
    }

    cityProjects.forEach(p => {
      html += `
        <div style="border-bottom:1px solid #ddd; padding:10px 0;">
          <h4>${p.project_name}</h4>
          <p>${p.project_summary.substring(0,80)}...</p>
          <p>
            <strong>نسبة النجاح:</strong> ${p.trust_score ?? '-'}% /
            <strong>المطلوب:</strong> ${p.requested_amount} ريال
          </p>
          <button onclick="window.location.href='project_details.php?id=${p.id}'">تفاصيل أكثر</button>
          <button onclick="alert('سيفتح تواصل مع المشروع رقم ${p.id}')">أنا مهتم</button>
        </div>`;
    });

    const panel = document.getElementById('city-info-panel');
    panel.innerHTML = html;

    panel.style.position = 'absolute';
    panel.style.top = '50px';
    panel.style.left = '20px';
    panel.style.width = '350px';
    panel.style.maxHeight = '400px';
    panel.style.overflowY = 'auto';
    panel.style.background = '#fff';
    panel.style.padding = '15px';
    panel.style.borderRadius = '10px';
    panel.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
    panel.style.zIndex = '9999';
    panel.style.display = 'block';

    document.getElementById('chooseCityCard').style.display = 'none';
  };
});
</script>

</body>
</html>

