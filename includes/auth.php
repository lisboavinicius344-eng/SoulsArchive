<?php

// Sessão, autenticação, autorização, CSRF e mensagens das páginas protegidas.

require_once __DIR__ . '/funcoes.php';

header('Cache-Control: no-store');

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_name('soulsarchive_sessao');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => url(),
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Relê a conta a cada requisição, para que a desativação e a troca de perfil valham imediatamente.
function usuarioAtual(): ?array
{
    static $carregado = false;
    static $usuario = null;

    if (!$carregado) {
        $carregado = true;

        if (isset($_SESSION['usuario_id'])) {
            $consulta = conectarBanco()->prepare(
                'SELECT id, nome, nome_usuario, perfil FROM usuarios WHERE id = ? AND ativo = 1'
            );
            $consulta->execute([$_SESSION['usuario_id']]);
            $usuario = $consulta->fetch() ?: null;

            if ($usuario === null) {
                unset($_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_perfil']);
            } else {
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];
            }
        }
    }

    return $usuario;
}

function exigirLogin(): array
{
    $usuario = usuarioAtual();

    if ($usuario === null) {
        definirMensagem('erro', 'Faça login para acessar o painel.');
        redirecionar('login.php');
    }

    return $usuario;
}

function exigirPerfil(string ...$perfis): array
{
    $usuario = exigirLogin();

    if (!in_array($usuario['perfil'], $perfis, true)) {
        paginaErro(403, 'Seu perfil não tem permissão para acessar esta área.');
    }

    return $usuario;
}

function exigirPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        paginaErro(405, 'Esta operação só aceita envio por formulário.');
    }
}

// Retorna a conta quando as credenciais são válidas. O hash é calculado mesmo para
// contas inexistentes, para que o tempo de resposta não revele quais contas existem.
function autenticar(string $identificacao, string $senha): ?array
{
    $pdo = conectarBanco();
    $consulta = $pdo->prepare(
        'SELECT id, nome, perfil, ativo, senha_hash FROM usuarios WHERE nome_usuario = ? OR email = ? LIMIT 1'
    );
    $consulta->execute([$identificacao, $identificacao]);
    $conta = $consulta->fetch();

    if ($conta === false) {
        password_hash($senha, PASSWORD_DEFAULT);
        return null;
    }

    if (!password_verify($senha, $conta['senha_hash']) || (int) $conta['ativo'] !== 1) {
        return null;
    }

    // O ON UPDATE de atualizado_em é anulado, pois um login não altera os dados da conta.
    $novoHash = password_needs_rehash($conta['senha_hash'], PASSWORD_DEFAULT)
        ? password_hash($senha, PASSWORD_DEFAULT)
        : $conta['senha_hash'];
    $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW(), senha_hash = ?, atualizado_em = atualizado_em WHERE id = ?')
        ->execute([$novoHash, $conta['id']]);

    return $conta;
}

function iniciarSessaoUsuario(array $conta): void
{
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int) $conta['id'];
    $_SESSION['usuario_nome'] = $conta['nome'];
    $_SESSION['usuario_perfil'] = $conta['perfil'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function encerrarSessaoUsuario(): void
{
    $_SESSION = [];

    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookie['path'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'],
    ]);

    session_destroy();
}

function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(tokenCsrf()) . '">';
}

function csrfValido(): bool
{
    $enviado = $_POST['csrf_token'] ?? null;

    return is_string($enviado)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $enviado);
}

function exigirCsrf(): void
{
    // Acima de post_max_size o PHP descarta todo o formulário, inclusive o token.
    if ($_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        paginaErro(413, 'O arquivo enviado excede o limite. Envie uma imagem de até 5 MB.');
    }

    if (!csrfValido()) {
        paginaErro(403, 'A verificação de segurança do formulário falhou. Recarregue a página e tente novamente.');
    }
}

function definirMensagem(string $tipo, string $texto): void
{
    $_SESSION['mensagens'][] = ['tipo' => $tipo, 'texto' => $texto];
}

function exibirMensagens(): void
{
    foreach ($_SESSION['mensagens'] ?? [] as $mensagem) {
        echo '<p class="alerta alerta-' . e($mensagem['tipo']) . '" role="status">' . e($mensagem['texto']) . '</p>';
    }

    unset($_SESSION['mensagens']);
}

function exibirErros(array $erros): void
{
    if ($erros === []) {
        return;
    }

    echo '<div class="alerta alerta-erro" role="alert"><ul>';
    foreach ($erros as $erro) {
        echo '<li>' . e($erro) . '</li>';
    }
    echo '</ul></div>';
}
