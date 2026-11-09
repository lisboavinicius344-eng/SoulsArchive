<?php

require_once __DIR__ . '/../../includes/auth.php';

exigirPerfil('administrador', 'editor');
exigirPost();
exigirCsrf();

$id = idValido($_POST['id'] ?? null);
$pdo = conectarBanco();

$consulta = $pdo->prepare('SELECT imagem FROM regioes WHERE id = ?');
$consulta->execute([$id ?? 0]);
$imagem = $consulta->fetchColumn();

$exclusao = $pdo->prepare('DELETE FROM regioes WHERE id = ?');
$exclusao->execute([$id ?? 0]);

if ($exclusao->rowCount() === 1) {
    removerImagemEnviada($pdo, $imagem ?: null);
    definirMensagem('sucesso', 'Região excluída com sucesso.');
} else {
    definirMensagem('erro', 'A região solicitada não foi encontrada.');
}

redirecionar('admin/regioes/index.php');
