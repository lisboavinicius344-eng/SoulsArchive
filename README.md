# SoulsArchive

# ⚔️ SoulArchive Wiki — Enciclopédia Digital Dark Souls

> **"May the Flames Guide Thee"** 🔥

O **SoulArchive Wiki** é uma plataforma enciclopédica conceitual dedicada ao universo do jogo *Dark Souls*. Desenvolvido como projeto prático para a disciplina de **Programação Web**, o site centraliza informações sobre a complexa lore, chefes lendários, mapas estruturais e builds, envelopado em uma interface de estética *Premium Dark Fantasy*.

---

## 👨‍💻 Equipe de Desenvolvimento & Contexto Acadêmico

* **Discentes/Desenvolvedores:** * **Guilherme Silva Teixeira** — *Designer Front-End (UI/UX e Estilização CSS3)*
  * **Vinícius Marcelo Lisboa** — *Programador Back-End (Estrutura Lógica e Semântica)*
* **Componente Curricular:** Programação para Web
* **Ano de Entrega:** 2026
* **Abordagem Técnica:** Projeto focado em semântica estrutural e design imersivo puramente nativo.

---

## 🛠️ Tecnologias Utilizadas

Em total conformidade com as diretrizes acadêmicas de desenvolvimento limpo, o projeto foi construído utilizando **tecnologias puras (Vanilla)**, sem o uso de frameworks pesados ou bibliotecas que mascarem o aprendizado da sintaxe:

* **HTML5 Avançado:** Estruturação semântica rigorosa para indexação e acessibilidade (tags `header`, `main`, `section`, `article`, `footer`).
* **CSS3 Premium:** * Layouts robustos e responsivos construídos com **CSS Grid Layout** e **Flexbox**.
  * Gerenciamento de design tokenizado através de **Variáveis CSS (`:root`)** para cores (tons de cinza profundo, preto e dourado-brasa) e tipografia.
  * Efeitos imersivos de transição (`transition`) e animações dinâmicas de interface.
* **Google Fonts:** Integração das fontes *Cinzel* (para títulos heráldicos e medievais) e *Inter* (para máxima legibilidade do corpo do texto).
* **Font Awesome:** Ícones vetoriais customizados para a temática de RPG.

---

## 📂 Arquitetura e Estrutura de Páginas

```text
├── index.php, bosses.php, builds.php, lore.php, maps.php # Páginas públicas alimentadas pelo banco
├── contatos.html                        # Página pública estática
├── index.html, bosses.html, builds.html, lore.html, maps.html # Redirecionam para as versões .php
├── login.php, logout.php                # Entrada e saída do painel
├── admin/                               # Painel: início, bosses/, builds/, lore_topicos/, lore_artigos/, regioes/ e usuarios/
├── config/database.php                  # Conexão PDO centralizada (conectarBanco)
├── includes/                            # auth.php, funcoes.php e layouts
├── database/                            # soulsarchive.sql e criar_admin.php
├── css/                                 # style.css e admin.css
└── js/admin.js                          # Confirmação de exclusão
```

---

## 🚀 Como Executar (XAMPP)

Requisitos: PHP 8.1 ou superior, com as extensões `pdo_mysql` e `mbstring`, e MySQL ou MariaDB.

1. Copie a pasta do projeto para `C:\xampp\htdocs\SoulsArchive` e inicie o Apache e o MySQL.
2. Importe `database/soulsarchive.sql` pelo phpMyAdmin. O arquivo cria o banco `soulsarchive` com o conteúdo inicial.
3. Crie o primeiro administrador pelo terminal:
   ```text
   cd C:\xampp\htdocs\SoulsArchive
   C:\xampp\php\php.exe database\criar_admin.php
   ```
4. Acesse `http://localhost/SoulsArchive/` e entre no painel pelo link **Painel**.

A conexão usa por padrão `127.0.0.1:3306`, usuário `root` e senha vazia. Em outro ambiente, defina as variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS`.

As imagens de chefes e builds são enviadas pelo próprio formulário do painel (JPG, PNG, WEBP ou GIF, até 5 MB) e ficam em `img/uploads/`. O formulário também aceita uma URL `http(s)` ou um caminho dentro de `img/`.
