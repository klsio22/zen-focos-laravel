# 🎯 ZenFocos - Gerenciador de Tarefas com Técnica Pomodoro

## 📋 Sobre o Projeto

ZenFocos é uma aplicação web desenvolvida em Laravel para gerenciamento de tarefas utilizando a técnica Pomodoro. O sistema permite que você organize suas tarefas, estime o tempo necessário em "pomodoros" (sessões de 25 minutos) e acompanhe seu progresso.

## ✨ Funcionalidades

- ✅ **Autenticação de Usuários** - Sistema completo de login e registro
- ✅ **Gerenciamento de Tasks** - Criar, editar, visualizar e remover tarefas
- ✅ **Técnica Pomodoro** - Sessões de foco de 25 minutos
- ✅ **Acompanhamento de Progresso** - Contador de pomodoros completados
- ✅ **Interface Moderna** - Design responsivo com TailwindCSS
- ✅ **Políticas de Acesso** - Cada usuário acessa apenas suas próprias tarefas

## 🚀 Tecnologias Utilizadas

- **Laravel 12** - Framework PHP
- **Laravel UI** - Sistema de autenticação
- **TailwindCSS** - Framework CSS para estilização
- **SQLite** - Banco de dados (pode ser alterado para MySQL/PostgreSQL)
- **Vite** - Build tool para assets

## 📦 Instalação

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js e NPM

### Passos para Instalação

1. **Clone o repositório ou navegue até a pasta do projeto**

```bash
cd /caminho/para/zen-focos-laravel
```

2. **Instale as dependências do PHP**

```bash
composer install
```

3. **Configure o arquivo .env**

O arquivo `.env` já está configurado para usar SQLite. Se desejar usar MySQL ou PostgreSQL, edite as configurações de banco de dados.

4. **Gere a chave da aplicação** (já foi feito na instalação)

```bash
php artisan key:generate
```

5. **Execute as migrações**

```bash
php artisan migrate
```

6. **Instale as dependências do Node.js**

```bash
npm install
```

7. **Compile os assets**

```bash
npm run build
```

## 🏃 Executando o Projeto

### Modo de Desenvolvimento

Execute o servidor de desenvolvimento:

```bash
php artisan serve
```

A aplicação estará disponível em: **http://localhost:8000**

### Compilando Assets em Modo Watch

Para desenvolvimento contínuo com hot reload:

```bash
npm run dev
```

## 📱 Usando a Aplicação

1. **Registre-se** - Acesse `/register` e crie uma conta
2. **Faça Login** - Entre com suas credenciais
3. **Crie uma Task** - Clique em "Nova Task" e preencha os dados
4. **Inicie um Pomodoro** - Clique em "🍅 Iniciar Pomodoro" na task desejada
5. **Acompanhe seu Progresso** - Veja quantos pomodoros foram completados

## 🗂️ Estrutura do Projeto

```
zen-focos-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TaskController.php
│   │   │   └── PomodoroController.php
│   ├── Models/
│   │   ├── Task.php
│   │   └── PomodoroSession.php
│   └── Policies/
│       ├── TaskPolicy.php
│       └── PomodoroSessionPolicy.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_tasks_table.php
│       └── 2024_01_01_000002_create_pomodoro_sessions_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── tasks/
│           ├── index.blade.php
│           ├── create.blade.php
│           └── edit.blade.php
└── routes/
    └── web.php
```

## 🎨 Conceitos Laravel Implementados

### Módulo 4: Roteamento e Ciclo de Vida
- ✅ Rotas RESTful com `Route::resource()`
- ✅ Rotas personalizadas para sessões Pomodoro
- ✅ Middleware de autenticação
- ✅ Grupos de rotas

### Módulo 5: Views com Blade
- ✅ Template principal (`layouts/app.blade.php`)
- ✅ Diretivas Blade: `@extends`, `@section`, `@yield`
- ✅ Condicionais: `@if`, `@foreach`, `@auth`
- ✅ Componentes e layouts reutilizáveis

### Módulo 6: Estilização
- ✅ TailwindCSS integrado via CDN
- ✅ Design responsivo
- ✅ Componentes estilizados (cards, formulários, botões)

### Módulo 7: Forms e Validação
- ✅ Validação de dados no servidor
- ✅ Exibição de erros de validação
- ✅ Proteção CSRF com `@csrf`
- ✅ Métodos HTTP: POST, PUT, DELETE

### Módulo 8: Autenticação
- ✅ Laravel UI com Bootstrap Auth
- ✅ Sistema de login e registro
- ✅ Middleware `auth` para proteção de rotas
- ✅ Policies para autorização de recursos

## 🔒 Políticas de Segurança

O projeto implementa **Policies** para garantir que:
- Usuários só podem visualizar suas próprias tasks
- Usuários só podem editar suas próprias tasks
- Usuários só podem deletar suas próprias tasks
- Sessões Pomodoro são vinculadas ao usuário autenticado

## 🐛 Debug e Troubleshooting

### Erro de Permissão no SQLite

Se tiver problemas com permissões no arquivo SQLite:

```bash
chmod 664 database/database.sqlite
chmod 775 database/
```

### Limpar Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Recompilar Assets

```bash
npm run build
```

## 📈 Próximas Melhorias

- [ ] Timer visual para sessões Pomodoro
- [ ] Notificações quando o Pomodoro terminar
- [ ] Relatórios e estatísticas de produtividade
- [ ] Categorias/Tags para tarefas
- [ ] Pausas curtas (5 min) e longas (15 min)
- [ ] Sistema de sons/alertas
- [ ] Dark mode

## 📄 Licença

Este projeto foi desenvolvido para fins educacionais.

## 👨‍💻 Desenvolvido com

- ❤️ Laravel
- 🍅 Técnica Pomodoro
- 🎨 TailwindCSS

---

**ZenFocos** - Foque no que importa! 🎯
