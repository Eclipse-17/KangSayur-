<?php
// Public entry (PHP) — previously index.html
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login KangSayur</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <div class="logo-icons">🥔 🌽 🍅 🥕 🥬</div>
      <h1 class="brand">Kang<span>Sayur</span></h1>
    </div>
  </header>

  <main class="center-wrap">
    <section class="login-card">
      <div class="login-card-body">
        <h2>Selamat Datang 👋</h2>
        <p class="subtitle">Pilih peran dan masukkan kredensial Anda</p>

        <div class="role-tabs">
          <button class="role-tab active" data-role="admin" type="button">Admin</button>
          <button class="role-tab" data-role="petugas_stok" type="button">Petugas Stok</button>
          <button class="role-tab" data-role="kasir" type="button">Kasir</button>
        </div>

        <form action="#" method="post" class="login-form">
          <input type="hidden" id="role" name="role" value="admin">
          <div class="form-group">
            <label for="username">USERNAME</label>
            <input type="text" id="username" name="username" value="Admin" />
          </div>

          <div class="form-group">
            <label for="password">PASSWORD</label>
            <input type="password" id="password" name="password" value="" />
          </div>

          <button type="submit" class="submit-btn">Masuk —</button>
        </form>
      </div>
    </section>
  </main>

  <script>
    // Role tab interactions: toggle active and set username value accordingly
    const roleTabs = document.querySelectorAll('.role-tab');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password'); // Tambahkan ini
    const roleInput = document.getElementById('role');
    roleTabs.forEach(tab => {
      tab.addEventListener('click', () => {
        roleTabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        const role = tab.dataset.role;
        roleInput.value = role;
        if (role === 'admin') { usernameInput.value = 'admin'; passwordInput.value = 'admin123'; }
        else if (role === 'petugas_stok') { usernameInput.value = 'budi_petugas'; passwordInput.value = 'budi123'; }
        else if (role === 'kasir') { usernameInput.value = 'eko_kasir'; passwordInput.value = 'eko123'; }
      });
    });

    // Login handler: send credentials to PHP endpoint and handle response
    const loginForm = document.querySelector('.login-form');
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);
      formData.append('ajax', '1');

      try {
        const res = await fetch('../php/login.php', { 
          method: 'POST', 
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const json = await res.json();
        if (json.success) {
          const role = json.role || roleInput.value;
          if (role === 'admin') window.location.href = 'admin.php';
          else if (role === 'petugas_stok') window.location.href = 'petugas.php';
          else if (role === 'kasir') window.location.href = 'kasir.php';
          else window.location.href = 'admin.php';
        } else {
          alert(json.message || 'Login gagal');
        }
      } catch (err) {
        alert('Terjadi kesalahan koneksi.');
      }
    });
  </script>
</body>
</html>
