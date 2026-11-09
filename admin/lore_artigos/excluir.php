<?php

require_once __DIR__ . '/../../includes/auth.php';

exigirPerfil('administrador', 'editor');
exigirPost();
exigirCsrf();

$id = idValido($_POST['id'] ?? null);
$pdo = conectarBanco();

$consulta = $pdo->prepare('SELECT imagem FROM lore_artigos WHERE id = ?');
$consulta->execute([$id ?? 0]);
$imagem = $consulta->fetchColumn();

$exclusao = $pdo->prepare('DELETE FROM lore_artigos WHERE id = ?');
$exclusao->execute([$id ?? 0]);

if ($exclusao->rowCount() === 1) {
    removerImagemEnviada($pdo, $imagem ?: null);
    definirMensagem('sucesso', 'Artigo excluído com sucesso.');
} else {
    definirMensagem('erro', 'O artigo solicitado não foi encontrado.');
}

redirecionar('admin/lore_artigos/index.php');
