# sgeadv
Sistema de Gestão de Escritório de Advocacia (PHP)

Resumo
-----
Aplicação para gestão de processos, pessoas, tarefas, audiências e movimentações financeiras em um escritório de advocacia.

Principais recursos
-------------------
- Gestão de pessoas (clientes, advogados, funcionários)
- Processos, andamentos e documentos
- Agendamento de audiências e participantes
- Tarefas e atribuições por usuário
- Controle básico de permissões por grupo

Requisitos
----------
- PHP 7.4+ (extensões PDO, mbstring, json, fileinfo)
- MySQL 8.0 (testado) ou MariaDB compatível
- Docker (opcional, recomendado para setup rápido)

Como iniciar (Docker)
---------------------
Se o projeto usa docker-compose:

1. Iniciar containers:

   docker-compose up -d

2. Verificar containers em execução:

   docker container ls

Importando o banco (seed)
-------------------------
Seed combinado (schema + dados de teste): `docker\mysql\init\seed-all.sql`.

Opções:
- Importar diretamente no host:

  mysql -u root -p < docker\mysql\init\seed-all.sql

- Usando o container MySQL em execução (ex.: `sgeadv-db-1`):

  docker exec -i sgeadv-db-1 mysql advocacia < "E:\Projetos\sgeadv\docker\mysql\init\seed-all.sql"

  Se for necessário informar senha do root:

  type "E:\Projetos\sgeadv\docker\mysql\init\seed-all.sql" | docker exec -i sgeadv-db-1 mysql -u root -p'YOUR_ROOT_PASSWORD' advocacia

Credenciais de teste
--------------------
- Usuário: admin@advocacia.local
- Senha: 123456

Onde estão os arquivos importantes
---------------------------------
- Schema: App/Database/advocacia-mysql.sql
- Seed combinado: docker/mysql/init/seed-all.sql
- Código PHP principal: App/ e Lib/

Logs e debugging
----------------
- PHP container logs:

  docker logs -f sgeadv-php-1

- MySQL container logs:

  docker logs -f sgeadv-db-1

Problemas comuns
----------------
- Erro "Class 'Auth' not found": algumas classes são carregadas no namespace global. Aplicada correção em `Lib/Advogado/Core/MenuBuilder.php` para referenciar `\Auth`. Caso ocorram erros similares, reiniciar o container PHP:

  docker restart sgeadv-php-1

- Permissões de arquivos (uploads/fotos): garanta que o diretório `App/Uploads` e `App/Images/Fotos` sejam graváveis pelo usuário do servidor web.

Executar testes / validar
------------------------
- Não existem testes automatizados incluídos. Para validar manualmente, importe o seed e acesse a aplicação em http://localhost:8080 (ajuste host/porta conforme seu docker-compose).

Contribuindo
------------
Abra issues ou PRs com pequenas mudanças. Para mudanças de código, siga o padrão atual de código e rode a aplicação localmente para verificar.

Ajuda
-----
.

