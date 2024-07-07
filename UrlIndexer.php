<?php

class UrlIndexer
{
    private $urlApi = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    private $oauthGoogle;

    public function __construct(OAuthGoogle $oauthGoogle)
    {
        $this->oauthGoogle = $oauthGoogle;
    }

    public function enviarUrlsParaIndexacao(array $urls)
    {
        if (count($urls) > 200) {
            return ['erro' => 'O número de URLs excede o limite de 200.'];
        }

        $tokenDeAcesso = $this->oauthGoogle->getAccessToken();

        if ($tokenDeAcesso) {
            $resultado = [];

            foreach (array_chunk($urls, 10) as $loteDeUrls) {
                foreach ($loteDeUrls as $url) {
                    $dados = [
                        'url' => $url,
                        'type' => 'URL_UPDATED'
                    ];

                    $resposta = $this->fazerRequisicaoHttp($this->urlApi, $tokenDeAcesso, $dados);

                    if ($resposta) {
                        $dadosDaResposta = json_decode($resposta, true);

                        if (isset($dadosDaResposta['error'])) {
                            $resultado[] = [
                                'url' => $url,
                                'sucesso' => false,
                                'mensagem' => 'Erro ao enviar solicitação de indexação: ' . $dadosDaResposta['error']['message']
                            ];
                        } else {
                            $resultado[] = [
                                'url' => $url,
                                'sucesso' => true,
                                'mensagem' => 'Solicitação de indexação enviada com sucesso.',
                                'resposta' => $resposta
                            ];
                        }
                    } else {
                        $resultado[] = [
                            'url' => $url,
                            'sucesso' => false,
                            'mensagem' => 'Erro ao enviar solicitação de indexação: resposta vazia.'
                        ];
                    }
                }
            }

            return $resultado;
        } else {
            return ['erro' => 'Erro ao obter o token de acesso.'];
        }
    }

    private function fazerRequisicaoHttp($url, $tokenDeAcesso, $dados)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $tokenDeAcesso,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));

        $resposta = curl_exec($ch);
        curl_close($ch);

        return $resposta;
    }
}
