<?php
// Configuração da conexão com o banco (padrão do XAMPP: usuário root, sem senha)
const DB_HOST = 'localhost';
const DB_NOME = 'mr_barber';
const DB_USUARIO = 'root';
const DB_SENHA = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NOME . ';charset=utf8mb4',
                DB_USUARIO,
                DB_SENHA,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Não foi possível conectar ao banco de dados. Verifique se o MySQL está ligado no XAMPP '
                . 'e se o arquivo banco.sql foi importado.');
        }
    }

    return $pdo;
}
