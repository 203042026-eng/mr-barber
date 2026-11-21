-- =====================================================
-- MR BARBER - Banco de dados (Sprint 2, pagina 12)
-- Importe este arquivo no phpMyAdmin (aba "Importar")
-- ou rode: mysql -u root < banco.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS mr_barber CHARACTER SET utf8mb4;
USE mr_barber;

CREATE TABLE administrador (
 id_admin INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 email VARCHAR(120) NOT NULL UNIQUE,
 senha_hash VARCHAR(255) NOT NULL,
 ativo BOOLEAN DEFAULT TRUE,
 data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categoria (
 id_categoria INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(60) NOT NULL UNIQUE,
 descricao VARCHAR(255),
 ordem_exibicao INT DEFAULT 0
);

CREATE TABLE servico (
 id_servico INT AUTO_INCREMENT PRIMARY KEY,
 id_categoria INT NOT NULL,
 id_admin INT NOT NULL,
 nome VARCHAR(100) NOT NULL,
 descricao TEXT,
 preco DECIMAL(10,2) NOT NULL CHECK (preco > 0),
 duracao_min INT,
 ativo BOOLEAN DEFAULT TRUE,
 data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
 FOREIGN KEY (id_admin) REFERENCES administrador(id_admin)
);

CREATE TABLE info_contato (
 id_info INT AUTO_INCREMENT PRIMARY KEY,
 id_admin INT,
 endereco VARCHAR(200) NOT NULL,
 telefone VARCHAR(20),
 whatsapp VARCHAR(20),
 email VARCHAR(120),
 horario_funcionamento VARCHAR(200),
 link_mapa VARCHAR(500),
 FOREIGN KEY (id_admin) REFERENCES administrador(id_admin)
);

CREATE TABLE agendamento (
 id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
 id_servico INT NOT NULL,
 nome_cliente VARCHAR(100) NOT NULL,
 telefone VARCHAR(20) NOT NULL,
 data_agendamento DATE NOT NULL,
 hora_agendamento TIME NOT NULL,
 status VARCHAR(15) DEFAULT 'pendente',
 data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_servico) REFERENCES servico(id_servico)
);

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Administrador (login: admin@mrbarber.com.br | senha: admin123)
-- TROQUE A SENHA depois de apresentar o projeto.
INSERT INTO administrador (nome, email, senha_hash) VALUES
('Admin', 'admin@mrbarber.com.br', '$2y$10$nR5A.p9X57IVLFkbbKHEUuk3dMHFR75ORN0L0gpkJIcJURx/M0XeG');

-- Categorias (a ordem define as abas do catalogo)
INSERT INTO categoria (nome, descricao, ordem_exibicao) VALUES
('Cabelo', 'Cortes e tratamentos para o cabelo', 1),
('Barba', 'Modelagem e cuidados com a barba', 2),
('Sobrancelha', 'Design e limpeza de sobrancelha', 3),
('Combos', 'Serviços combinados com preço especial', 4);

-- Serviços (os 3 primeiros aparecem em destaque na Home)
-- Os preços abaixo são apenas EXEMPLOS: edite pelo painel admin.
INSERT INTO servico (id_categoria, id_admin, nome, descricao, preco, duracao_min) VALUES
(1, 1, 'Corte', 'Corte moderno ou clássico, com acabamento na navalha.', 40.00, 30),
(2, 1, 'Barba', 'Barba modelada com toalha quente e finalização.', 30.00, 30),
(4, 1, 'Corte + Barba', 'O combo completo para sair com o visual renovado.', 65.00, 60),
(3, 1, 'Sobrancelha', 'Design e limpeza das sobrancelhas.', 15.00, 15),
(1, 1, 'Luzes', 'Mechas e luzes com produtos de qualidade.', 120.00, 90),
(1, 1, 'Progressiva', 'Alinhamento e redução de volume dos fios.', 150.00, 90),
(1, 1, 'Corte infantil', 'Corte com paciência e cuidado para os pequenos.', 35.00, 30);

-- Dados de contato (EDITE pelo painel: menu "Dados de contato")
INSERT INTO info_contato (id_admin, endereco, telefone, whatsapp, email, horario_funcionamento, link_mapa) VALUES
(1, 'Rua Exemplo, 000 - Centro, Guarulhos - SP', '(11) 0000-0000', '(11) 90000-0000',
 'contato@mrbarber.com.br', 'Seg a Sáb, das 09h às 16h',
 'https://www.google.com/maps?q=Guarulhos,+SP&output=embed');

-- =====================================================
-- CONSULTAS USADAS NO SITE (para a documentacao)
-- =====================================================
-- servicos.php:
-- SELECT s.nome, s.descricao, s.preco, c.nome AS categoria
-- FROM servico s JOIN categoria c ON c.id_categoria = s.id_categoria
-- WHERE s.ativo = TRUE ORDER BY c.ordem_exibicao, s.nome;
--
-- agendar.php (horarios ocupados):
-- SELECT hora_agendamento FROM agendamento
-- WHERE data_agendamento = ? AND status <> 'cancelado';
