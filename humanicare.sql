-- ============================================
-- SCRIPT COMPLETO - SISTEMA DE EVENTOS
-- ============================================

-- 1. REMOVER TABELAS EXISTENTES (se necessário)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Participa;
DROP TABLE IF EXISTS Evento;
DROP TABLE IF EXISTS Utilizador;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 2. CRIAR TABELA UTILIZADOR
-- ============================================
CREATE TABLE Utilizador (
    utilizador_id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NULL,
    data_registo TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (utilizador_id),
    UNIQUE KEY uk_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 3. CRIAR TABELA EVENTO
-- ============================================
CREATE TABLE Evento (
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
    CONSTRAINT fk_evento_utilizador 
        FOREIGN KEY (utilizador_id) 
        REFERENCES Utilizador(utilizador_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_vagas CHECK (vagas >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 4. CRIAR TABELA PARTICIPA
-- ============================================
CREATE TABLE Participa (
    evento_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    data_participacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (evento_id, utilizador_id),
    
    CONSTRAINT fk_participa_evento 
        FOREIGN KEY (evento_id) 
        REFERENCES Evento(evento_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
    CONSTRAINT fk_participa_utilizador 
        FOREIGN KEY (utilizador_id) 
        REFERENCES Utilizador(utilizador_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- 5. CRIAR ÍNDICES ADICIONAIS (para melhor performance)
-- ============================================
CREATE INDEX idx_evento_data ON Evento(data_evento);
CREATE INDEX idx_evento_utilizador ON Evento(utilizador_id);
CREATE INDEX idx_participa_utilizador ON Participa(utilizador_id);

-- ============================================
-- 6. INSERIR DADOS DE TESTE
-- ============================================

-- Inserir utilizadores de teste (senha: "123456" sem hash por simplicidade)
INSERT INTO Utilizador (nome, email, senha, telefone) VALUES
('João Silva', 'joao.silva@email.com', '123456', '912345678'),
('Maria Santos', 'maria.santos@email.com', '123456', '913456789'),
('Pedro Costa', 'pedro.costa@email.com', '123456', '914567890'),
('Ana Ferreira', 'ana.ferreira@email.com', '123456', '915678901');

-- Inserir eventos de teste
INSERT INTO Evento (nome, descricao, data_evento, local_evento, vagas, utilizador_id) VALUES
('Workshop de PHP', 'Aprenda PHP do zero ao avançado', '2026-02-15', 'Centro de Formação Lisboa', 30, 1),
('Conferência de Tecnologia', 'As últimas tendências em desenvolvimento web', '2026-03-10', 'Pavilhão Multiusos Porto', 100, 1),
('Meetup de Programadores', 'Networking e partilha de experiências', '2026-02-20', 'Café Central Coimbra', 20, 2),
('Hackathon 2026', 'Competição de desenvolvimento de software', '2026-04-05', 'Universidade de Aveiro', 50, 2),
('Curso de JavaScript', 'JavaScript moderno e frameworks', '2026-03-01', 'Online', 40, 3);

-- Inserir participações de teste
INSERT INTO Participa (evento_id, utilizador_id) VALUES
(1, 2),  -- Maria participa no Workshop de PHP
(1, 3),  -- Pedro participa no Workshop de PHP
(1, 4),  -- Ana participa no Workshop de PHP
(2, 2),  -- Maria participa na Conferência
(2, 3),  -- Pedro participa na Conferência
(3, 1),  -- João participa no Meetup
(3, 4),  -- Ana participa no Meetup
(4, 1),  -- João participa no Hackathon
(4, 3),  -- Pedro participa no Hackathon
(5, 2),  -- Maria participa no Curso JS
(5, 4);  -- Ana participa no Curso JS

-- ============================================
-- 7. VERIFICAR DADOS
-- ============================================
SELECT 'Utilizadores criados:' as Info;
SELECT * FROM Utilizador;

SELECT 'Eventos criados:' as Info;
SELECT * FROM Evento;

SELECT 'Participações registadas:' as Info;
SELECT * FROM Participa;