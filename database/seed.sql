-- =============================================================
-- seed.sql - Dados iniciais para testes manuais da API
-- =============================================================
-- Executa inserts idempotentes com emails fixos para evitar
-- duplicacao quando o arquivo for rodado varias vezes.

USE api_php;

INSERT INTO users (name, email, hair_color, eye_color, height, weight)
VALUES
    ('Ana Souza', 'ana.souza@example.com', 'castanho', 'castanho', 1.65, 58.40),
    ('Bruno Lima', 'bruno.lima@example.com', 'preto', 'verde', 1.82, 79.20),
    ('Carla Mendes', 'carla.mendes@example.com', 'loiro', 'azul', 1.70, 63.00),
    ('Diego Alves', 'diego.alves@example.com', 'ruivo', 'castanho', 1.76, 74.50),
    ('Fernanda Rocha', 'fernanda.rocha@example.com', 'preto', 'mel', 1.68, 60.30)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    hair_color = VALUES(hair_color),
    eye_color = VALUES(eye_color),
    height = VALUES(height),
    weight = VALUES(weight);
