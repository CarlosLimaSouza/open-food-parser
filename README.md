# Open Food Facts Parser API

Uma API RESTful desenvolvida para facilitar a curadoria e revisão de dados nutricionais do projeto Open Food Facts pela equipe da Fitness Foods LC.

Este é um desafio técnico (challenge) da [Coodesh](https://coodesh.com/).

---

## Como eu vou resolver isso: Meu plano

Esse aqui é o meu roteiro (e a documentação da minha linha de raciocínio). Vou seguir as regras usando a stack mais proxima possivel da vaga. Pode ser que eu tenha que revisar o plano depois, mas já é bom ter algo para usar como uma espécie de fluxograma de como vou abordar o problema.

---

### A Stack que eu escolhi
- **Linguagem:** PHP 8.3
- **Framework:** Laravel 11
- **Banco de Dados:** PostgreSQL 16
- **Cache/Fila:** Redis (Para eu não travar o CRON)
- **Container:** Docker com Laravel Sail (para eu não ter dor de cabeça com ambiente)
- **Testes:** PHPUnit

---

### 📝 O que eu vou fazer (Passo a Passo)

#### Passo 1: Preparando o meu terreno (Setup)
Antes de começar a codar, eu preciso deixar o ambiente pronto.
- **Minha ideia:** Vou usar o Docker para garantir que tudo funcione igual no meu PC e no deploy.
- **Detalhe:** Vou configurar o Laravel para já reconhecer o banco e avisar que o Redis vai cuidar das filas pesadas que eu vou criar depois.

#### Passo 2: Como eu vou guardar esses dados?
Agora eu foco no banco. Preciso criar uma estrutura que aceite o JSON deles, mas com os meus campos extras: quero saber a hora exata que importei e o status (se está em rascunho, publicado ou no lixo).
Preciso configurar uma tabela de logs , registra cada tentativa de importação (se deu problema, quantos itens vieram,etc).
- **Lembrete:** Não vou deletar nada de verdade agora. Se eu precisar apagar algo, só mudo o status para "trash".
- **Para depois:** Se esse banco explodisse de tamanho, como eu iria manter a busca rápida? Por enquanto, vou manter simples com índices básicos, mas é algo a se pensar.

#### Passo 3: Encarando a importação (A parte chata)
Aqui é o maior desafio. Vou ter que baixar arquivos gigantes todo dia.
Eu fiz um teste inicial e vi que o arquivo compactado já passa dos 55MB, o que significa que descompactado ele deve bater quase 1GB de puro texto.
- **Minha estratégia:** Não vou tentar ler tudo de uma vez para não fritar o servidor. Vou ler em pedaços e salvar só os primeiros 100 de cada arquivo.
- **Meu Plano B:** Vou deixar para detalhar as filas e as tentativas de erro quando eu estiver com a mão na massa, porque sei que isso ai pode complicar e aparecer novas necessidade que não pensei agora. 
Por enquanto o que eu ja sei é que se o site deles cair ou o download falhar, vou usar o sistema de logs que planejei no Passo 2 para me avisar. Se der erro, o Job volta para a fila para tentar de novo (retry), assim não perco o dado.

#### Passo 4: Expondo meus dados (Criando a API)
Vou fazer o básico:
- Uma rota inicial só para eu checar a saúde do sistema.
- A lista de produtos (com paginação, porque além de boas práticas e eu não sou maluco de carregar tudo de uma vez).
- Os jeitos de eu ver, editar e "esconder" cada produto.
- **Lembrete:** Usar o SKU do produto e não o ID autoincrement nas rotas , já que o desafio foca no campo 'code'.

#### Passo 5: Segurança e o meu manual
Vou colocar uma chave (API Key) na porta de entrada. 
- Vou criar o manual da API usando o padrão OpenAPI 3.0 (Swagger). É um diferencial da vaga (e de quebra, já deixo documentado como usar a chave de segurança).

#### Passo 6: Check-up final (Será que funcionou?)
Antes de dar como finalizado, vou testar tudo. Vou ver se minha chave bloqueia intrusos, se o produto realmente vai para o lixo quando eu mando e se os dados estão consistentes.
- Se eu encontrar erro, eu paro, respiro (vou passear com os cachorros) e conserto. O foco é eu entregar um núcleo sólido, e o prazo é mais que o suficiente para não me afobar.

---

### ✅ Meu Checklist
- [ ] Docker rodando redondo com Postgres e Redis.
- [ ] Estrutura do banco seguindo o modelo que eu planejei.
- [ ] Meu CRON trabalhando em silêncio via filas.
- [ ] Limite de 100 itens sendo respeitado (não posso esquecer!).
- [ ] Minha API respondendo JSON bonitinho.
- [ ] Todos os meus testes passando com um `php artisan test`.

--- 

## 🚀 Como instalar e usar 

**Pré-requisitos:** Docker Desktop instalado.

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/CarlosLimaSouza/open-food-parser.git
   cd open-food-parser
   ```

2. **Suba o ambiente (Docker):**
   ```bash
   ./vendor/bin/sail up -d
   ```

3. **Instale as dependências:**
   ```bash
   ./vendor/bin/sail composer install
   ```

4. **Prepare o Banco de Dados:**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

5. **Acesse a API:**
   - Documentação (Swagger): `http://localhost/api/documentation`
   - Testes: `./vendor/bin/sail artisan test`

---
