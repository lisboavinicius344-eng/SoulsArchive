<?php

require_once __DIR__ . '/../../includes/auth.php';

exigirPerfil('administrador', 'editor');
exigirPost();
exigirCsrf();

$id = idValido($_POST['id'] ?? null);
$pdo = conectarBanco();

$consulta = $pdo->prepare('SELECT imagem FROM bosses WHERE id = ?');
$consulta->execute([$id ?? 0]);
$imagem = $consulta->fetchColumn();

$exclusao = $pdo->prepare('DELETE FROM bosses WHERE id = ?');

try {
    $exclusao->execute([$id ?? 0]);

    if ($exclusao->rowCount() === 1) {
        removerImagemEnviada($pdo, $imagem ?: null);
        definirMensagem('sucesso', 'Chefe excluído com sucesso.');
    } else {
        definirMensagem('erro', 'O chefe solicitado não foi encontrado.');
    }
} catch (PDOException $erro) {
    if ($erro->getCode() !== SQLSTATE_RESTRICAO) {
        throw $erro;
    }

    definirMensagem('erro', 'Este chefe não pode ser excluído porque há registros relacionados a ele.');
}

redirecionar('admin/bosses/index.php');
