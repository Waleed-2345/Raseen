<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header('Location: signin.php');
    exit;
}
$investor_id = $_SESSION['investor_id'];
$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) { die("فشل الاتصال: " . $conn->connect_error); }
$sql_pref = "SELECT interests, region FROM investors WHERE ID = $investor_id";
$result_pref = $conn->query($sql_pref);
$preferred_interests = '';
$preferred_region = '';
if ($result_pref && $row_pref = $result_pref->fetch_assoc()) {
    $preferred_interests = $row_pref['interests'];
    $preferred_region = $row_pref['region'];
}
$field_map = [
    'tourism'    => 'سياحي',
    'tech'       => 'تقني',
    'business'   => 'تجاري',
    'industrial' => 'صناعي',
    'logistics'  => 'لوجستي',
];
$interest_array = array_map('trim', explode(',', $preferred_interests));
$where = [];
if (!empty($preferred_region)) {
    $where[] = "region = '" . $conn->real_escape_string($preferred_region) . "'";
}
if (!empty($interest_array)) {
    $interest_conditions = [];
    foreach ($interest_array as $interest) {
        $interest_conditions[] = "field LIKE '%" . $conn->real_escape_string($interest) . "%'";
    }
    $where[] = "(" . implode(' OR ', $interest_conditions) . ")";
}
$query = "SELECT * FROM projects";
if (!empty($where)) {
    $query .= " WHERE " . implode(' AND ', $where);
}
$query .= " ORDER BY trust_score DESC";
$result = $conn->query($query);

echo '<style>
.raseen-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    direction: rtl;
    justify-content: flex-start;
}
.raseen-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 2px 12px #0001;
    padding: 20px 18px;
    width: 340px;
    text-align: right;
    border: 1px solid #e0e0e0;
    margin-bottom: 18px;
    transition: box-shadow 0.2s;
}
.raseen-card:hover {
    box-shadow: 0 4px 20px #00968830;
}
.raseen-card h3 {
    margin: 0 0 12px 0;
    color: #115e22;
    font-size: 1.23em;
    font-weight: 800;
}
.raseen-card .raseen-summary {
    color: #444;
    font-size: 1em;
    margin-bottom: 12px;
    min-height: 65px;
}
.raseen-card .raseen-label {
    font-weight: bold;
    color: #17633b;
}
.raseen-card .raseen-value {
    color: #2d2d2d;
    font-weight: 500;
}
.raseen-card .raseen-btn {
    display:inline-block;
    background:#0a5e24;
    color:#fff;
    padding:7px 20px;
    border-radius:7px;
    text-decoration:none;
    font-weight:600;
    font-size: 1em;
    transition: background 0.15s;
    margin-top:0;
    margin-bottom:0;
}
.raseen-card .raseen-btn.interest-btn {
    background: #0e3e76;
}
.raseen-card .raseen-btn:hover {
    background: #188d44;
}
.raseen-card .raseen-btn.interest-btn:hover {
    background: #2236a5;
}
.raseen-card .raseen-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}
</style>';

function showProjectCard($row, $field_map) {
    $financeType = $row['finance_type'] === 'equity' ? 'استثمار' : 'قرض';
    if ($financeType === 'استثمار') {
        $reason = !empty($row['investment_reason_equity']) ? $row['investment_reason_equity'] : $row['investment_reason'];
    } else {
        $reason = $row['loan_reason'];
    }
    $investorShare = $financeType === 'استثمار' ? $row['investor_share'] . '%' : '-';
    $detailsUrl = 'project_details.php?id=' . $row['id'];
    $interestUrl = 'chat.php?project_id=' . $row['id'];
    $field_ar = isset($field_map[$row['field']]) ? $field_map[$row['field']] : $row['field'];
    echo '<div class="raseen-card">';
    echo '<h3>' . htmlspecialchars($row['project_name']) . '</h3>';
    echo '<div class="raseen-summary">' . nl2br(htmlspecialchars($row['project_summary'])) . '</div>';
    echo '<div><span class="raseen-label">المنطقة:</span> <span class="raseen-value">' . htmlspecialchars($row['region']) . '</span></div>';
    echo '<div><span class="raseen-label">المدينة:</span> <span class="raseen-value">' . htmlspecialchars($row['city']) . '</span></div>';
    echo '<div><span class="raseen-label">نوع المشروع:</span> <span class="raseen-value">' . htmlspecialchars($field_ar) . '</span></div>';
    if (!empty($reason)) {
        echo '<div><span class="raseen-label">سبب طلب التمويل:</span> <span class="raseen-value">' . nl2br(htmlspecialchars($reason)) . '</span></div>';
    }
    echo '<div><span class="raseen-label">نوع التمويل:</span> <span class="raseen-value">' . $financeType . '</span></div>';
    if ($financeType === 'استثمار' && $row['investor_share'] > 0) {
        echo '<div><span class="raseen-label">نسبة المستثمر:</span> <span class="raseen-value">' . $investorShare . '</span></div>';
    }
    echo '<div><span class="raseen-label">درجة الثقة:</span> <span class="raseen-value">' . htmlspecialchars($row['trust_score']) . '</span></div>';
    echo '<div class="raseen-actions">';
    echo '<a href="' . $detailsUrl . '" class="raseen-btn">عرض التفاصيل</a>';
    echo '<a href="' . $interestUrl . '" class="raseen-btn interest-btn">أنا مهتم</a>';
    echo '</div>';
    echo '</div>';
}

if ($result && $result->num_rows > 0) {
    echo '<div class="raseen-cards">';
    while($row = $result->fetch_assoc()) {
        showProjectCard($row, $field_map);
    }
    echo '</div>';
} else {
    $query_fallback = "SELECT * FROM projects ORDER BY trust_score DESC LIMIT 8";
    $result_fallback = $conn->query($query_fallback);
    if ($result_fallback && $result_fallback->num_rows > 0) {
        echo '<div class="raseen-cards">';
        while($row = $result_fallback->fetch_assoc()) {
            showProjectCard($row, $field_map);
        }
        echo '</div>';
    } else {
        echo '<div style="text-align:center; margin:80px 0 40px 0;">
    <span style="display:inline-block; color:#15803d; background:#e6faed; border-radius:13px; font-size:1.35em; padding:20px 45px; font-weight:700; box-shadow:0 4px 16px #19c37d1a;">
        لا توجد مشاريع متاحة حالياً
    </span>
</div>';
    }
}
echo '<div style="width:100%; text-align:center; margin:40px 0 15px 0;">
        <a href="dashboard.php" style="display:inline-block; background:#185e22; color:#fff; font-size:1.1em; padding:9px 34px; border-radius:8px; text-decoration:none; font-weight:600;">عودة</a>
      </div>';
$conn->close();
?>
