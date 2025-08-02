<html>
<head>
  <meta charset="UTF-8">
  <title>الرئيسية | رصين</title>
  <style>
    body {
      margin: 0;
      font-family: 'Tajawal', Arial, sans-serif;
      background: #fff url('27624781-8a53-4d8a-ab58-b04d090867ce.png') no-repeat center bottom;
      background-size: contain; /* جرب cover لو تبيها تغطي */
      direction: rtl;
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 24px 40px 10px 40px;
      background: #fff;
      border-bottom: 1px solid #eee;
      position: relative;
    }
    .logo-img {
      height: 85px; /* كبره حسب ما تبي */
      margin-left: 10px;
    }
    .nav {
      display: flex;
      align-items: center;
      gap: 32px;
    }
    .nav-link {
      text-decoration: none;
      color: #2c2c2c;
      font-size: 1.1em;
      margin-right: 20px;
      position: relative;
      cursor: pointer;
    }
    .search-bar {
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 8px 14px;
      width: 250px;
      outline: none;
      font-size: 1em;
      margin: 0 20px;
    }
    .dropdown {
      position: relative;
      display: inline-block;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      top: 36px;
      right: 0;
      min-width: 140px;
      background-color: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.09);
      z-index: 100;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #eee;
      pointer-events: auto;
    }
    .dropdown-content a {
      color: #185c37;
      padding: 12px 18px;
      text-decoration: none;
      display: block;
      font-size: 1em;
      transition: background 0.2s;
    }
    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }
    .dropdown:focus-within .dropdown-content,
    .dropdown:hover .dropdown-content {
      display: block;
    }
  </style>
</head>
<body>

  <div class="header">
    <img src="raseen.logo.svg" alt="شعار رصين" class="logo-img">
    <div class="nav">
      <a href="#" class="nav-link">الرئيسية</a>
      <div class="dropdown" tabindex="0">
        <span class="nav-link">المستخدم</span>
        <div class="dropdown-content">
          <a href="choose_account.php">إنشاء حساب</a>
          <a href="signin.php">تسجيل دخول</a>
        </div>
      </div>
      <input class="search-bar" type="text" placeholder="ابحث...">
    </div>
  </div>

  <!-- باقي الصفحة الرئيسية هنا -->

</body>
</html>
