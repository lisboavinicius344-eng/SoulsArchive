<?php

// Funções compartilhadas pelas páginas públicas e pelo painel.

require_once __DIR__ . '/../config/database.php';

const TIPOS_BUILD = ['Força', 'Destreza', 'Fé', 'Feitiçaria', 'Qualidade', 'Sangramento', 'Piromancia', 'Híbrida', 'Outra'];
const PERFIS_USUARIO = ['administrador' => 'Administrador', 'editor' => 'Editor'];
const SQLSTATE_RESTRICAO = '23000';
const PASTA_UPLOADS = 'img/uploads/';
const TAMANHO_MAXIMO_IMAGEM = 5 * 1024 * 1024;
const EXTENSOES_IMAGEM = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];

ini_set('display_errors', '0');

// Nenhum detalhe técnico chega ao navegador, e o log registra apenas o tipo e o
// local da falha, pois a mensagem de um PDOException pode conter trechos de SQL.
set_exception_handler(function (Throwable $erro): void {
    error_log(sprintf(
        'SoulsArchive: %s (código %s) em %s:%d',
        get_class($erro),
        $erro->getCode(),
        basename($erro->getFile()),
        $erro->getLine()
    ));

    paginaErro(500, 'Não foi possível concluir a operação. Tente novamente mais tarde.');
});

if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
}

function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function textoHtml(?string $texto): string
{
    return nl2br(e($texto));
}

// Ícones do painel, desenhados inline para dispensar bibliotecas externas.
function icone(string $nome): string
{
    $desenhos = [
        'painel' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'chefes' => '<polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5"/><line x1="13" y1="19" x2="19" y2="13"/><line x1="16" y1="16" x2="20" y2="20"/><line x1="19" y1="21" x2="21" y2="19"/><polyline points="14.5 6.5 18 3 21 3 21 6 17.5 9.5"/><line x1="5" y1="14" x2="9" y2="18"/><line x1="7" y1="17" x2="4" y2="20"/><line x1="3" y1="19" x2="5" y2="21"/>',
        'builds' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'usuarios' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'site' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'voltar' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'sair' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'mais' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'editar' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
        'excluir' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
        'busca' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'estrela' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'livro' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'mapa' => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>',
        'pasta' => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>',
        'chama' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
    ];

    return '<svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $desenhos[$nome]
        . '</svg>';
}

// Monta caminhos a partir da raiz do projeto, que pode estar em uma subpasta do servidor.
function url(string $caminho = ''): string
{
    static $base = null;

    if ($base === null) {
        $raiz = str_replace('\\', '/', dirname(__DIR__));
        $pastaScript = str_replace('\\', '/', dirname((string) realpath($_SERVER['SCRIPT_FILENAME'] ?? '')));
        $relativo = trim(substr($pastaScript, strlen($raiz)), '/');
        $niveis = $relativo === '' ? 0 : substr_count($relativo, '/') + 1;

        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        for ($i = 0; $i < $niveis; $i++) {
            $base = str_replace('\\', '/', dirname($base));
        }
        $base = rtrim($base, '/') . '/';
    }

    return $base . ltrim($caminho, '/');
}

function urlImagem(string $imagem): string
{
    return preg_match('#^https?://#i', $imagem) ? $imagem : url($imagem);
}

function redirecionar(string $caminho): void
{
    header('Location: ' . url($caminho));
    exit;
}

function paginaErro(int $status, string $mensagem): void
{
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $mensagem . PHP_EOL);
        exit(1);
    }

    if (!headers_sent()) {
        http_response_code($status);
    }

    $titulo = [
        403 => 'Acesso negado',
        404 => 'Conteúdo não encontrado',
        405 => 'Operação não permitida',
        413 => 'Arquivo muito grande',
    ][$status] ?? 'Erro interno';

    require __DIR__ . '/layout_erro.php';
    exit;
}

// Entradas do usuário: valores que não sejam texto UTF-8 válido são descartados.
function limparTexto($valor): string
{
    return is_string($valor) && mb_check_encoding($valor, 'UTF-8') ? trim($valor) : '';
}

function textoPost(string $campo): string
{
    return limparTexto($_POST[$campo] ?? '');
}

function textoGet(string $campo): string
{
    return limparTexto($_GET[$campo] ?? '');
}

function idValido($valor): ?int
{
    $id = is_string($valor) ? filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

    return $id === false ? null : $id;
}

// Aceita URLs http(s) ou caminhos locais dentro de img/, o que recusa javascript:, data: e "../".
function imagemValida(string $imagem): bool
{
    if (preg_match('#^img/(?!.*\.\.)[\w ()./-]+\.(jpe?g|png|webp|gif)$#i', $imagem)) {
        return true;
    }

    return filter_var($imagem, FILTER_VALIDATE_URL) !== false
        && preg_match('#^https?://#i', $imagem) === 1;
}

function validarImagem(string $imagem, array &$erros): void
{
    if ($imagem === '') {
        return;
    }

    if (mb_strlen($imagem) > 255 || !imagemValida($imagem)) {
        $erros[] = 'Informe uma URL de imagem http(s) ou um caminho dentro de img/ com extensão jpg, jpeg, png, webp ou gif.';
    }
}

// O tipo é identificado pelo conteúdo do arquivo; o nome e a extensão enviados são ignorados.
function validarArquivoImagem(string $campo, array &$erros): ?array
{
    $arquivo = $_FILES[$campo] ?? null;
    $situacao = $arquivo['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($situacao === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($situacao === UPLOAD_ERR_INI_SIZE || $situacao === UPLOAD_ERR_FORM_SIZE
        || ($situacao === UPLOAD_ERR_OK && $arquivo['size'] > TAMANHO_MAXIMO_IMAGEM)) {
        $erros[] = 'A imagem deve ter no máximo 5 MB.';
        return null;
    }

    if ($situacao !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
        $erros[] = 'Não foi possível receber a imagem. Tente enviá-la novamente.';
        return null;
    }

    $informacoes = @getimagesize($arquivo['tmp_name']);
    $extensao = $informacoes === false ? null : (EXTENSOES_IMAGEM[$informacoes[2]] ?? null);

    if ($extensao === null) {
        $erros[] = 'Envie uma imagem nos formatos JPG, PNG, WEBP ou GIF.';
        return null;
    }

    return ['origem' => $arquivo['tmp_name'], 'extensao' => $extensao];
}

// O arquivo enviado tem prioridade sobre a URL, que só é validada na ausência dele.
function lerCampoImagem(array &$erros): array
{
    $arquivo = validarArquivoImagem('arquivo_imagem', $erros);
    $imagem = isset($_POST['remover_imagem']) ? '' : textoPost('imagem');

    if ($arquivo === null) {
        validarImagem($imagem, $erros);
    }

    return [$imagem, $arquivo];
}

function salvarImagemEnviada(array $arquivo): string
{
    $pasta = dirname(__DIR__) . '/' . PASTA_UPLOADS;

    if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
        throw new RuntimeException('Pasta de imagens indisponível.');
    }

    $nome = bin2hex(random_bytes(16)) . '.' . $arquivo['extensao'];

    if (!move_uploaded_file($arquivo['origem'], $pasta . $nome)) {
        throw new RuntimeException('Falha ao gravar a imagem enviada.');
    }

    return PASTA_UPLOADS . $nome;
}

// Apaga um arquivo enviado pelo painel que não seja mais usado por nenhum registro.
function removerImagemEnviada(PDO $pdo, ?string $imagem): void
{
    if ($imagem === null || !preg_match('#^img/uploads/[0-9a-f]{32}\.(jpg|png|webp|gif)$#', $imagem)) {
        return;
    }

    $consulta = $pdo->prepare(
        'SELECT (SELECT COUNT(*) FROM bosses WHERE imagem = ?)
            + (SELECT COUNT(*) FROM builds WHERE imagem = ?)
            + (SELECT COUNT(*) FROM lore_artigos WHERE imagem = ?)
            + (SELECT COUNT(*) FROM regioes WHERE imagem = ?)'
    );
    $consulta->execute([$imagem, $imagem, $imagem, $imagem]);

    if ((int) $consulta->fetchColumn() === 0) {
        @unlink(dirname(__DIR__) . '/' . $imagem);
    }
}

// Campo vazio equivale a zero; números menores aparecem primeiro nas listagens.
function validarOrdem(string $ordem, array &$erros): int
{
    if ($ordem === '') {
        return 0;
    }

    $valor = filter_var($ordem, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);

    if ($valor === false) {
        $erros[] = 'A ordem de exibição deve ser um número inteiro de 0 a 9999.';
        return 0;
    }

    return $valor;
}

function validarUsuario(string $nome, string $nomeUsuario, string $email, ?string $senha): array
{
    $erros = [];

    if ($nome === '' || mb_strlen($nome) > 120) {
        $erros[] = 'Informe o nome, com até 120 caracteres.';
    }

    if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $nomeUsuario)) {
        $erros[] = 'O nome de usuário deve ter de 3 a 50 caracteres: letras sem acento, números, ponto, hífen ou sublinhado.';
    }

    if (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $erros[] = 'Informe um e-mail válido.';
    }

    // O algoritmo bcrypt considera somente os primeiros 72 bytes da senha.
    if ($senha !== null && (mb_strlen($senha) < 8 || strlen($senha) > 72)) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres e no máximo 72 bytes.';
    }

    return $erros;
}

function gerarSlug(string $nome): string
{
    $slug = strtr(mb_strtolower($nome, 'UTF-8'), [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
    ]);
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $slug), '-');

    return $slug === '' ? 'build' : substr($slug, 0, 170);
}

// Acrescenta um sufixo numérico até encontrar um slug livre, ignorando o próprio registro na edição.
function gerarSlugUnico(PDO $pdo, string $nome, ?int $ignorarId = null): string
{
    $base = gerarSlug($nome);
    $consulta = $pdo->prepare('SELECT COUNT(*) FROM builds WHERE slug = ? AND id <> ?');

    for ($sufixo = 1; ; $sufixo++) {
        $slug = $sufixo === 1 ? $base : $base . '-' . $sufixo;
        $consulta->execute([$slug, $ignorarId ?? 0]);

        if ((int) $consulta->fetchColumn() === 0) {
            return $slug;
        }
    }
}

function termoLike(string $termo): string
{
    return '%' . addcslashes($termo, '%_\\') . '%';
}

// $colunas, $origem e $ordem vêm sempre do código; os valores do usuário vão em $parametros.
function consultarPaginado(PDO $pdo, string $colunas, string $origem, array $parametros, string $ordem, int $porPagina): array
{
    $contagem = $pdo->prepare("SELECT COUNT(*) FROM $origem");
    $contagem->execute($parametros);
    $total = (int) $contagem->fetchColumn();

    $paginas = max(1, (int) ceil($total / $porPagina));
    $pagina = min(idValido($_GET['pagina'] ?? null) ?? 1, $paginas);

    $consulta = $pdo->prepare("SELECT $colunas FROM $origem ORDER BY $ordem LIMIT ? OFFSET ?");
    $posicao = 1;
    foreach ($parametros as $valor) {
        $consulta->bindValue($posicao++, $valor);
    }
    $consulta->bindValue($posicao++, $porPagina, PDO::PARAM_INT);
    $consulta->bindValue($posicao, ($pagina - 1) * $porPagina, PDO::PARAM_INT);
    $consulta->execute();

    return [
        'itens' => $consulta->fetchAll(),
        'total' => $total,
        'pagina' => $pagina,
        'paginas' => $paginas,
    ];
}

function exibirPaginacao(array $resultado, string $ancora = ''): void
{
    if ($resultado['paginas'] <= 1) {
        return;
    }

    $filtros = array_filter($_GET, 'is_string');
    $atual = $resultado['pagina'];
    $fragmento = $ancora === '' ? '' : '#' . $ancora;

    $link = function (int $pagina) use ($filtros, $fragmento): string {
        return e('?' . http_build_query(array_merge($filtros, ['pagina' => $pagina])) . $fragmento);
    };

    echo '<nav class="paginacao" aria-label="Paginação">';

    if ($atual > 1) {
        echo '<a href="' . $link($atual - 1) . '">Anterior</a>';
    }

    for ($pagina = max(1, $atual - 2); $pagina <= min($resultado['paginas'], $atual + 2); $pagina++) {
        echo $pagina === $atual
            ? '<span class="atual" aria-current="page">' . $pagina . '</span>'
            : '<a href="' . $link($pagina) . '">' . $pagina . '</a>';
    }

    if ($atual < $resultado['paginas']) {
        echo '<a href="' . $link($atual + 1) . '">Próxima</a>';
    }

    echo '</nav>';
}
