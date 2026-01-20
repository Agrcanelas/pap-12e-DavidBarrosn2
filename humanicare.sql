-- Criação da Base de Dados
CREATE DATABASE IF NOT EXISTS sistema_eventos;
USE sistema_eventos;

-- Tabela Utilizador
CREATE TABLE Utilizador (
    utilizador_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela Evento
CREATE TABLE Evento (
    evento_id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    local VARCHAR(200),
    data_evento DATETIME NOT NULL,
    vagas INT DEFAULT 0
);

-- Tabela Cria (relacionamento Utilizador cria Evento)
CREATE TABLE Cria (
    evento_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (evento_id, utilizador_id),
    FOREIGN KEY (evento_id) REFERENCES Evento(evento_id) ON DELETE CASCADE,
    FOREIGN KEY (utilizador_id) REFERENCES Utilizador(utilizador_id) ON DELETE CASCADE
);

-- Tabela Participa (relacionamento Utilizador participa em Evento)
CREATE TABLE Participa (
    evento_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    data_participacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (evento_id, utilizador_id),
    FOREIGN KEY (evento_id) REFERENCES Evento(evento_id) ON DELETE CASCADE,
    FOREIGN KEY (utilizador_id) REFERENCES Utilizador(utilizador_id) ON DELETE CASCADE
);

-- Índices para melhorar performance
CREATE INDEX idx_evento_data ON Evento(data_evento);
CREATE INDEX idx_utilizador_email ON Utilizador(email);
CREATE INDEX idx_cria_utilizador ON Cria(utilizador_id);
CREATE INDEX idx_participa_utilizador ON Participa(utilizador_id);