<html>
<head>
  <meta charset="UTF-8">
  <title>تسجيل رائد أعمال</title>
  <style>
    body {
      font-family: sans-serif;
      background-color: #f0f8f5;
      padding: 40px;
      direction: rtl;
    }
    h2 {
      text-align: center;
      color: #035917;
    }
    label {
      display: block;
      margin-top: 15px;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      background-color: #035917;
      color: white;
      border: none;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <h2>إنشاء حساب رائد أعمال</h2>

    <form action="entrepreneur_register.php" method="POST">
    <label>الاسم الأول</label>
    <input type="text" name="first_name" required>

    <label>الاسم الثاني</label>
    <input type="text" name="last_name" required>

    <label>رقم الهوية</label>
    <input type="text" name="national_id" pattern="[0-9]{10,}" inputmode="numeric" required placeholder="مثال: 1023456789">

    <label>رقم الجوال</label>
    <input type="text" name="phone" pattern="05[0-9]{8}" inputmode="numeric" required placeholder="05XXXXXXXX">

    <label>البريد الإلكتروني</label>
    <input type="email" name="email" required>

    <label>كلمة المرور</label>
    <input type="password" name="password" required>

    <label>تأكيد كلمة المرور</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">تسجيل</button>
  </form>

</body>
</html>
