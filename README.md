# Banco Simplificado

Este é um mini sistema bancário desenvolvido com Laravel. Ele permite operações básicas entre usuários, como transferências e consultas de saldo. O sistema é preparado para rodar via Docker, e os testes são executados com PHPUnit.

## Tecnologias Utilizadas

- PHP 8+
- Laravel
- MySQL
- Docker
- Docker Compose
- PHPUnit

## Requisitos

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## Como rodar o projeto

1. Clone o repositório:

    ```bash
    git clone git@github.com:leabru29/banco-simplificado.git
    cd banco-simplificado
    ```

2. Copie o arquivo de exemplo do ambiente:

    ```bash
    cp .env.example .env
    ```

3. Suba os containers com Docker Compose:

    ```bash
    docker-compose up -d
    ```

4. Acesse o container do Laravel:

    ```bash
    docker exec -it banco-app bash
    ```

5. Instale as dependências:

    ```bash
    composer install
    ```

6. Gere a chave da aplicação:

    ```bash
    php artisan key:generate
    ```

7. Execute as migrations:

    ```bash
    php artisan migrate
    ```

8. A aplicação estará disponível em:

    ```
    http://localhost:8000
    ```

## Executando os Testes

Para rodar os testes automatizados com PHPUnit:

```bash
php artisan test
# ou
vendor/bin/phpunit
```

## Estrutura Básica do Projeto

- `app/Models/` – Models do sistema, como `User`, `Transaction`, etc.
- `app/Http/Controllers/` – Lógica das rotas, como transferências e validações.
- `tests/Feature/` – Testes automatizados de funcionalidades principais.
- `database/migrations/` – Estrutura do banco de dados.

## Funcionalidades

- Cadastro de usuários comuns e lojistas.
- Transferência de saldo entre usuários.
- Validações de permissões para transferências.
- Notificações simuladas após transações.
- Testes automatizados com PHPUnit.

## Contato

Desenvolvido por **Leandro Bezerra da Silva**  
[LinkedIn](https://www.linkedin.com/in/leandro-bezerra-da-silva-740064145/)  
[GitHub](https://github.com/leabru29)