<?php include('./includes/head.php') ?>

<body>
  <main>
    <section class="login-section">
      <div class="login-header">
        <h2>Login</h2>
      </div>
      <form id="login-form">
        <input type="hidden" name="e" value="L">
        <label>Email:</label>
        <input type="email" name="ds_usuario_email" id="ds_usuario_email" />
        <label>Senha:</label>
        <input type="password" name="ds_usuario_senha" id="ds_usuario_senha" />
        <button>Entrar</button>
      </form>
    </section>
  </main>
</body>

</html>