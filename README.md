# API de Gerenciamento de Eventos

API REST em Laravel para cadastro de eventos e gerenciamento das inscrições dos participantes, com autenticação via Laravel Sanctum.

## Como executar

Requisitos: PHP 8.3+ e Composer.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

A API fica disponível em `http://localhost:8000/api`. O banco padrão é SQLite (`database/database.sqlite`).

## Autenticação

O cadastro e o login retornam um `token`. Envie-o nas demais requisições:

```
Authorization: Bearer {token}
Accept: application/json
```

## Rotas

| Método | Rota | Descrição | Autenticação |
|--------|------|-----------|--------------|
| POST | `/api/register` | Cadastro de usuário | Não |
| POST | `/api/login` | Autenticação | Não |
| GET | `/api/me` | Dados do usuário autenticado | Sim |
| POST | `/api/logout` | Encerrar sessão (revoga o token) | Sim |
| GET | `/api/events` | Listagem dos eventos (paginada) | Sim |
| POST | `/api/events` | Cadastro de evento | Sim |
| GET | `/api/events/{id}` | Consulta de um evento | Sim |
| PUT/PATCH | `/api/events/{id}` | Atualização de evento (somente o organizador) | Sim |
| DELETE | `/api/events/{id}` | Exclusão de evento (somente o organizador) | Sim |
| POST | `/api/events/{id}/registrations` | Inscrição do usuário autenticado no evento | Sim |
| GET | `/api/registrations` | Inscrições do usuário autenticado | Sim |
| DELETE | `/api/registrations/{id}` | Cancelamento de inscrição | Sim |

## Campos do evento

| Campo | Tipo | Regra |
|-------|------|-------|
| `name` | string | obrigatório |
| `description` | string | opcional |
| `location` | string | opcional |
| `event_date` | data `AAAA-MM-DD` | opcional |
| `event_time` | hora `HH:MM` | opcional |
| `max_participants` | inteiro ≥ 1 | opcional (vazio = sem limite) |

## Regras de negócio

- Inscrição em evento com lotação máxima atingida retorna `422`.
- Inscrição duplicada do mesmo usuário no mesmo evento retorna `409` (também há índice único no banco).
- Somente usuários autenticados realizam inscrições; sem token a resposta é `401`.
- Cada usuário vê e cancela apenas as próprias inscrições (`403` para as de outros).
- Somente quem cadastrou o evento pode atualizá-lo ou excluí-lo (`403` para os demais).
- A lotação não pode ser reduzida abaixo do número de inscritos.

## Estrutura

- Migrations: `database/migrations` (`events`, `registrations`)
- Models: `app/Models` (`User`, `Event`, `Registration`)
- Controllers: `app/Http/Controllers` (`AuthController`, `EventController`, `RegistrationController`)
- Form Requests: `app/Http/Requests`
- API Resources: `app/Http/Resources`
- Rotas: `routes/api.php`

## Testes

- Coleção do Postman: `docs/API-Eventos.postman_collection.json` (importe no Postman ou Insomnia; o token é salvo automaticamente após o cadastro/login).
- Testes automatizados: `php artisan test`
