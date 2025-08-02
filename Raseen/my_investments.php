<?php
session_start();
if (!isset($_SESSION['investor_id'])) {
    header("Location: signin.php");
    exit;
}
$investor_id = $_SESSION['investor_id'];

$conn = new mysqli("localhost", "root", "", "raseen");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب الاستثمارات مع التقييم لو موجود
$query = "
    SELECT 
        inv.*, 
        pr.project_name, pr.city, pr.region,
        pu.last_update_at, pu.last_update_text, pu.last_pdf,
        r.overall_star, r.usability_star, r.trust_star, r.communication_star
    FROM investments inv
    JOIN projects pr ON inv.project_id = pr.id
    LEFT JOIN (
        SELECT investment_id, MAX(created_at) AS last_update_at,
            SUBSTRING_INDEX(GROUP_CONCAT(update_text ORDER BY created_at DESC SEPARATOR '|||'), '|||', 1) AS last_update_text,
            SUBSTRING_INDEX(GROUP_CONCAT(pdf_report ORDER BY created_at DESC SEPARATOR '|||'), '|||', 1) AS last_pdf
        FROM project_updates GROUP BY investment_id
    ) pu ON pu.investment_id = inv.id
    LEFT JOIN platform_ratings r ON r.investment_id = inv.id AND r.investor_id = inv.investor_id
    WHERE inv.investor_id = ?
    ORDER BY inv.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $investor_id);
$stmt->execute();
$result = $stmt->get_result();

$investments = [];
while ($row = $result->fetch_assoc()) {
    $investments[] = $row;
}
$stmt->close();
$conn->close();

// ابحث عن أول استثمار مكتمل لم يُقيّم بعد (للتنبيه)
$pending_to_rate = null;
foreach ($investments as $inv) {
    $is_completed = ($inv['status'] === 'accepted' && intval($inv['investor_signed']) === 1 && intval($inv['entrepreneur_signed']) === 1);
    $is_rated = isset($inv['overall_star']) && ($inv['overall_star'] + $inv['usability_star'] + $inv['trust_star'] + $inv['communication_star']) > 0;
    if ($is_completed && !$is_rated) {
        $pending_to_rate = $inv;
        break;
    }
}
?>
<html>
<head>
    <title>استثماراتي | منصة رصين</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma; background: #f7faf7; margin:0; padding:0;}
        .container { max-width: 960px; margin: 38px auto; background:#fff; border-radius:13px; box-shadow:0 4px 28px #19c37d1c; padding:30px 28px; position: relative;}
        h2 { color:#15724e; margin-bottom:20px; display:inline-block;}
        .inv-table { width:100%; border-collapse:collapse; margin-top:16px;}
        .inv-table th, .inv-table td { padding:13px 7px; border-bottom:1px solid #e1eaea; text-align:right; vertical-align: top;}
        .inv-table th { background:#e7f9f3; color:#186246; font-size:1.08em;}
        .status-accepted { color: #19c37d; font-weight: bold;}
        .status-pending { color: #ffa800; }
        .status-rejected { color: #c33; }
        .status-negotiating { color: #1564c3; }
        .empty { text-align:center; color:#666; margin: 54px 0;}
        .update-box { background:#f0f8f8; padding:8px 12px; border-radius:8px; font-size:.9em; margin-top:4px; }
        .small-link { font-size:.85em; color:#1564c3; text-decoration:none; margin-left:6px; }
        .no-update { color:#888; font-size:.9em; }
        .back-btn { position: absolute; top: 32px; left: 28px; background: #168a53; color: #fff; padding:10px 16px; border-radius:8px; text-decoration:none; font-weight:600; font-size:.9em; }
        .back-btn:hover { filter:brightness(1.05); }
        /* بانر التنبيه */
        .rate-alert { background:#fff8ea; border-right:7px solid #ffc107; padding:18px 18px; margin-bottom:22px; border-radius:8px; display:flex; align-items:center; gap:15px; box-shadow:0 2px 14px #ffc10722;}
        .rate-alert b { color:#168a53;}
        .rate-btn { background:#168a53; color:#fff; padding:8px 24px; border-radius:7px; text-decoration:none; font-size:1.1em; font-weight:600; margin-right:auto;}
        .rate-close { background:none; border:none; font-size:22px; color:#c33; margin-right:8px; cursor:pointer;}
        @media (max-width: 800px){
          .back-btn { position: static; display:inline-block; margin-bottom:12px; }
          .rate-alert { flex-direction:column; align-items:flex-start; }
        }
        /* بوب أب */
        .modal { display:none; position:fixed;z-index:999;top:0;left:0;width:100vw;height:100vh;background:#0007;align-items:center;justify-content:center;}
        .modal-content { background:#fff; padding:32px 22px 24px; border-radius:13px; min-width:370px; max-width:99vw; box-shadow:0 6px 40px #0e91871a; text-align:center; position:relative;}
        .modal-content h2 { color:#17836b; font-size:1.18em; margin-bottom:15px;}
        .modal-close { background:none; border:none; color:#c44; font-size:22px; position:absolute; top:14px; left:14px; cursor:pointer;}
        .stars { display:inline-flex; gap:8px; font-size:2.0em; direction:ltr; margin-bottom:2px; }
        .star { cursor:pointer; user-select:none; color:#d3d6df; transition:.11s; }
        .star.filled { color:#fec340; text-shadow: 0 2px 9px #fec34034;}
        .feedback-box { width:96%; min-height:38px; padding:6px 10px; border-radius:6px; border:1px solid #ccc; margin-top:7px; }
        .success-msg { color:#17836b; background:#e7f7f1; font-weight:600; margin:13px 0 8px; border-radius:6px; padding:7px 0;}
        .btn { background:#168a53; color:#fff; border:none; padding:7px 21px; border-radius:7px; margin-top:12px; cursor:pointer;}
        .rated { color:#168a53; font-weight:600; }
        @media (max-width: 700px){ .modal-content{ min-width:98vw; } }
    </style>
</head>
<body>
    <div class="container">
        <a href="investor_homepage.php" class="back-btn">← الرئيسية</a>
        <h2>استثماراتي</h2>

        <?php if ($pending_to_rate): ?>
          <div id="rate-alert" class="rate-alert">
            <span style="font-weight:600; color:#bd8100;">
              تذكير:
            </span>
            <span style="color:#633;">
              لديك استثمار مكتمل في <b><?= htmlspecialchars($pending_to_rate['project_name']) ?></b> ولم تقم بتقييم المنصة بعد.
            </span>
            <button class="rate-btn" onclick="openModal(<?= intval($pending_to_rate['id']) ?>)">قيّم الآن</button>
            <button onclick="document.getElementById('rate-alert').style.display='none';" class="rate-close">&times;</button>
          </div>
        <?php endif; ?>

        <table class="inv-table">
            <tr>
                <th>المشروع</th>
                <th>الحالة</th>
                <th>المبلغ</th>
                <th>الحصة (%)</th>
                <th>آخر تحديث</th>
                <th>ملخص التحديث</th>
            </tr>
            <?php foreach ($investments as $inv): ?>
                <tr>
                    <td style="min-width:180px;">
                        <div><b><?= htmlspecialchars($inv['project_name']) ?></b></div>
                        <div style="color:#666;font-size:.94em;"><?= htmlspecialchars($inv['city']) ?> - <?= htmlspecialchars($inv['region']) ?></div>
                    </td>
                    <td>
                        <?php
                            $st = $inv['status'];
                            if ($st == 'accepted') echo "<span class='status-accepted'>تم التوقيع</span>";
                            elseif ($st == 'pending') echo "<span class='status-pending'>بانتظار الموافقة</span>";
                            elseif ($st == 'negotiating') echo "<span class='status-negotiating'>تفاوض</span>";
                            elseif ($st == 'rejected') echo "<span class='status-rejected'>مرفوض</span>";
                            else echo htmlspecialchars($st);
                        ?>
                    </td>
                    <td><?= number_format($inv['amount']) ?> ريال</td>
                    <td><?= htmlspecialchars($inv['equity_percentage']) ?: '-' ?></td>
                    <td>
                        <?php if (!empty($inv['last_update_at'])): ?>
                            <?= date("Y-m-d", strtotime($inv['last_update_at'])) ?>
                        <?php else: ?>
                            <div class="no-update">لا يوجد</div>
                        <?php endif; ?>
                    </td>
                    <td style="max-width:250px;">
                        <?php if (!empty($inv['last_update_text'])): ?>
                            <div class="update-box">
                                <?= nl2br(htmlspecialchars(mb_strimwidth($inv['last_update_text'], 0, 100, '...'))) ?>
                                <div style="margin-top:4px;">
                                  <a href="investment_updates.php?investment_id=<?= urlencode($inv['id']) ?>" class="small-link">شاهد كل التحديثات</a>
                                  <?php if (!empty($inv['last_pdf'])): ?>
                                    <a href="uploads/<?= rawurlencode($inv['last_pdf']) ?>" target="_blank" class="small-link">تحميل التقرير</a>
                                  <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-update">لم يُرسل تحديث بعد</div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- بوب أب التقييم -->
        <?php if ($pending_to_rate): ?>
        <div class="modal" id="modal-<?= intval($pending_to_rate['id']) ?>">
          <div class="modal-content">
            <button class="modal-close" onclick="closeModal(<?= intval($pending_to_rate['id']) ?>)">&times;</button>
            <h2>كيف تقيّم تجربتك في هذا الاستثمار؟</h2>
            <form class="rate-form" data-id="<?= intval($pending_to_rate['id']) ?>">
              <input type="hidden" name="investment_id" value="<?= intval($pending_to_rate['id']) ?>">
              <div style="text-align:right;margin:0 0 12px 0;">
                <label>1. التقييم العام</label>
                <div class="stars" data-name="overall_star"></div>
                <input type="hidden" name="overall_star" value="5">
                <label>2. سهولة الاستخدام</label>
                <div class="stars" data-name="usability_star"></div>
                <input type="hidden" name="usability_star" value="5">
                <label>3. الثقة</label>
                <div class="stars" data-name="trust_star"></div>
                <input type="hidden" name="trust_star" value="5">
                <label>4. التواصل</label>
                <div class="stars" data-name="communication_star"></div>
                <input type="hidden" name="communication_star" value="5">
                <label>5. يهمنا رأيك:</label>
                <textarea name="feedback" class="feedback-box" placeholder="يهمنا رأيك أو اقتراحك حول تجربتك"></textarea>
              </div>
              <button type="submit" class="btn">حفظ التقييم</button>
              <div class="success-msg" style="display:none;">تم حفظ التقييم! شكراً لك </div>
            </form>
          </div>
        </div>
        <?php endif; ?>

    </div>
    <script>
    function openModal(id){ document.getElementById('modal-'+id).style.display='flex'; }
    function closeModal(id){ document.getElementById('modal-'+id).style.display='none'; }

    // نجوم التقييم
    document.querySelectorAll('.rate-form').forEach(form => {
      ['overall_star','usability_star','trust_star','communication_star'].forEach(function(name){
        const starsDiv = form.querySelector('.stars[data-name="'+name+'"]');
        if (!starsDiv) return;
        let val = 5;
        for(let i=1;i<=5;i++){
          const s = document.createElement('span');
          s.className = 'star' + (i<=val?' filled':'');
          s.textContent = '★';
          s.dataset.value = i;
          s.onclick = function(){
            val = i;
            starsDiv.parentNode.querySelector('input[name="'+name+'"]').value = val;
            starsDiv.querySelectorAll('.star').forEach((star,j) => {
              star.classList.toggle('filled', j<i);
            });
          };
          starsDiv.appendChild(s);
        }
      });
      // حفظ التقييم بدون إعادة تحميل
      form.onsubmit = function(e){
        e.preventDefault();
        const fd = new FormData(form);
        fd.append('ajax_rate',1);
        fetch('user_reviews.php',{method:'POST',body:fd}) // اسم ملف الحفظ هنا!
          .then(r=>r.json())
          .then(d=>{
            if(d.ok){
              form.querySelector('.success-msg').style.display='block';
              setTimeout(()=>window.location.reload(), 800);
            }else{
              alert(d.msg||'حدث خطأ');
            }
          });
      };
    });
    // إغلاق المودال عند الضغط بالخلفية
    document.querySelectorAll('.modal').forEach(function(modal){
      modal.addEventListener('click',function(e){
        if(e.target === modal){ modal.style.display='none'; }
      });
    });
    </script>
</body>
</html>
