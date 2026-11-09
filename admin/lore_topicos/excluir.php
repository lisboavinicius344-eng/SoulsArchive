<?php

require_once __DIR__ . '/../../includes/auth.php';

exigirPerfil('administrador', 'editor');
exigirPost();
exigirCsrf();

$id = idValido($_POST['id'] ?? null);
$exclusao = conectarBanco()->prepare('DELETE FROM lore_topicos WHERE id = ?');

try {
    $exclusao->execute([$id ?? 0]);

    if ($exclusao->rowCount() === 1) {
        definirMensagem('sucesso', 'Tópico excluído com sucesso.');
    } else {
        definirMensagem('erro', 'O tópico solicitado não foi encontrado.');
    }
} catch (PDOException $erro) {
    if ($erro->getCode() !== SQLSTATE_RESTRICAO) {
        throw $erro;
    }

    definirMensagem('erro', 'Este tópico não pode ser excluído porque possui artigos. Exclua ou mova os artigos primeiro.');
}

redirecionar('admin/lore_topicos/index.php');
