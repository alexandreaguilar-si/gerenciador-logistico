CREATE DATABASE IF NOT EXISTS gerenciador_logistico
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gerenciador_logistico;

CREATE TABLE IF NOT EXISTS produtos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    peso_kg DECIMAL(10,3) NOT NULL DEFAULT 0,
    valor_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS enderecos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cep CHAR(9) NOT NULL,
    logradouro VARCHAR(180) NOT NULL,
    numero VARCHAR(30) NOT NULL,
    bairro VARCHAR(120) NOT NULL,
    cidade VARCHAR(120) NOT NULL,
    uf CHAR(2) NOT NULL,
    referencia VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transportadores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_razao VARCHAR(180) NOT NULL,
    documento VARCHAR(20) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    veiculo_modelo VARCHAR(120) NOT NULL,
    placa VARCHAR(10) NOT NULL,
    UNIQUE KEY uk_transportadores_documento (documento),
    UNIQUE KEY uk_transportadores_placa (placa)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fretes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transportador_id INT UNSIGNED NOT NULL,
    endereco_origem_id INT UNSIGNED NOT NULL,
    endereco_destino_id INT UNSIGNED NOT NULL,
    data_frete DATE NOT NULL,
    status ENUM('Pendente', 'Em Trânsito', 'Entregue') NOT NULL DEFAULT 'Pendente',
    valor_frete DECIMAL(12,2) NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fretes_transportador FOREIGN KEY (transportador_id)
        REFERENCES transportadores (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_fretes_origem FOREIGN KEY (endereco_origem_id)
        REFERENCES enderecos (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_fretes_destino FOREIGN KEY (endereco_destino_id)
        REFERENCES enderecos (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_fretes_data_status (data_frete, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS frete_itens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    frete_id INT UNSIGNED NOT NULL,
    produto_id INT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL,
    CONSTRAINT fk_frete_itens_frete FOREIGN KEY (frete_id)
        REFERENCES fretes (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_frete_itens_produto FOREIGN KEY (produto_id)
        REFERENCES produtos (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_frete_itens_produto (produto_id)
) ENGINE=InnoDB;

INSERT INTO produtos (nome, descricao, peso_kg, valor_unitario)
SELECT 'Caixa organizadora', 'Caixa plástica empilhável, 40 litros', 2.500, 89.90
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome = 'Caixa organizadora');
INSERT INTO produtos (nome, descricao, peso_kg, valor_unitario)
SELECT 'Monitor 24 polegadas', 'Monitor LED para escritório', 4.200, 899.00
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome = 'Monitor 24 polegadas');
INSERT INTO produtos (nome, descricao, peso_kg, valor_unitario)
SELECT 'Cadeira ergonômica', 'Cadeira para uso corporativo', 12.000, 749.90
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE nome = 'Cadeira ergonômica');

INSERT INTO enderecos (cep, logradouro, numero, bairro, cidade, uf, referencia)
SELECT '87501-000', 'Avenida Paraná', '1200', 'Centro', 'Umuarama', 'PR', 'Centro de distribuição'
WHERE NOT EXISTS (SELECT 1 FROM enderecos WHERE cep = '87501-000' AND numero = '1200');
INSERT INTO enderecos (cep, logradouro, numero, bairro, cidade, uf, referencia)
SELECT '87013-000', 'Avenida Brasil', '450', 'Zona 01', 'Maringá', 'PR', 'Recepção de mercadorias'
WHERE NOT EXISTS (SELECT 1 FROM enderecos WHERE cep = '87013-000' AND numero = '450');
INSERT INTO enderecos (cep, logradouro, numero, bairro, cidade, uf, referencia)
SELECT '86010-000', 'Rua Maranhão', '80', 'Centro', 'Londrina', 'PR', 'Portão lateral'
WHERE NOT EXISTS (SELECT 1 FROM enderecos WHERE cep = '86010-000' AND numero = '80');

INSERT INTO transportadores (nome_razao, documento, telefone, veiculo_modelo, placa)
SELECT 'Rota Sul Logística', '12345678000195', '(44) 3333-4455', 'Mercedes-Benz Accelo', 'ABC1D23'
WHERE NOT EXISTS (SELECT 1 FROM transportadores WHERE documento = '12345678000195');
INSERT INTO transportadores (nome_razao, documento, telefone, veiculo_modelo, placa)
SELECT 'Marcos Oliveira', '12345678909', '(44) 99999-1234', 'Fiat Ducato', 'DEF4G56'
WHERE NOT EXISTS (SELECT 1 FROM transportadores WHERE documento = '12345678909');

INSERT INTO fretes (transportador_id, endereco_origem_id, endereco_destino_id, data_frete, status, valor_frete, observacoes)
SELECT t.id, origem.id, destino.id, CURRENT_DATE, 'Em Trânsito', 580.00, 'Entrega de materiais de escritório'
FROM transportadores t
JOIN enderecos origem ON origem.cep = '87501-000' AND origem.numero = '1200'
JOIN enderecos destino ON destino.cep = '87013-000' AND destino.numero = '450'
WHERE t.documento = '12345678000195'
  AND NOT EXISTS (SELECT 1 FROM fretes WHERE observacoes = 'Entrega de materiais de escritório');

INSERT INTO frete_itens (frete_id, produto_id, quantidade)
SELECT f.id, p.id, 3
FROM fretes f
JOIN produtos p ON p.nome = 'Monitor 24 polegadas'
WHERE f.observacoes = 'Entrega de materiais de escritório'
  AND NOT EXISTS (SELECT 1 FROM frete_itens fi WHERE fi.frete_id = f.id AND fi.produto_id = p.id);
INSERT INTO frete_itens (frete_id, produto_id, quantidade)
SELECT f.id, p.id, 2
FROM fretes f
JOIN produtos p ON p.nome = 'Cadeira ergonômica'
WHERE f.observacoes = 'Entrega de materiais de escritório'
  AND NOT EXISTS (SELECT 1 FROM frete_itens fi WHERE fi.frete_id = f.id AND fi.produto_id = p.id);
