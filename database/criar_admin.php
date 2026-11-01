<?php

// Cria uma conta de administrador pelo terminal: php database/criar_admin.php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/funcoes.php';

function perguntar(string $rotulo): string
{
    echo $rotulo . ': ';

    return trim((string) fgets(STDIN));
}

$nome = perguntar('Nome');
$nomeUsuario = perguntar('Nome de usuário');
$email = perguntar('E-mail');
$senha = perguntar('Senha (mínimo de 8 caracteres)');

$erros = validarUsuario($nome, $nomeUsuario, $email, $senha);

if ($erros !== []) {
    fwrite(STDERR, implode(PHP_EOL, $erros) . PHP_EOL);
    exit(1);
}

try {
    conectarBanco()
        ->prepare("INSERT INTO usuarios (nome, nome_usuario, email, senha_hash, perfil) VALUES (?, ?, ?, ?, 'administrador')")
        ->execute([$nome, $nomeUsuario, $email, password_hash($senha, PASSWORD_DEFAULT)]);
} catch (PDOException $erro) {
    $mensagem = $erro->getCode() === SQLSTATE_RESTRICAO
        ? 'Nome de usuário ou e-mail já cadastrado.'
        : 'Não foi possível acessar o banco. Verifique se o MySQL está ativo e se database/soulsarchive.sql foi importado.';
    fwrite(STDERR, $mensagem . PHP_EOL);
    exit(1);
}

echo 'Administrador criado com sucesso.' . PHP_EOL;
