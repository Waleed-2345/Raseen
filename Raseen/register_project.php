<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

if (!isset($_SESSION['entrepreneur_id'])) {
    header('Location: signin.php');
    exit;
}

$entrepreneur_id = $_SESSION['entrepreneur_id'];
$project_name = $_POST['project_name'];
$project_summary = $_POST['project_summary'];
$commercial_registration = $_POST['commercial_registration'];
$field = $_POST['field'];
$region = $_POST['region'];
$city = $_POST['city'];

$sub_fields = isset($_POST['sub_fields']) ? implode(',', $_POST['sub_fields']) : null;
$other_field_detail = $_POST['other_field_detail'] ?? null;

$has_team = isset($_POST['has_team']) && $_POST['has_team'] === 'نعم' ? 1 : 0;
$team_size = $has_team ? (int)$_POST['team_size'] : 0;

$has_rent = isset($_POST['has_rent']) && $_POST['has_rent'] === 'نعم' ? 1 : 0;
$rent_cost = $has_rent ? (float)$_POST['rent_cost'] : 0;

$has_salaries = isset($_POST['has_salaries']) && $_POST['has_salaries'] === 'نعم' ? 1 : 0;
$salary_range = $has_salaries ? (float)$_POST['salary_range'] : 0;

$has_operating_costs = isset($_POST['has_operating_costs']) && $_POST['has_operating_costs'] === 'نعم' ? 1 : 0;
$operating_costs = $has_operating_costs ? (float)$_POST['operating_costs'] : 0;

$has_marketing = isset($_POST['has_marketing']) && $_POST['has_marketing'] === 'نعم' ? 1 : 0;
$marketing_cost = $has_marketing ? (float)$_POST['marketing_cost'] : 0;

$has_other_costs = isset($_POST['has_other_costs']) && $_POST['has_other_costs'] === 'نعم' ? 1 : 0;
$other_costs = $has_other_costs ? (float)$_POST['other_costs'] : 0;

$finance_type = $_POST['finance_type'] ?? null;

$requested_amount_equity = $_POST['requested_amount_equity'] ?? 0;
$investment_reason_equity = $_POST['investment_reason_equity'] ?? '';
$investor_share = $_POST['investor_share'] ?? 0;
$expected_monthly_return_before_equity = $_POST['expected_monthly_return_before_equity'] ?? 0;
$expected_monthly_return_equity = $_POST['expected_monthly_return_equity'] ?? 0;

$loan_amount = $_POST['loan_amount'] ?? 0;
$loan_reason = $_POST['loan_reason'] ?? '';
$loan_repayment_type = $_POST['loan_repayment_type'] ?? '';
$installment_period = $_POST['installment_period'] ?? 0;
$expected_monthly_return_before_loan = $_POST['expected_monthly_return_before_loan'] ?? 0;
$expected_monthly_return_loan = $_POST['expected_monthly_return_loan'] ?? 0;

if ($finance_type == "loan") {
    $requested_amount = $loan_amount;
    $investment_reason = $loan_reason;
    $expected_monthly_return_before = $expected_monthly_return_before_loan;
    $expected_monthly_return = $expected_monthly_return_loan;
} else {
    $requested_amount = $requested_amount_equity;
    $investment_reason = $investment_reason_equity;
    $expected_monthly_return_before = $expected_monthly_return_before_equity;
    $expected_monthly_return = $expected_monthly_return_equity;
}

if (!$requested_amount) $requested_amount = 0;
if (!$investment_reason) $investment_reason = '';
if (!$expected_monthly_return_before) $expected_monthly_return_before = 0;
if (!$expected_monthly_return) $expected_monthly_return = 0;

$score = 100;
$bonus = 0;
$totalMonthly = $rent_cost + ($salary_range * $team_size) + $operating_costs + $marketing_cost + $other_costs;
$annual = $totalMonthly * 12;

if (!$has_team || $team_size == 0) $score -= 15;
else if ($team_size < 3) $score -= 8;

if ($salary_range > 0 && $salary_range < 4000) $score -= 10;

$salariesTotal = $salary_range * $team_size;
$salariesRatio = $totalMonthly > 0 ? $salariesTotal / $totalMonthly : 0;
if ($salariesRatio > 0.6) $score -= 10;

if (!$has_marketing || $marketing_cost == 0) $score -= 5;

if ($finance_type == "loan") {
    if ($expected_monthly_return_before < $totalMonthly * 0.5) $score -= 10;

    if ($loan_amount > 3 * $annual) $score -= 30;
    else if ($loan_amount > 2 * $annual) $score -= 20;
    else if ($loan_amount > 1 * $annual) $score -= 10;

    if ($loan_repayment_type == 'installments') $bonus += 5;
    else if ($loan_repayment_type == 'one_payment') $score -= 10;

    if ($installment_period >= 12 && $installment_period <= 48) $bonus += 2;
    else if ($installment_period < 6 || $installment_period > 48) $score -= 5;

    if ($expected_monthly_return > $totalMonthly * 1.5) $bonus += 5;
    else if ($expected_monthly_return > $totalMonthly) $bonus += 3;

    $growth = ($expected_monthly_return_before == 0) ? 0 : (($expected_monthly_return - $expected_monthly_return_before) / $expected_monthly_return_before);
    if ($expected_monthly_return_before == 0 && $expected_monthly_return > 0) $growth = 1;
    if ($growth >= 1) $bonus += 5;
    else if ($growth >= 0.5) $bonus += 3;

} else {
    if ($expected_monthly_return_before < $totalMonthly * 0.5) $score -= 10;
    if ($expected_monthly_return < $totalMonthly) $score -= 15;

    if ($requested_amount_equity > 3 * $annual) $score -= 30;
    else if ($requested_amount_equity > 2 * $annual) $score -= 20;
    else if ($requested_amount_equity > 1 * $annual) $score -= 10;

    if ($expected_monthly_return >= $totalMonthly * 1.5) $bonus += 5;
    else if ($expected_monthly_return >= $totalMonthly) $bonus += 3;

    $growth = ($expected_monthly_return_before == 0) ? 0 : (($expected_monthly_return - $expected_monthly_return_before) / $expected_monthly_return_before);
    if ($expected_monthly_return_before == 0 && $expected_monthly_return > 0) $growth = 1;
    if ($growth >= 1) $bonus += 5;
    else if ($growth >= 0.5) $bonus += 3;
}

if ($bonus > 15) $bonus = 15;
$score += $bonus;
if ($score > 95) $score = 95;
if ($score < 0) $score = 0;

$trust_score = $score;

$sql = "INSERT INTO projects (
    entrepreneur_id, project_name, project_summary, commercial_registration, field, sub_fields, other_field_detail,
    region, city, has_team, team_size, has_rent, rent_cost, has_salaries, salary_range, has_operating_costs, operating_costs,
    has_marketing, marketing_cost, has_other_costs, other_costs, finance_type,
    requested_amount_equity, investment_reason_equity, investor_share, expected_monthly_return_equity,
    loan_amount, loan_reason, loan_repayment_type, installment_period,
    requested_amount, investment_reason, expected_monthly_return_before, expected_monthly_return, trust_score
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("فشل في تحضير الاستعلام: " . $conn->error);
}

$stmt->bind_param(
    "issssssssiiididididssssdsdssdssiddi",
    $entrepreneur_id,
    $project_name,
    $project_summary,
    $commercial_registration,
    $field,
    $sub_fields,
    $other_field_detail,
    $region,
    $city,
    $has_team,
    $team_size,
    $has_rent,
    $rent_cost,
    $has_salaries,
    $salary_range,
    $has_operating_costs,
    $operating_costs,
    $has_marketing,
    $marketing_cost,
    $has_other_costs,
    $other_costs,
    $finance_type,
    $requested_amount_equity,
    $investment_reason_equity,
    $investor_share,
    $expected_monthly_return_equity,
    $loan_amount,
    $loan_reason,
    $loan_repayment_type,
    $installment_period,
    $requested_amount,
    $investment_reason,
    $expected_monthly_return_before,
    $expected_monthly_return,
    $trust_score
);

if ($stmt->execute()) {
    echo "<script>
        alert('تم حفظ المشروع بنجاح');
        window.location.href = 'entrepreneur_homepage.php';
    </script>";
} else {
    die('خطأ في الإدخال: ' . $stmt->error);
}

$stmt->close();
$conn->close();
?>
