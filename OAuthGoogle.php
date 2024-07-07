<?php

class OAuthGoogle
{
    private $idCliente;
    private $segredoCliente;
    private $uriRedirecionamento;
    private $endpointToken = 'https://accounts.google.com/o/oauth2/token';
    private $urlInfoUsuario = 'https://www.googleapis.com/oauth2/v1/userinfo';
    private $arquivoToken = 'token.json';

    public function __construct($idCliente, $segredoCliente, $uriRedirecionamento = null)
    {

        $this->idCliente = $idCliente;
        $this->segredoCliente = $segredoCliente;
        if ($uriRedirecionamento === null) {
            $this->uriRedirecionamento = $this->getUriAtual();
        } else {
            $this->uriRedirecionamento = $uriRedirecionamento;
        }
    }

    public function getUrlAutenticacao($scopes = array())
    {
        $defaultScopes = array('email', 'profile');
    
        $scopes = array_merge($defaultScopes, $scopes);
    
        $queryParams = array(
            'client_id' => $this->idCliente,
            'redirect_uri' => $this->uriRedirecionamento,
            'scope' => implode(' ', $scopes),
            'response_type' => 'code'
        );
    
        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($queryParams);
    }
    
    public function getAccessToken($codigo = null)
    {
        $token = $this->getTokenFromFile();
        if ($token && $this->isTokenValid($token)) {
            return $token['access_token'];
        } elseif ($codigo) {
            $params = array(
                'code' => $codigo,
                'client_id' => $this->idCliente,
                'client_secret' => $this->segredoCliente,
                'redirect_uri' => $this->uriRedirecionamento,
                'grant_type' => 'authorization_code'
            );

            $token = $this->getToken($params);

            $erro = $token['error'] ?? '';

            switch ($erro) {
                case 'invalid_grant':
                    header("Location: ./"); 
                     break;
                case 'redirect_uri_mismatch':
                    trigger_error("Erro: URI de redirecionamento não corresponde.", E_USER_ERROR);
                    break;
            }



            if ($token && isset($token['access_token'])) {
                $this->salvarTokenEmArquivo($token);
                return $token['access_token'];
            }
        }

        return $token;
    }

    public function getInfoUsuario($accessToken)
    {
        $infoUsuario = file_get_contents($this->urlInfoUsuario . '?access_token=' . $accessToken);
        return json_decode($infoUsuario, true);
    }

    private function isTokenValid($token)
    {
        if (isset($token['expires_at'])) {
            $expiresAt = strtotime($token['expires_at']);
            return $expiresAt > time();
        }
        return false;
    }

    private function getToken($params)
    {
        $curl = curl_init($this->endpointToken);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        $tokenInfo = json_decode($response, true);

        if (isset($tokenInfo['access_token'])) {
            $tokenInfo['expires_at'] = date('Y-m-d H:i:s', time() + $tokenInfo['expires_in']);
        }

        return $tokenInfo;
    }

    private function getTokenFromFile()
    {
        if (file_exists($this->arquivoToken)) {
            $tokenJson = file_get_contents($this->arquivoToken);
            return json_decode($tokenJson, true);
        }
        return null;
    }

    private function salvarTokenEmArquivo($token)
    {
        $tokenJson = json_encode($token);
        file_put_contents($this->arquivoToken, $tokenJson);
    }

    private function getUriAtual()
    {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $uriAtual = $protocolo . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $partes = parse_url($uriAtual);
        return $partes['scheme'] . '://' . $partes['host'] . $partes['path'] ;
    }
}
