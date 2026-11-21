# Site Mr Barber – Projeto Extensão (Web Standard)

Site completo com PHP + MySQL, seguindo os wireframes e o diagrama da 2ª Sprint.

## Como rodar no seu computador (XAMPP)

1. Instale o **XAMPP** (apachefriends.org).
2. Copie a pasta `mr-barber` para `C:\xampp\htdocs\`.
3. No **XAMPP Control Panel**, clique em **Start** no **Apache** e no **MySQL**.
4. Abra `http://localhost/phpmyadmin`, vá na aba **Importar**, escolha o arquivo **`banco.sql`** e clique em **Importar**.
   (Opcional: importe depois `agendamentos_exemplo.sql` para ter agendamentos de teste no painel.)
5. Acesse **`http://localhost/mr-barber/`**.

> Não abra os arquivos com duplo clique (endereço `C:/...`): o PHP só funciona pelo `localhost`.

## Acesso à área administrativa

- Endereço: `http://localhost/mr-barber/admin/login.php` (ou o link "Admin" do menu)
- E-mail: `admin@mrbarber.com.br`
- Senha: `admin123` (troque depois da apresentação)

## O que tem em cada pasta

```
mr-barber/
├── index.php, servicos.php, agendar.php, contato.php   páginas públicas
├── horarios.php            devolve os horários ocupados de uma data (usado no agendamento)
├── banco.sql               criação das tabelas (Sprint 2, pág. 12) + dados iniciais
├── agendamentos_exemplo.sql  (opcional) agendamentos de teste
├── config/conexao.php      dados de conexão com o MySQL
├── includes/               topo, rodapé, funções e controle de login
├── admin/                  login, painel (CRUD de serviços), agendamentos, categorias, dados de contato
├── css/                    style.css (site) e admin.css (painel)
├── js/                     agendar.js e admin.js (validações e interações)
└── img/                    coloque aqui barbearia.jpg (foto da seção "Sobre")
```

## Ajustes que você vai querer fazer

- **Foto da barbearia:** salve uma foto como `img/barbearia.jpg`. Enquanto não existir, aparece o quadro cinza do wireframe.
- **Endereço, telefones, e-mail, horário e mapa:** painel admin → **Dados de contato**. Os valores iniciais são fictícios.
- **Preços e serviços:** painel admin → **Serviços**. Os preços do `banco.sql` são exemplos.
- **Senha do MySQL diferente de vazia:** edite `config/conexao.php`.
- **Dias fechados e horários do agendamento:** constantes `DIAS_FECHADOS` e `HORARIOS` em `includes/funcoes.php`
  (hoje: domingo fechado, horários de 09:00 a 11:30 e de 13:00 a 15:30, como no wireframe).

## Regras do projeto implementadas

- Catálogo e destaques lidos do banco (consulta do projeto: `JOIN categoria ... ORDER BY ordem_exibicao, nome`).
- Agendamento grava como **pendente**; antes de gravar o PHP confere se já existe outro na mesma data e hora que não esteja cancelado.
- Datas passadas e dias fechados bloqueados (no JavaScript e de novo no PHP).
- Admin: **Confirmar** muda para confirmado; **Cancelar** libera o horário.
- Serviço que já tem agendamentos não pode ser excluído (para não perder o histórico): desmarque "Serviço ativo" para escondê-lo do site.
- Senha do administrador guardada com hash (`password_verify`), consultas com *prepared statements* e formulários do admin protegidos por token CSRF.
