-- SoulsArchive: estrutura do banco e conteúdo inicial.
-- O administrador inicial é criado por database/criar_admin.php, para que
-- nenhuma senha padrão fique registrada neste arquivo.

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS soulsarchive
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE soulsarchive;

CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(120) NOT NULL,
  nome_usuario  VARCHAR(50)  NOT NULL,
  email         VARCHAR(190) NOT NULL,
  senha_hash    VARCHAR(255) NOT NULL,
  perfil        ENUM('administrador', 'editor') NOT NULL DEFAULT 'editor',
  ativo         TINYINT(1)   NOT NULL DEFAULT 1,
  criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  ultimo_acesso DATETIME     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_nome_usuario (nome_usuario),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS builds (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(150) NOT NULL,
  slug          VARCHAR(180) NOT NULL,
  tipo          ENUM('Força', 'Destreza', 'Fé', 'Feitiçaria', 'Qualidade', 'Sangramento', 'Piromancia', 'Híbrida', 'Outra') NOT NULL,
  descricao     TEXT         NOT NULL,
  atributos     TEXT         NULL,
  armas         TEXT         NULL,
  equipamentos  TEXT         NULL,
  imagem        VARCHAR(255) NULL,
  criado_por    INT UNSIGNED NULL,
  criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_builds_slug (slug),
  KEY idx_builds_tipo (tipo),
  CONSTRAINT fk_builds_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id)
    ON UPDATE CASCADE
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bosses (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(150) NOT NULL,
  descricao     TEXT         NOT NULL,
  dificuldade   VARCHAR(60)  NOT NULL,
  imagem        VARCHAR(255) NULL,
  destaque      TINYINT(1)   NOT NULL DEFAULT 0,
  criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_bosses_nome (nome),
  KEY idx_bosses_destaque (destaque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conteúdo migrado das páginas estáticas. Os ids explícitos com INSERT IGNORE
-- permitem reimportar o arquivo sem duplicar nem sobrescrever registros.

INSERT IGNORE INTO bosses (id, nome, descricao, dificuldade, imagem, destaque) VALUES
  (1, 'Artorias', 'O lendário Andarilho do Abismo, consumido pela corrupção que tentou impedir.', 'Dificuldade Alta', 'img/boss-artorias.jpg', 1),
  (2, 'Ornstein & Smough', 'A dupla lendária de Anor Londo, símbolo do desafio soulslike.', 'Muito Difícil', 'img/boss-ornstein-smough.jpg', 1),
  (3, 'Manus', 'Uma entidade primordial tomada pelo descontrole da humanidade.', 'Extremo (Easy para Vinícius)', 'img/boss-manus.jpg', 1),
  (4, 'Gwyn', 'O Senhor da Luz Solar, reduzido a uma sombra do que um dia foi.', 'Alta', 'img/boss-gwyn.jpg', 1),
  (5, 'Nameless King', 'Um antigo deus guerreiro, esquecido pela própria história.', 'Insano', 'img/boss-nameless-king.jpg', 0),
  (6, 'Great Grey Wolf Sif', 'O fiel guardião do túmulo de Artorias e um dos confrontos mais tristes do jogo.', 'Média', 'img/boss-sif.jpg', 1);

INSERT IGNORE INTO builds (id, nome, slug, tipo, descricao, armas, imagem) VALUES
  (1, 'Strength Build', 'strength-build', 'Força', 'Alto dano físico, armas pesadas e grande capacidade de stagger.', 'Zweihander, Great Club, Dragon Tooth e Black Knight Greataxe.', 'img/build_strengh.jpg'),
  (2, 'Dexterity Build', 'dexterity-build', 'Destreza', 'Velocidade, esquivas rápidas e ataques consecutivos.', 'Uchigatana, Balder Side Sword, Chaos Blade e Gold Tracer.', 'img/build_dex.jpg'),
  (3, 'Faith Build', 'faith-build', 'Fé', 'Milagres, buffs sagrados e regeneração.', 'Sunlight Spear, Wrath of the Gods, Great Heal e Lightning Blade.', 'img/build_faith.jpg'),
  (4, 'Sorcery Build', 'sorcery-build', 'Feitiçaria', 'Alto dano mágico e combate à distância.', 'Crystal Soul Spear, Homing Soulmass, Dark Bead e White Dragon Breath.', 'img/build_sorcery.jpg'),
  (5, 'Quality Build', 'quality-build', 'Qualidade', 'Equilíbrio entre força e destreza, permitindo grande variedade de armas.', 'Claymore, Halberd, Longsword e Black Knight Sword.', 'img/build_quality.jpg');
