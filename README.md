# Open Food Facts Parser API

Uma API RESTful para curadoria de dados nutricionais do projeto Open Food Facts.

Este projeto foi desenvolvido como parte de um desafio técnico para a Fitness Foods LC. A API permite gerenciar informações sobre produtos alimentícios, automatizando a importação de dados da base do Open Food Facts e fornecendo endpoints para consulta e edição.

---

## 📋 Diferenciais do Desafio

Abaixo está o resumo dos diferenciais solicitados e como cada um foi atendido neste projeto:

- **Diferencial 1: Endpoint de busca com Elastic Search ou similares**
  - ❌ Não implementado. O projeto possui listagem paginada e consulta por código, mas não busca avançada.

- **Diferencial 2: Docker para facilitar deploy**
  - ✅ Cumprido! O projeto utiliza Docker/Laravel Sail, permitindo fácil setup e deploy para DevOps.

- **Diferencial 3: Sistema de alerta para falhas no Sync**
  - ✅ Cumprido! Falhas de importação são registradas em `import_histories` e nos logs, permitindo monitoramento e alerta.

- **Diferencial 4: Documentação OpenAPI 3.0**
  - ✅ Cumprido! Documentação gerada com L5-Swagger, disponível em `/docs` e `/api-docs`.

- **Diferencial 5: Unit Tests para GET e PUT**
  - ✅ Cumprido! Testes automatizados garantem o funcionamento dos endpoints GET e PUT do CRUD.

- **Diferencial 6: Segurança via API KEY**
  - ✅ Cumprido! Todos os endpoints são protegidos por middleware que exige o header `x-api-key`.


---

## Como eu planejei resolver o desafio?

Essa aqui é a documentação final , trazendo uma linguagem mais profissional de como o projeto ficou e seria consumido por outros profissionais.
No primeiro commit , eu fiz um registro mais voltado ao meu planejamento e linha de raciocínio.
Para entender melhor como foi planejado, por favor voltar ao commit "bfda214" , onde esse mesmo readme era voltado para uma explicação mais informal da idéia.
Informações mais técnicas foram documentadas de maneira informal com comentários dentro dos próprios arquivos.

---

## 🚀 Tecnologias Utilizadas

- **PHP 8.4** com **Laravel 12**
- **PostgreSQL 18** (Banco de dados principal)
- **Redis** (Driver de filas para processamento em background)
- **Docker & Laravel Sail** (Orquestração do ambiente)
- **L5-Swagger** (Documentação OpenAPI 3.0)
- **PHPUnit** (Testes de funcionalidade)

---

## 🛠️ Instalação e Configuração

### Pré-requisitos
- Docker Desktop instalado.
- Git.

### Passos para rodar o projeto

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/CarlosLimaSouza/open-food-parser.git
   cd open-food-parser
   ```

2. **Configure o ambiente:**
   ```bash
   cp .env.example .env
   ```

3. **Instale as dependências do Composer:**
   Se você **não** tem PHP instalado localmente, use este container temporário:
   ```bash
   docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
   ```
   Caso já tenha PHP 8.4+, basta rodar:
   ```bash
   composer install
   ```

4. **Suba o ambiente com Docker Sail:**
   ```bash
   ./vendor/bin/sail up -d
   ```
   *(Se for a primeira vez, o Docker irá baixar as imagens PostgreSQL e Redis. Isso pode levar alguns minutos.)*

5. **Gere a chave da aplicação e rode as migrations:**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```
    **Nota:** Se o comando falhar por permissões. 
    Use a flag `--show` e Copie a chave gerada e cole manualmente no arquivo `.env` na linha `APP_KEY=`.
   ```bash
   ./vendor/bin/sail artisan key:generate --show
   ```
   Depois rode a migration
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

6. **Corrija permissões (se necessário):**
   Em ambientes Windows/WSL, pode ser necessário liberar permissões de escrita:
   ```bash
   docker exec -u 0 open-food-parser-laravel.test-1 chmod -R 777 storage bootstrap/cache
   ```

7. **Execute a primeira importação:**
   ```bash
   ./vendor/bin/sail artisan app:import-products
   ```
   *(Isso importará os primeiros 100 produtos de cada arquivo do Open Food Facts. Certifique-se de que o worker está rodando: `./vendor/bin/sail artisan queue:work`)*

4. **Configuração da API Key:**
   O projeto utiliza um middleware de segurança. A chave padrão definida no `.env` é `fitness_food_secret_key`.
   Todas as requisições para a API devem conter o header:
   `x-api-key: fitness_food_secret_key`

---

## Sistema de Importação (CRON)

O sistema de importação foi projetado para ser eficiente em memória, processando arquivos `.json.gz` via streaming.

O agendamento da importação é realizado via CRON às 03:00 (Horário de Brasília) por padrão. Você pode configurar o horário e o fuso horário no seu `.env`:
- `APP_TIMEZONE=America/Sao_Paulo`
- `IMPORT_SCHEDULE_TIME=03:00`

---

## ⏰ Observação sobre o CRON do Laravel

O agendamento de tarefas do Laravel (schedule) **não executa automaticamente**. É necessário um "gatilho" externo para rodar as tarefas agendadas:

- Em produção, configure o cron do sistema para rodar o comando abaixo a cada minuto:
  ```bash
  * * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
  ```
- No ambiente Docker/Sail, você pode deixar um terminal rodando:
  ```bash
  ./vendor/bin/sail artisan schedule:work
  ```
  Assim, o schedule do Laravel executa as tarefas automaticamente no tempo configurado.

Se rodar apenas `schedule:run` manualmente, o cron só executa naquele instante. Para automação real, use um dos métodos acima.

---

## 📖 Documentação da API (Swagger)

A API possui documentação interativa através do Swagger UI.
- **URL da Documentação:** [http://localhost/docs](http://localhost/docs)
- **Especificação JSON:** [http://localhost/api-docs](http://localhost/api-docs)

---

## 🧪 Testes

Para garantir que tudo está funcionando como esperado, você pode rodar a suíte de testes automatizados:

```bash
./vendor/bin/sail artisan test
```

---




- **Execução manual:**
  Para testar a importação, execute:
  ```bash
  ./vendor/bin/sail artisan app:import-products
  ```
- **Fila de processamento:**
  A importação manda jobs para o Redis. Certifique-se de que o worker está rodando (o Sail já sobe um em background se configurado, ou você pode rodar):
  ```bash
  ./vendor/bin/sail artisan queue:work
  ```
---

## 🧪 Testes

Para rodar os testes automatizados:
```bash
./vendor/bin/sail artisan test
```

---

## 📖 Documentação da API (Swagger)

A documentação completa dos endpoints (OpenAPI 3.0) pode ser acessada em:
`http://localhost/docs`

---

## 🛣️ Endpoints Principais

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/` | Detalhes da API, status do banco e uptime. |
| GET | `/api/products` | Lista produtos (paginado). |
| GET | `/api/products/{code}` | Detalhes de um produto específico. |
| PUT | `/api/products/{code}` | Atualiza dados de um produto. |
| DELETE| `/api/products/{code}` | Altera o status do produto para `trash`. |

---

## ✒️ Autor
Desenvolvido por Carlos Lima de Souza como parte do desafio Coodesh.

---
