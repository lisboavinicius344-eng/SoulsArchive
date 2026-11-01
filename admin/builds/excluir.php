<?php

require_once __DIR__ . '/../../includes/auth.php';

exigirPerfil('administrador', 'editor');
exigirPost();
exigirCsrf();

$id = idValido($_POST['id'] ?? null);
$pdo = conectarBanco();

$consulta = $pdo->prepare('SELECT imagem FROM builds WHERE id = ?');
$consulta->execute([$id ?? 0]);
$imagem = $consulta->fetchColumn();

$exclusao = $pdo->prepare('DELETE FROM builds WHERE id = ?');

try {
    $exclusao->execute([$id ?? 0]);

    if ($exclusao->rowCount() === 1) {
        removerImagemEnviada($pdo, $imagem ?: null);
        definirMensagem('sucesso', 'Build excluída com sucesso.');
    } else {
        definirMensagem('erro', 'A build solicitada não foi encontrada.');
    }
} catch (PDOException $erro) {
    if ($erro->getCode() !== SQLSTATE_RESTRICAO) {
        throw $erro;
    }

    definirMensagem('erro', 'Esta build não pode ser excluída porque há registros relacionados a ela.');
}

redirecionar('admin/builds/index.php');
