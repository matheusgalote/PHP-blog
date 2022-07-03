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
        $usuariodao->insert($usuario);
        break;
    case 'V':
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
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
</head>

<body>
    <h1>The Blog</h1>
    <form action="">
        <input type="hidden" name="e" value="<?= $evento ? $evento : 'I' ?>">
        <input type="hidden" name="cd_usuario" value="<?= $usuario->getCd_usuario() ?>">
        <label for="nome">NOME</label>
        <input type="text" name="ds_usuario_nome" value="<?= $usuario->getDs_usuario_nome() ?>">
        <label for="idade">EMAIL</label>
        <input type="text" name="ds_usuario_email" value="<?= $usuario->getDs_usuario_email() ?>">
        <button type="submit">Enviar</button>
    </form>
    <?= $usuariodao->list($usuario);
 ?>
</body>

</html>