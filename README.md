# Gerenciador Logístico

Aplicação PHP nativa para cadastro de produtos, endereços, transportadores, emissão de fretes com múltiplos itens e relatórios diários. A interface utiliza Bootstrap 5.3, Bootstrap Icons e JavaScript ES6.

## Requisitos

- XAMPP com Apache, PHP 8.0 ou superior e MySQL/MariaDB.
- Navegador moderno e acesso à internet para carregar os CDNs do Bootstrap e consultar a API ViaCEP.

## Instalação no XAMPP

1. Clone ou copie a pasta do projeto para `C:\xampp\htdocs\gerenciador-logistico`.
2. No painel do XAMPP, inicie **Apache** e **MySQL**.
3. Abra `http://localhost/phpmyadmin` (ou `http://localhost:8080/phpmyadmin`, conforme a porta configurada no Apache), selecione a opção **Importar** e importe `database/schema.sql`. O script cria o banco `gerenciador_logistico`, as tabelas e dados de demonstração.
4. Se suas credenciais do MySQL não forem as padrão do XAMPP, configure as variáveis de ambiente `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`. Por padrão, a aplicação usa `127.0.0.1:3306`, banco `gerenciador_logistico`, usuário `root` e senha vazia.
5. Acesse `http://localhost/gerenciador-logistico/` (ou `http://localhost:8080/gerenciador-logistico/`).

Se o diretório do projeto tiver outro nome, substitua o segmento da URL pelo nome real da pasta em `htdocs`.

## Estrutura

```text
assets/       CSS e JavaScript do navegador
config/       conexão PDO com MySQL
database/     schema e dados iniciais
includes/     layout compartilhado, funções, sessão e proteção CSRF
pages/        dashboard e telas dos módulos
actions/      processamento POST e exportação CSV
index.php     dashboard
```

## Funcionalidades

- CRUD de produtos, endereços e transportadores; registros relacionados a fretes são protegidos por chaves estrangeiras.
- Consulta de CEP pelo ViaCEP ao sair do campo CEP.
- Emissão de frete com vários produtos, validação no servidor e transação PDO para gravar frete e itens atomicamente.
- Dashboard com indicadores e fretes recentes.
- Relatório por data com peso total, valor dos fretes e exportação CSV com BOM UTF-8 para Excel.
- Consultas parametrizadas, escape de saída HTML e token CSRF nos formulários de alteração.

## Dados de demonstração

O schema inclui produtos, endereços, transportadores e um frete com dois produtos. Os comandos de seed são idempotentes para poder importar novamente sem duplicar os registros de exemplo.

## Observações

- O CSV é protegido contra conteúdo que possa ser interpretado como fórmula por planilhas.
- A aplicação foi projetada para ambiente local de desenvolvimento. Antes de disponibilizar na internet, configure credenciais próprias, HTTPS, logs/monitoramento e políticas de acesso adequadas.
- O arquivo SQL não cria usuário nem altera a senha do MySQL; isso permanece sob controle da configuração do XAMPP.
