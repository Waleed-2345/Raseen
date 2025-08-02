<html >
<head>
  <title>تسجيل مستثمر</title>
  <style>
    body {
      font-family: sans-serif;
      background-color: #f0f8f5;
      padding: 40px;
      direction: rtl;
    }
    h2 {
      color: #035917;
      text-align: center;
      margin-bottom: 30px;
    }
    form {
      max-width: 700px;
      margin: auto;
    }
    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 15px;
    }
    .checkbox-group, .radio-group {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 10px;
    }
    .checkbox-group label,
    .radio-group label {
      display: flex;
      align-items: center;
      font-weight: normal;
    }
    .checkbox-group input[type="checkbox"],
    .radio-group input[type="radio"] {
      margin-left: 5px;
    }
    button {
      margin-top: 25px;
      width: 100%;
      padding: 12px;
      background-color: #035917;
      color: white;
      border: none;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #028e45;
    }
  </style>
</head>
<body>

  <h2>تسجيل مستثمر جديد</h2>

  <form action="investor_register.php" method="POST">
    <label>الاسم الأول</label>
    <input type="text" name="first_name" required>

    <label>الاسم الثاني</label>
    <input type="text" name="last_name" required>

    <label>رقم الهوية</label>
    <input type="text" name="national_id" pattern="[0-9]{10,}" inputmode="numeric" required>

    <label>البريد الإلكتروني</label>
    <input type="email" name="email" required>

    <label>رقم الجوال</label>
    <input type="text" name="phone" pattern="05[0-9]{8}" inputmode="numeric" required placeholder="05XXXXXXXX">


    <label>المنطقة الجغرافية (اختر واحدة أو أكثر)</label>
    <div class="checkbox-group">
      <label><input type="checkbox" name="region[]" value="الشرقية"> الشرقية</label>
      <label><input type="checkbox" name="region[]" value="الوسطى"> الوسطى</label>
      <label><input type="checkbox" name="region[]" value="الغربية"> الغربية</label>
      <label><input type="checkbox" name="region[]" value="الجنوبية"> الجنوبية</label>
    </div>
	
	<label>ما المجالات التي تهمك للاستثمار؟</label>
<div class="checkbox-group" id="mainInterests">
  <label><input type="checkbox" value="tourism" onchange="toggleSub(this)"> السياحة والضيافة</label>
  <label><input type="checkbox" value="retail" onchange="toggleSub(this)"> التجزئة والمطاعم</label>
  <label><input type="checkbox" value="realestate" onchange="toggleSub(this)"> العقار والإسكان</label>
  <label><input type="checkbox" value="health" onchange="toggleSub(this)"> الصحة والعلاج</label>
  <label><input type="checkbox" value="education" onchange="toggleSub(this)"> التعليم والتدريب</label>
  <label><input type="checkbox" value="logistics" onchange="toggleSub(this)"> الصناعات والخدمات اللوجستية</label>
  <label><input type="checkbox" value="tech" onchange="toggleSub(this)"> التقنية والابتكار</label>
  <label><input type="checkbox" value="other" onchange="toggleSub(this)"> مجالات متنوعة</label>
</div>

<!-- حاوية التفاصيل -->
<div id="subInterestsContainer" style="margin-top: 20px;"></div>

<script>
  const subInterests = {
    tourism: ['فنادق', 'منتجعات', 'نُزل', 'استراحات', 'مخيمات', 'فلل فندقية'],
    retail: ['مطاعم راقية', 'مطاعم شعبية', 'مقاهي', 'أكشاك', 'متاجر ذكية'],
    realestate: ['شقق', 'وحدات مفروشة', 'عمائر', 'أراضي'],
    health: ['مراكز تأهيل', 'مراكز طبية', 'حضانات', 'دور كبار السن'],
    education: ['حضانات', 'مراكز تدريب', 'تعليم تقني'],
    logistics: ['مستودعات', 'نقل', 'شحن', 'مصانع صغيرة'],
    tech: ['تطبيقات', 'ذكاء اصطناعي', 'أنظمة ذكية'],
    other: ['أفكار ريادية', 'مشاريع بيئية', 'مشاريع اجتماعية']
  };

  function toggleSub(checkbox) {
    const container = document.getElementById("subInterestsContainer");
    const value = checkbox.value;
    const exists = document.getElementById("sub_" + value);

    if (checkbox.checked) {
      if (!exists) {
        const section = document.createElement("div");
        section.id = "sub_" + value;
        section.style.marginTop = "10px";

        const title = document.createElement("label");
        title.innerHTML = `<strong>اختر التفاصيل لـ: ${checkbox.parentElement.innerText}</strong>`;
        section.appendChild(title);

        const group = document.createElement("div");
        group.classList.add("checkbox-group");

        subInterests[value].forEach(item => {
          const label = document.createElement("label");
          label.innerHTML = `<input type="checkbox" name="sub_interests[]" value="${item}"> ${item}`;
          group.appendChild(label);
        });

        section.appendChild(group);
        container.appendChild(section);
      }
    } else {
      if (exists) {
        container.removeChild(exists);
      }
    }
  }
</script>


    <label>نوع الاستثمار المفضل:</label>
    <div class="radio-group">
      <label><input type="radio" name="investment_type" value="استثمار جزئي"> الاستثمار بجزء معين من المشروع</label>
      <label><input type="radio" name="investment_type" value="قرض"> تقديم قرض للمشروع</label>
      <label><input type="radio" name="investment_type" value="شراء فكرة"> شراء فكرة جاهزة</label>
      <label><input type="radio" name="investment_type" value="استثمار في فكرة"> الاستثمار في فكرة جديدة</label>
    </div>

    <label>كلمة المرور</label>
    <input type="password" name="password" required>

    <label>تأكيد كلمة المرور</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">تسجيل</button>
  </form>

</body>
</html>
