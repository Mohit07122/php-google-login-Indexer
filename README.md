# Google Indexer PHP

Este projeto em PHP facilita a indexação rápida de URLs no Google utilizando OAuth para autenticação. Ideal para automatizar o envio de URLs para indexação no Google Search Console.

## Funcionalidades

- Autenticação com OAuth para obter tokens de acesso.
- Envio eficiente de múltiplas URLs para indexação.
- Feedback detalhado sobre o resultado da operação de indexação.

## Configuração

1. Clone o repositório para o seu ambiente local:

   ```bash
   git clone https://github.com/YesIAmJustLearner/php-google-login-Indexer.git
   cd php-google-login-Indexer
   ```

2. Configure suas credenciais de cliente OAuth no arquivo `OAuthGoogle.php`.
3. Certifique-se de ter configurado um projeto no [Google Cloud Console](https://console.cloud.google.com/).
4. Ative a API de Indexação de Pesquisa (Google Web Search Indexing) para o seu projeto.

## Como Usar

1. Execute o script PHP em seu servidor.
2. O script redireciona para a página de autenticação do Google.
3. Após autorização, as URLs listadas em `$urls` são enviadas para indexação.
4. Resultados são exibidos indicando sucesso ou falha para cada URL.

## Requisitos

- PHP 7.0 ou superior
- Extensão cURL habilitada
- Projeto configurado no Google Cloud Console
- API de Indexação de Pesquisa (Google Web Search Indexing) ativada para o projeto
