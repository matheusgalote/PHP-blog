<?php
include('./myadmin/src/Azura.inc.php');
include('./myadmin/src/Usuario.inc.php');

$zura = new Azura();
$usuario = new Usuario();
$usuariodao = new UsuarioDAO();

$usuario = $zura->populateClass('usuario', $_REQUEST);

$evento = $_REQUEST['e'];

switch ($evento) {
    case 'I':
        $usuario->setCd_usuario($usuariodao->nextId('usuario'));
        $usuariodao->insert($usuario);
        break;
    case 'R':
        $usuario->setCd_usuario($_REQUEST['cd']);
        $usuario = $usuariodao->load($usuario);
        $evento = 'U';
        break;
    case 'U':
        $usuario->setCd_usuario($_REQUEST['cd_usuario']);
        $usuariodao->update($usuario);
        break;
    case 'D':
        $usuario->setCd_usuario($_REQUEST['cd']);
        $usuariodao->delete($usuario);
        break;
}

?>

<?php include('./includes/head.php'); ?>

<body class="container">
    <h1 class="title">The Blog</h1>
    <form action="">
        <input type="hidden" name="e" value="<?= $evento ? $evento : 'I' ?>">
        <input type="hidden" name="cd_usuario" value="<?= $usuario->getCd_usuario() ?>">
        <div class="ui input">
            <input type="text" name="ds_usuario_nome" value="<?= $usuario->getDs_usuario_nome() ?>" placeholder="Nome">
        </div>
        <div class="ui input">
            <input type="text" name="ds_usuario_email" value="<?= $usuario->getDs_usuario_email() ?>" placeholder="Email">
        </div>
        <div class="ui input">
            <input type="password" name="ds_usuario_senha" value="<?= $usuario->getDs_usuario_senha() ?>" placeholder="Password">
        </div>
        <button class="ui blue button">Enviar</button>
    </form>
    <?= $usuariodao->list($usuario);
    ?>
</body>

</html>