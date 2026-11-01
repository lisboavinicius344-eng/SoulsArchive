<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador');
exigirPost();
exigirCsrf();

$id = idValido($_POST['id'] ?? null);

// Quem exclui é sempre um administrador ativo, então impedir a exclusão da
// própria conta garante que o sistema nunca fique sem administradores.
if ($id === (int) $usuario['id']) {
    definirMensagem('erro', 'Você não pode excluir a própria conta.');
    redirecionar('admin/usuarios/index.php');
}

// A chave estrangeira mantém as builds da conta, com criado_por nulo.
$exclusao = conectarBanco()->prepare('DELETE FROM usuarios WHERE id = ?');
$exclusao->execute([$id ?? 0]);

if ($exclusao->rowCount() === 1) {
    definirMensagem('sucesso', 'Usuário excluído com sucesso.');
} else {
    definirMensagem('erro', 'O usuário solicitado não foi encontrado.');
}

redirecionar('admin/usuarios/index.php');
