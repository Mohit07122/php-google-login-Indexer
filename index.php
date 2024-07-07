<?php

include_once 'OAuthGoogle.php';
include_once 'UrlIndexer.php';

$idCliente = 'seu-id-cliente';
$segredoCliente = 'seu-segredo-cliente';

$oauthGoogle = new OAuthGoogle($clientId, $clientSecret);

if (isset($_GET['code'])) {
    $codigo = $_GET['code'];
    $accessToken = $oauthGoogle->getAccessToken($codigo);
    if ($accessToken) {
        echo "Token de acesso obtido com sucesso!";
    } else {
        echo "Erro ao obter o token de acesso.";
    }
} else {
    $urlAutenticacao = $oauthGoogle->getUrlAutenticacao(['https://www.googleapis.com/auth/indexing']);
    header('Location: ' . $urlAutenticacao);
    exit;
}

$urls = [
    'site1.com.br',
    'site2.com.br',
];

$urlIndexer = new UrlIndexer($oauthGoogle);

$resultado = $urlIndexer->enviarUrlsParaIndexacao($urls);

foreach ($resultado as $res) {
    echo "URL: " . $res['url'] . "<br>";
    echo "Sucesso: " . ($res['sucesso'] ? 'Sim' : 'Não') . "<br>";
    echo "Mensagem: " . $res['mensagem'] . "<br>";
    if (isset($res['resposta'])) {
        echo "Resposta: " . $res['resposta'] . "<br>";
    }
    echo "<hr>";
}
