<?php
include('./myadmin/src/Azura.inc.php');
include('./myadmin/src/Usuario.inc.php');

$zura = new Azura();
$usuario = new Usuario();
$usuariodao = new UsuarioDAO();

$usuario = $zura->populateClass('usuario', $_REQUEST);

switch($_REQUEST['e']) {
    case 'I':
        $usuariodao->insert($usuario);
    
    case 'U':
        $usuariodao->update($usuario);
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
        <input type="hidden" name="e" value="I">
        <label for="nome">NOME</label>
        <input type="text" name="ds_usuario_nome">
        <label for="idade">EMAIL</label>
        <input type="text" name="ds_usuario_email">
        <button type="submit">Enviar</button>
    </form>
</body>
</html>