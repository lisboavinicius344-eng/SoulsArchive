<?php

require_once __DIR__ . '/includes/auth.php';

// Uma sessão já expirada não tem o que encerrar nem concede acesso.
if (usuarioAtual() === null) {
    redirecionar('login.php');
}

exigirPost();
exigirCsrf();

encerrarSessaoUsuario();

redirecionar('login.php?encerrada=1');
