-- ============================================
-- SCRIPT COMPLETO E ATUALIZADO - HUMANICARE
-- Base de Dados: humanicare
-- Versão: 4.0 (COM COMENTÁRIOS INCLUÍDOS)
-- Data: 2025
-- ============================================

-- ============================================
-- 1. CONFIGURAÇÕES INICIAIS
-- ============================================
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- 2. REMOVER TABELAS EXISTENTES
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comentario;
DROP TABLE IF EXISTS participa;
DROP TABLE IF EXISTS evento;
DROP TABLE IF EXISTS utilizador;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 3. CRIAR TABELA UTILIZADOR
-- ============================================
CREATE TABLE utilizador (
    utilizador_id INT NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(200) NOT NULL,
    email         VARCHAR(100) NOT NULL,
    foto_perfil   VARCHAR(255) NULL,
    senha         VARCHAR(255) NOT NULL,
    telefone      VARCHAR(20)  NULL,
    data_registo  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (utilizador_id),
    UNIQUE KEY uk_email (email),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 4. CRIAR TABELA EVENTO
-- ============================================
CREATE TABLE evento (
    evento_id      INT NOT NULL AUTO_INCREMENT,
    nome           VARCHAR(200) NOT NULL,
    descricao      TEXT NULL,
    data_inicio    DATE NOT NULL,
    hora_inicio    TIME NOT NULL DEFAULT '00:00:00',
    data_fim       DATE NOT NULL,
    hora_fim       TIME NOT NULL DEFAULT '23:59:00',
    local_evento   VARCHAR(200) NOT NULL,
    vagas          INT NOT NULL DEFAULT 0,
    imagem         VARCHAR(255) NULL,
    imagens_extras JSON NULL,
    utilizador_id  INT NOT NULL,
    data_criacao   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (evento_id),
    INDEX idx_utilizador   (utilizador_id),
    INDEX idx_data_inicio  (data_inicio),
    INDEX idx_data_criacao (data_criacao),

    CONSTRAINT fk_evento_utilizador
        FOREIGN KEY (utilizador_id)
        REFERENCES utilizador(utilizador_id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT chk_vagas CHECK (vagas >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 5. CRIAR TABELA PARTICIPA
-- ============================================
CREATE TABLE participa (
    evento_id         INT NOT NULL,
    utilizador_id     INT NOT NULL,
    data_participacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (evento_id, utilizador_id),
    INDEX idx_utilizador        (utilizador_id),
    INDEX idx_data_participacao (data_participacao),

    CONSTRAINT fk_participa_evento
        FOREIGN KEY (evento_id)
        REFERENCES evento(evento_id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_participa_utilizador
        FOREIGN KEY (utilizador_id)
        REFERENCES utilizador(utilizador_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 6. CRIAR TABELA COMENTARIO
-- ============================================
CREATE TABLE comentario (
    comentario_id   INT NOT NULL AUTO_INCREMENT,
    evento_id       INT NOT NULL,
    utilizador_id   INT NOT NULL,
    texto           TEXT NOT NULL,
    data_comentario TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (comentario_id),
    INDEX idx_evento     (evento_id),
    INDEX idx_utilizador (utilizador_id),
    INDEX idx_data       (data_comentario),

    CONSTRAINT fk_comentario_evento
        FOREIGN KEY (evento_id)
        REFERENCES evento(evento_id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_comentario_utilizador
        FOREIGN KEY (utilizador_id)
        REFERENCES utilizador(utilizador_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 6b. TRIGGERS - LIMITE DE 5 IMAGENS POR EVENTO
--     (1 imagem de capa + até 4 imagens extra = máx. 5)
-- ============================================
DROP TRIGGER IF EXISTS trg_evento_limite_imagens_insert;
DROP TRIGGER IF EXISTS trg_evento_limite_imagens_update;

DELIMITER $$

CREATE TRIGGER trg_evento_limite_imagens_insert
BEFORE INSERT ON evento
FOR EACH ROW
BEGIN
    IF NEW.imagens_extras IS NOT NULL
       AND JSON_VALID(NEW.imagens_extras)
       AND JSON_LENGTH(NEW.imagens_extras) > 4 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Limite excedido: máximo de 5 imagens por evento (1 capa + 4 extras).';
    END IF;
END$$

CREATE TRIGGER trg_evento_limite_imagens_update
BEFORE UPDATE ON evento
FOR EACH ROW
BEGIN
    IF NEW.imagens_extras IS NOT NULL
       AND JSON_VALID(NEW.imagens_extras)
       AND JSON_LENGTH(NEW.imagens_extras) > 4 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Limite excedido: máximo de 5 imagens por evento (1 capa + 4 extras).';
    END IF;
END$$

DELIMITER ;

-- ============================================
-- 7. INSERIR UTILIZADORES DE TESTE
-- ============================================
INSERT INTO utilizador (nome, email, senha, telefone) VALUES
('Joao Silva',     'joao.silva@email.com',     '123456', '912345678'),
('Maria Santos',   'maria.santos@email.com',   '123456', '913456789'),
('Pedro Costa',    'pedro.costa@email.com',    '123456', '914567890'),
('Ana Ferreira',   'ana.ferreira@email.com',   '123456', '915678901'),
('Carlos Mendes',  'carlos.mendes@email.com',  '123456', '916789012'),
('Sofia Oliveira', 'sofia.oliveira@email.com', '123456', '917890123');

-- ============================================
-- 8. INSERIR EVENTOS DE TESTE
-- ============================================
INSERT INTO evento (nome, descricao, data_inicio, hora_inicio, data_fim, hora_fim, local_evento, vagas, utilizador_id) VALUES
('Limpeza da Praia de Matosinhos',
 'Acao de voluntariado para limpeza da praia. Traga luvas e sacos do lixo. Vamos juntos manter as nossas praias limpas!',
 '2026-02-15', '09:00:00', '2026-02-15', '18:00:00', 'Praia de Matosinhos, Porto', 50, 1),

('Plantacao de Arvores no Parque da Cidade',
 'Vamos plantar arvores nativas no Parque da Cidade. Atividade adequada para todas as idades. Material fornecido.',
 '2026-03-01', '09:00:00', '2026-03-01', '18:00:00', 'Parque da Cidade, Porto', 40, 1),

('Workshop de Reciclagem Criativa',
 'Aprenda a transformar materiais reciclaveis em objetos uteis e decorativos. Oficina pratica e gratuita.',
 '2026-02-20', '09:00:00', '2026-02-20', '18:00:00', 'Centro Cultural de Vila Nova de Gaia', 25, 2),

('Recolha de Alimentos para Familias Carenciadas',
 'Campanha solidaria de recolha de alimentos nao pereciveis. Doe e ajude quem mais precisa.',
 '2026-02-10', '09:00:00', '2026-02-10', '18:00:00', 'Supermercado Continente, Gaia', 30, 2),

('Visita a Lar de Idosos',
 'Passar uma tarde com os idosos, ouvir historias e fazer companhia. Levar alegria e o melhor presente.',
 '2026-02-25', '09:00:00', '2026-02-25', '18:00:00', 'Lar Sao Vicente de Paulo, Porto', 15, 3),

('Maratona de Leitura em Bibliotecas',
 'Voluntarios vao ler historias para criancas em bibliotecas publicas. Ajude a promover a leitura!',
 '2026-03-05', '09:00:00', '2026-03-05', '18:00:00', 'Biblioteca Municipal do Porto', 20, 3),

('Caminhada Solidaria pela Saude Mental',
 'Caminhada de sensibilizacao para a importancia da saude mental. Todos sao bem-vindos!',
 '2026-03-10', '09:00:00', '2026-03-10', '18:00:00', 'Jardim do Passeio Alegre, Porto', 100, 4),

('Oficina de Compostagem Domestica',
 'Aprenda a fazer compostagem em casa e reduzir o desperdicio. Workshop pratico com especialistas.',
 '2026-02-28', '09:00:00', '2026-02-28', '18:00:00', 'Horta Comunitaria de Gaia', 20, 4),

('Apoio Escolar a Criancas',
 'Voluntariado de explicacoes gratuitas para criancas do 1o ciclo. Ajude no sucesso escolar!',
 '2026-03-15', '09:00:00', '2026-03-15', '18:00:00', 'Junta de Freguesia de Campanha', 10, 5),

('Feira de Troca de Livros e Brinquedos',
 'Traga livros e brinquedos que ja nao usa e troque por outros. Economia circular em acao!',
 '2026-03-20', '09:00:00', '2026-03-20', '18:00:00', 'Praca da Republica, Gaia', 60, 5),

('Construcao de Casas para Animais de Rua',
 'Vamos construir abrigos para animais abandonados. Traga ferramentas e boa vontade!',
 '2026-02-18', '09:00:00', '2026-02-18', '18:00:00', 'Associacao Protetora dos Animais, Porto', 25, 6),

('Limpeza das Margens do Rio Douro',
 'Acao de limpeza das margens do Rio Douro. Proteja o nosso rio e a biodiversidade local.',
 '2026-03-25', '09:00:00', '2026-03-25', '18:00:00', 'Cais de Gaia', 45, 6);

-- ============================================
-- 9. INSERIR PARTICIPACOES DE TESTE
-- ============================================
INSERT INTO participa (evento_id, utilizador_id) VALUES
(1, 2), (1, 3), (1, 4), (1, 5),
(2, 2), (2, 4), (2, 6),
(3, 1), (3, 3), (3, 5), (3, 6),
(4, 1), (4, 3), (4, 6),
(5, 2), (5, 4), (5, 6),
(6, 1), (6, 2), (6, 4),
(7, 1), (7, 2), (7, 3), (7, 5), (7, 6),
(8, 1), (8, 3), (8, 5),
(9, 2), (9, 4), (9, 6),
(10, 1), (10, 3), (10, 4), (10, 6),
(11, 1), (11, 2), (11, 3), (11, 5),
(12, 2), (12, 3), (12, 4), (12, 5);

-- ============================================
-- 10. INSERIR COMENTARIOS DE TESTE
-- ============================================
INSERT INTO comentario (evento_id, utilizador_id, texto) VALUES
(1, 2, 'Otima iniciativa! Estarei la com certeza.'),
(1, 3, 'Ja participei no ano passado, foi uma experiencia incrivel!'),
(1, 4, 'Posso levar luvas extra para quem precisar.'),
(2, 4, 'Adoro este tipo de acoes. Plantamos mais de 50 arvores no ultimo evento!'),
(2, 6, 'Que maravilha, vou inscrever-me ja.'),
(3, 1, 'Tenho muito material reciclavel em casa, e a oportunidade perfeita.'),
(3, 5, 'Workshop muito bem organizado. Recomendo!'),
(4, 3, 'Ja separei alguns alimentos para levar. Todos deviamos ajudar.'),
(5, 2, 'Os idosos adoram companhia. Vale muito a pena.'),
(6, 1, 'Ler para criancas e uma das melhores sensacoes do mundo.'),
(7, 5, 'A saude mental e tao importante. Obrigada por esta iniciativa!'),
(7, 6, 'Vou levar a minha familia toda. E para uma causa muito importante.'),
(8, 3, 'Ja faco compostagem em casa ha 2 anos. E mais facil do que parece!'),
(9, 4, 'Tenho experiencia em dar explicacoes. Conto com voces.'),
(10, 6, 'Tenho muitos livros para trocar. Ate ja!'),
(11, 2, 'Os animais de rua merecem muito mais atencao. Bravo!'),
(12, 5, 'O Douro e o nosso rio. Temos de o proteger juntos.');

-- ============================================
-- 11. VIEWS UTEIS
-- ============================================
CREATE OR REPLACE VIEW v_eventos_completos AS
SELECT
    e.evento_id,
    e.nome,
    e.descricao,
    e.data_inicio,
    e.hora_inicio,
    e.data_fim,
    e.hora_fim,
    e.local_evento,
    e.vagas,
    e.imagem,
    e.data_criacao,
    u.nome        AS criador_nome,
    u.email       AS criador_email,
    u.foto_perfil AS criador_foto,
    COUNT(DISTINCT p.utilizador_id) AS total_participantes,
    COUNT(DISTINCT c.comentario_id) AS total_comentarios
FROM evento e
JOIN utilizador u ON e.utilizador_id = u.utilizador_id
LEFT JOIN participa  p ON e.evento_id = p.evento_id
LEFT JOIN comentario c ON e.evento_id = c.evento_id
GROUP BY e.evento_id;

CREATE OR REPLACE VIEW v_participacoes_detalhadas AS
SELECT
    p.evento_id,
    p.utilizador_id,
    p.data_participacao,
    e.nome        AS evento_nome,
    e.data_inicio,
    e.hora_inicio,
    e.data_fim,
    e.hora_fim,
    e.local_evento,
    u.nome        AS participante_nome,
    u.email       AS participante_email,
    u.foto_perfil AS participante_foto
FROM participa p
JOIN evento     e ON p.evento_id     = e.evento_id
JOIN utilizador u ON p.utilizador_id = u.utilizador_id;

-- ============================================
-- 12. PROCEDURE: INSCREVER UTILIZADOR
-- ============================================
DROP PROCEDURE IF EXISTS sp_inscrever_evento;

DELIMITER $$
CREATE PROCEDURE sp_inscrever_evento(
    IN p_evento_id     INT,
    IN p_utilizador_id INT
)
BEGIN
    DECLARE v_existe INT;
    SELECT COUNT(*) INTO v_existe
    FROM participa
    WHERE evento_id = p_evento_id AND utilizador_id = p_utilizador_id;

    IF v_existe = 0 THEN
        INSERT INTO participa (evento_id, utilizador_id)
        VALUES (p_evento_id, p_utilizador_id);
        SELECT 'Inscricao realizada com sucesso!' AS mensagem;
    ELSE
        SELECT 'Ja esta inscrito neste evento!' AS mensagem;
    END IF;
END$$
DELIMITER ;

-- ============================================
-- 13. VERIFICACOES FINAIS
-- ============================================
SELECT COUNT(*) AS 'Total de Utilizadores' FROM utilizador;
SELECT COUNT(*) AS 'Total de Eventos'       FROM evento;
SELECT COUNT(*) AS 'Total de Participacoes' FROM participa;
SELECT COUNT(*) AS 'Total de Comentarios'   FROM comentario;

-- ============================================
-- 14. FINALIZACAO
-- ============================================
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Base de dados HUMANICARE criada com sucesso!' AS STATUS;
SELECT 'Tabelas: utilizador, evento, participa, comentario' AS TABELAS;
SELECT 'Email: joao.silva@email.com  | Senha: 123456' AS TESTE1;
SELECT 'Email: maria.santos@email.com | Senha: 123456' AS TESTE2;