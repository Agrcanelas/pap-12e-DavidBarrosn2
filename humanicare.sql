-- ============================================
-- SCRIPT COMPLETO E CORRIGIDO - HUMANICARE
-- Base de Dados: humanicare
-- Versão: 2.0 (Corrigida)
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

DROP TABLE IF EXISTS participa;
DROP TABLE IF EXISTS evento;
DROP TABLE IF EXISTS utilizador;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 3. CRIAR TABELA UTILIZADOR
-- ============================================
CREATE TABLE utilizador (
    utilizador_id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NULL,
    data_registo TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (utilizador_id),
    UNIQUE KEY uk_email (email),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 4. CRIAR TABELA EVENTO
-- ============================================
CREATE TABLE evento (
    evento_id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT NULL,
    data_evento DATE NOT NULL,
    local_evento VARCHAR(200) NOT NULL,
    vagas INT NOT NULL DEFAULT 0,
    imagem VARCHAR(255) NULL,
    utilizador_id INT NOT NULL,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (evento_id),
    INDEX idx_utilizador (utilizador_id),
    INDEX idx_data_evento (data_evento),
    INDEX idx_data_criacao (data_criacao),
    
    CONSTRAINT fk_evento_utilizador 
        FOREIGN KEY (utilizador_id) 
        REFERENCES utilizador(utilizador_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT chk_vagas CHECK (vagas >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 5. CRIAR TABELA PARTICIPA
-- ============================================
CREATE TABLE participa (
    evento_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    data_participacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (evento_id, utilizador_id),
    INDEX idx_utilizador (utilizador_id),
    INDEX idx_data_participacao (data_participacao),
    
    CONSTRAINT fk_participa_evento 
        FOREIGN KEY (evento_id) 
        REFERENCES evento(evento_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_participa_utilizador 
        FOREIGN KEY (utilizador_id) 
        REFERENCES utilizador(utilizador_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 6. INSERIR UTILIZADORES DE TESTE
-- ============================================
INSERT INTO utilizador (nome, email, senha, telefone) VALUES
('João Silva', 'joao.silva@email.com', '123456', '912345678'),
('Maria Santos', 'maria.santos@email.com', '123456', '913456789'),
('Pedro Costa', 'pedro.costa@email.com', '123456', '914567890'),
('Ana Ferreira', 'ana.ferreira@email.com', '123456', '915678901'),
('Carlos Mendes', 'carlos.mendes@email.com', '123456', '916789012'),
('Sofia Oliveira', 'sofia.oliveira@email.com', '123456', '917890123');

-- ============================================
-- 7. INSERIR EVENTOS DE TESTE
-- ============================================
INSERT INTO evento (nome, descricao, data_evento, local_evento, vagas, utilizador_id) VALUES
('Limpeza da Praia de Matosinhos', 'Ação de voluntariado para limpeza da praia. Traga luvas e sacos do lixo. Vamos juntos manter as nossas praias limpas!', '2026-02-15', 'Praia de Matosinhos, Porto', 50, 1),

('Plantação de Árvores no Parque da Cidade', 'Vamos plantar árvores nativas no Parque da Cidade. Atividade adequada para todas as idades. Material fornecido.', '2026-03-01', 'Parque da Cidade, Porto', 40, 1),

('Workshop de Reciclagem Criativa', 'Aprenda a transformar materiais recicláveis em objetos úteis e decorativos. Oficina prática e gratuita.', '2026-02-20', 'Centro Cultural de Vila Nova de Gaia', 25, 2),

('Recolha de Alimentos para Famílias Carenciadas', 'Campanha solidária de recolha de alimentos não perecíveis. Doe e ajude quem mais precisa.', '2026-02-10', 'Supermercado Continente, Gaia', 30, 2),

('Visita a Lar de Idosos', 'Passar uma tarde com os idosos, ouvir histórias e fazer companhia. Levar alegria é o melhor presente.', '2026-02-25', 'Lar São Vicente de Paulo, Porto', 15, 3),

('Maratona de Leitura em Bibliotecas', 'Voluntários vão ler histórias para crianças em bibliotecas públicas. Ajude a promover a leitura!', '2026-03-05', 'Biblioteca Municipal do Porto', 20, 3),

('Caminhada Solidária pela Saúde Mental', 'Caminhada de sensibilização para a importância da saúde mental. Todos são bem-vindos!', '2026-03-10', 'Jardim do Passeio Alegre, Porto', 100, 4),

('Oficina de Compostagem Doméstica', 'Aprenda a fazer compostagem em casa e reduzir o desperdício. Workshop prático com especialistas.', '2026-02-28', 'Horta Comunitária de Gaia', 20, 4),

('Apoio Escolar a Crianças', 'Voluntariado de explicações gratuitas para crianças do 1º ciclo. Ajude no sucesso escolar!', '2026-03-15', 'Junta de Freguesia de Campanhã', 10, 5),

('Feira de Troca de Livros e Brinquedos', 'Traga livros e brinquedos que já não usa e troque por outros. Economia circular em ação!', '2026-03-20', 'Praça da República, Gaia', 60, 5),

('Construção de Casas para Animais de Rua', 'Vamos construir abrigos para animais abandonados. Traga ferramentas e boa vontade!', '2026-02-18', 'Associação Protetora dos Animais, Porto', 25, 6),

('Limpeza das Margens do Rio Douro', 'Ação de limpeza das margens do Rio Douro. Proteja o nosso rio e a biodiversidade local.', '2026-03-25', 'Cais de Gaia', 45, 6);

-- ============================================
-- 8. INSERIR PARTICIPAÇÕES DE TESTE
-- ============================================
INSERT INTO participa (evento_id, utilizador_id) VALUES
-- Evento 1: Limpeza da Praia
(1, 2), (1, 3), (1, 4), (1, 5),

-- Evento 2: Plantação de Árvores
(2, 2), (2, 4), (2, 6),

-- Evento 3: Workshop de Reciclagem
(3, 1), (3, 3), (3, 5), (3, 6),

-- Evento 4: Recolha de Alimentos
(4, 1), (4, 3), (4, 6),

-- Evento 5: Visita a Lar
(5, 2), (5, 4), (5, 6),

-- Evento 6: Maratona de Leitura
(6, 1), (6, 2), (6, 4),

-- Evento 7: Caminhada Solidária
(7, 1), (7, 2), (7, 3), (7, 5), (7, 6),

-- Evento 8: Oficina de Compostagem
(8, 1), (8, 3), (8, 5),

-- Evento 9: Apoio Escolar
(9, 2), (9, 4), (9, 6),

-- Evento 10: Feira de Troca
(10, 1), (10, 3), (10, 4), (10, 6),

-- Evento 11: Casas para Animais
(11, 1), (11, 2), (11, 3), (11, 5),

-- Evento 12: Limpeza do Douro
(12, 2), (12, 3), (12, 4), (12, 5);

-- ============================================
-- 9. ESTATÍSTICAS E VERIFICAÇÕES
-- ============================================

-- Ver total de utilizadores
SELECT COUNT(*) as 'Total de Utilizadores' FROM utilizador;

-- Ver total de eventos
SELECT COUNT(*) as 'Total de Eventos' FROM evento;

-- Ver total de participações
SELECT COUNT(*) as 'Total de Participações' FROM participa;

-- Ver eventos com mais participantes
SELECT 
    e.nome as 'Evento',
    e.local_evento as 'Local',
    e.data_evento as 'Data',
    COUNT(p.utilizador_id) as 'Participantes'
FROM evento e
LEFT JOIN participa p ON e.evento_id = p.evento_id
GROUP BY e.evento_id
ORDER BY COUNT(p.utilizador_id) DESC;

-- Ver utilizadores mais ativos
SELECT 
    u.nome as 'Utilizador',
    COUNT(p.evento_id) as 'Eventos Participados'
FROM utilizador u
LEFT JOIN participa p ON u.utilizador_id = p.utilizador_id
GROUP BY u.utilizador_id
ORDER BY COUNT(p.evento_id) DESC;

-- Ver eventos criados por cada utilizador
SELECT 
    u.nome as 'Criador',
    COUNT(e.evento_id) as 'Eventos Criados'
FROM utilizador u
LEFT JOIN evento e ON u.utilizador_id = e.utilizador_id
GROUP BY u.utilizador_id
ORDER BY COUNT(e.evento_id) DESC;

-- ============================================
-- 10. VIEWS ÚTEIS (OPCIONAL)
-- ============================================

-- View: Eventos com informação completa
CREATE OR REPLACE VIEW v_eventos_completos AS
SELECT 
    e.evento_id,
    e.nome,
    e.descricao,
    e.data_evento,
    e.local_evento,
    e.vagas,
    e.imagem,
    e.data_criacao,
    u.nome as criador_nome,
    u.email as criador_email,
    COUNT(DISTINCT p.utilizador_id) as total_participantes
FROM evento e
JOIN utilizador u ON e.utilizador_id = u.utilizador_id
LEFT JOIN participa p ON e.evento_id = p.evento_id
GROUP BY e.evento_id;

-- View: Participações com detalhes
CREATE OR REPLACE VIEW v_participacoes_detalhadas AS
SELECT 
    p.evento_id,
    p.utilizador_id,
    p.data_participacao,
    e.nome as evento_nome,
    e.data_evento,
    e.local_evento,
    u.nome as participante_nome,
    u.email as participante_email
FROM participa p
JOIN evento e ON p.evento_id = e.evento_id
JOIN utilizador u ON p.utilizador_id = u.utilizador_id;

-- ============================================
-- 11. PROCEDURES ÚTEIS (OPCIONAL)
-- ============================================

-- Procedure: Inscrever utilizador em evento
DELIMITER $$

CREATE PROCEDURE sp_inscrever_evento(
    IN p_evento_id INT,
    IN p_utilizador_id INT
)
BEGIN
    DECLARE v_existe INT;
    
    -- Verificar se já está inscrito
    SELECT COUNT(*) INTO v_existe
    FROM participa
    WHERE evento_id = p_evento_id 
    AND utilizador_id = p_utilizador_id;
    
    IF v_existe = 0 THEN
        INSERT INTO participa (evento_id, utilizador_id)
        VALUES (p_evento_id, p_utilizador_id);
        SELECT 'Inscrição realizada com sucesso!' as mensagem;
    ELSE
        SELECT 'Já está inscrito neste evento!' as mensagem;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- 12. FINALIZAÇÃO
-- ============================================

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- Mensagem de sucesso
SELECT '✅ Base de dados HUMANICARE criada com sucesso!' as 'STATUS';
SELECT 'Utilize as credenciais de teste para login' as 'INFO';
SELECT 'Email: joao.silva@email.com | Senha: 123456' as 'TESTE 1';
SELECT 'Email: maria.santos@email.com | Senha: 123456' as 'TESTE 2';