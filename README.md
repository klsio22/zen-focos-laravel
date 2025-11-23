# 🍅 ZenFocos - Pomodoro Task Manager

> **Sistema web para gerenciamento de tarefas com a técnica Pomodoro**

[![Laravel](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-4.0-38B2AC?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=flat-square&logo=javascript)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📖 Sobre a Aplicação

### O que é ZenFocos?

**ZenFocos** é uma aplicação web moderna para gerenciamento de tarefas baseada na **técnica Pomodoro** - um método científico de organização do tempo que alterna períodos de foco intenso (25 minutos) com pequenas pausas.

### Para que serve?

- 🎯 **Organizar tarefas** de forma intuitiva e visual
- ⏱️ **Executar sessões Pomodoro** de 25 minutos com timer interativo
- ⏸️ **Pausar e retomar** sessões sem perder o progresso
- 📊 **Rastrear progresso** com contadores e status visual
- 📱 **Acessar de qualquer dispositivo** com design responsivo

### Público-alvo

- **Profissionais autônomos** que precisam organizar tempo
- **Estudantes** que querem melhorar produtividade
- **Equipes** que usam técnicas de time-boxing
- **Qualquer pessoa** interessada em produtividade e foco

### Como funciona

```
1. Criar tarefa com título e estimar quantos pomodoros vai levar
   ↓
2. Clicar em "Ver Pomodoro" para abrir o timer
   ↓
3. Timer inicia contagem de 25 minutos
   ↓
4. Pode pausar (tempo fica salvo), pular ou completar
   ↓
5. Ao completar, contador é incrementado automaticamente
   ↓
6. Quando completar todos os pomodoros, tarefa fica "Concluída"
```

---

## 🖼️ Screenshots

### Dashboard - Lista de Tarefas
Tarefas organizadas em 3 colunas por status:
- **Pendentes** (cinza) - ainda não iniciadas
- **Em Progresso** (amarelo) - com pomodoros em andamento
- **Concluídas** (verde) - 100% completas com progresso visual

### Timer Focado
- **Timer circular animado** que conta 25 minutos
- **Botões Play/Pause/Pular** para controle
- **Indicador de sessão** (ex: "Sessão 2 de 4")
- **Feedback visual** com dots indicando progresso

### Gerenciamento de Tarefas
- Criar nova tarefa (título + descrição + pomodoros estimados)
- Editar ou deletar tarefas existentes
- Marcar como concluída
- Checkbox rápido para marcar como done

---

## 🚀 Quick Start (Instalação Rápida)

### Pré-requisitos

- **PHP 8.2+** com extensões: `PDO`, `Ctype`, `JSON`, `Mbstring`, `Tokenizer`, `XML`
- **Composer** ([instalação](https://getcomposer.org/download/))
- **Node.js 18+** e **npm** ([instalação](https://nodejs.org/))
- **SQLite** (ou MySQL/PostgreSQL - veja `.env.example`)

### Passo 1: Clonar e instalar dependências

```bash
# Clonar repositório
git clone https://github.com/klsio22/zen-focos-laravel.git
cd zen-focos-laravel

# Instalar dependências PHP
composer install

# Instalar dependências Node.js
npm install
```

### Passo 2: Configurar ambiente

```bash
# Copiar arquivo de configuração
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Criar banco de dados SQLite (automático com padrão)
touch database/database.sqlite

# Executar migrações
php artisan migrate
```

### Passo 3: Compilar assets e rodar servidor

```bash
# Terminal 1: Compilar CSS/JS em tempo real
npm run dev

# Terminal 2: Rodar servidor Laravel
php artisan serve
```

✅ **Aplicação rodando em:** `http://localhost:8000`

---

## 📋 Autenticação de Teste

A aplicação inclui autenticação via Laravel UI. Para testar:

1. Acesse `http://localhost:8000`
2. Clique em **"Register"** para criar conta
3. Preencha email e senha
4. Faça login

**Ou use dados de teste (se seeder estiver configurado):**
```
Email: test@example.com
Senha: password
```

---

## 🎮 Como Usar

### 1️⃣ Criar Tarefa
1. Clique em **"Nova Tarefa"**
2. Preencha:
   - **Título** (obrigatório, máx 255 caracteres)
   - **Descrição** (opcional)
   - **Pomodoros Estimados** (mínimo 1)
3. Clique em **"Salvar"**

### 2️⃣ Iniciar Pomodoro
1. No card da tarefa, clique em **"Ver Pomodoro"**
2. Timer mostra 25:00
3. Clique em **Play** para iniciar contagem regressiva

### 3️⃣ Controlar Timer
- **Pause**: Para temporariamente (tempo fica salvo)
- **Pular**: Registra pomodoro sem esperar 25 min
- **Retomar**: Continua de onde parou
- **Concluir**: Marca tarefa como finalizada

### 4️⃣ Gerenciar Tarefas
- **Editar**: Mude status, descrição, etc
- **Deletar**: Remove tarefa e todas as sessões associadas
- **Checkbox**: Marca como concluída rapidamente

---

## 🛠️ Configurações Avançadas

### Usar banco de dados diferente

Edite `.env`:

```env
# SQLite (padrão)
DB_CONNECTION=sqlite
DB_DATABASE=/caminho/absoluto/para/database.sqlite

# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zenfocos
DB_USERNAME=root
DB_PASSWORD=

# PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=zenfocos
DB_USERNAME=postgres
DB_PASSWORD=
```

Depois rode:
```bash
php artisan migrate
```

### Deploy em Produção

```bash
# Compilar assets otimizados
npm run build

# Limpar cache
php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Migrar com força (cuidado!)
php artisan migrate --force

# Servir com gunicorn ou Apache
# (Configurar .env com APP_ENV=production, APP_DEBUG=false)
```

---

## 🏗️ Estrutura do Projeto

```
zen-focos-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TaskController.php          # CRUD de tarefas
│   │   │   └── PomodoroController.php      # Gerenciamento de sessões
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Task.php
│   │   └── PomodoroSession.php
│   └── Policies/
│       ├── TaskPolicy.php
│       └── PomodoroSessionPolicy.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── app.js                         # Entry point
│   │   ├── timer-store.js                 # Store global
│   │   ├── timer.js                       # Lógica do timer
│   │   └── task-cards.js                  # Sincronização
│   ├── views/
│   │   ├── layouts/app.blade.php          # Layout principal
│   │   ├── tasks/
│   │   │   ├── index.blade.php            # Lista
│   │   │   ├── create.blade.php           # Criar
│   │   │   ├── edit.blade.php             # Editar
│   │   │   └── components/task-card.blade.php
│   │   └── timer/focused.blade.php        # Timer focado
│   └── css/app.css                        # Tailwind v4
├── routes/
│   ├── web.php                            # Rotas principais
│   └── console.php
├── .env.example                           # Template .env
├── PROJECT_MODEL.md                       # Documentação técnica
├── ROTEIRO_VIDEO_DEFESA.md               # Checklist vídeo
└── README.md                              # Este arquivo
```

---

## 🔌 Stack Tecnológico

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| **Backend** | Laravel | 12.0 |
| **Frontend** | Blade Templates | Laravel |
| **Interatividade** | JavaScript Vanilla | ES6+ |
| **Estilização** | Tailwind CSS | 4.0 |
| **Ícones** | Heroicons via Blade UI Kit | - |
| **Build** | Vite | 7.0 |
| **Banco de Dados** | SQLite / MySQL / PostgreSQL | - |
| **Autenticação** | Laravel UI | 4.0 |

---

## 📚 Documentação Adicional

- **`PROJECT_MODEL.md`** - Documentação técnica completa (modelos, controllers, regras de negócio, endpoints)
- **`ROTEIRO_VIDEO_DEFESA.md`** - Roteiro e checklist para vídeo de apresentação (10 minutos)

---

## 🐛 Troubleshooting

### Erro "Class 'PDO' not found"
```bash
# Instale extensões PHP necessárias
php -m | grep -i pdo

# Ubuntu/Debian:
sudo apt-get install php8.2-sqlite php8.2-pdo
```

### Erro de permissões SQLite
```bash
# Permissões para banco de dados
chmod 664 database/database.sqlite
chmod 775 database/
```

### Limpar cache e cache de views
```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

### Assets não carregam (CSS/JS)
```bash
# Recompile com Vite
npm run dev

# Ou verifique se Laravel está servindo corretamente
php artisan serve --host=0.0.0.0 --port=8000
```

### Erro "CSRF token mismatch"
Certifique-se que:
1. `.env` tem `APP_KEY` configurada (rode `php artisan key:generate`)
2. Sessões estão habilitadas
3. Cookie está sendo enviado corretamente

---

## 🤝 Contribuindo

Encontrou um bug ou tem uma sugestão? Abra uma [issue](https://github.com/klsio22/zen-focos-laravel/issues)!

---

## 📄 Licença

MIT License - veja arquivo `LICENSE` para detalhes

---

## 👨‍💻 Autor

**klsio22** - Desenvolvedor

---

## 📞 Suporte

Para dúvidas sobre como usar a aplicação ou reportar problemas, abra uma issue no repositório.

---

**Última atualização:** 23 de novembro de 2025
