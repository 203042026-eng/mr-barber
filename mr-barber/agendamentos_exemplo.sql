-- OPCIONAL: agendamentos de exemplo para testar o painel admin.
-- Rode DEPOIS do banco.sql. Ajuste as datas se quiser.
USE mr_barber;

INSERT INTO agendamento (id_servico, nome_cliente, telefone, data_agendamento, hora_agendamento, status) VALUES
(1, 'Cliente 1', '(11) 90000-0001', '2026-11-20', '09:00:00', 'confirmado'),
(2, 'Cliente 2', '(11) 90000-0002', '2026-11-20', '09:30:00', 'pendente'),
(3, 'Cliente 3', '(11) 90000-0003', '2026-11-20', '10:30:00', 'pendente'),
(5, 'Cliente 4', '(11) 90000-0004', '2026-11-20', '11:00:00', 'confirmado'),
(4, 'Cliente 5', '(11) 90000-0005', '2026-11-21', '14:00:00', 'pendente'),
(7, 'Cliente 6', '(11) 90000-0006', '2026-11-21', '15:00:00', 'cancelado');
