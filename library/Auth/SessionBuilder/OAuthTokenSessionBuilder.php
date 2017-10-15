<?php


namespace Auth\SessionBuilder;


use Mu\Env;
use Mu\Exception\Auth\NoPrivileges;
use Mu\Exception\Auth\OAuth2InvalidGrant;
use Mu\Exception\Auth\OAuth2NoPrivileges;
use Mu\Interfaces\SessionInterface;
use Mu\OAuth\OAuth2Storage\iContoServices;
use Mu\Service\Auth\AuthService;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

class OAuthTokenSessionBuilder extends SessionBuilderProto
{
    /**
     * @var AuthService
     */
    private $authService;
    
    /**
     * @var OAuth2
     */
    private $provider;
    
    public function prepare()
    {
        $this->authService = Env::getServiceContainer()->getService('Auth');
        $this->provider    = new OAuth2(new iContoServices(), [OAuth2::CONFIG_SUPPORTED_SCOPES => \Mu\OAuth\OAuth2::getAllowedScopes()]);
    }
    
    /**
     * @param SessionInterface $session
     * @param                  $token
     * @param                  $resource
     *
     * @throws NoPrivileges
     * @throws OAuth2InvalidGrant
     * @throws OAuth2NoPrivileges
     * 
     * @return void
     */
    public function buildSession(SessionInterface $session, $token, $resource)
    {
        if (!$token) {
            return ;
        }
        
        $accessTokenData = [];
        
        try {
            $this->provider->verifyAccessToken($token, $resource);
            
            $accessTokenData = $this->authService->getOAuth2AccessTokenInfo($token);
            
            // Занесение инфы о пользователе в сессию для контроллера и плагинов
            if (!empty($accessTokenData) && !empty($accessTokenData['user_id'])) {
                $session->setUserId($accessTokenData['user_id']);
            }
            
        } catch (OAuth2ServerException $e) {
            // маппинг OAuth исключений на iConto-вские
            switch ($e->getMessage()) {
                case OAuth2::ERROR_INVALID_GRANT:
                    throw new OAuth2InvalidGrant($e->getMsg());
                    break;
                case OAuth2::ERROR_INSUFFICIENT_SCOPE:
                    throw new OAuth2NoPrivileges();
                    break;
                default:
                    throw new NoPrivileges($e->getMsg());
            }
        }
        
        $appKey = isset($accessTokenData['app_key']) ? $accessTokenData['app_key'] : null;
        if ($appKey) {
            $app = $this->authService->getAccessApplicationByAppKey($appKey);
            
            if (!empty($app)) {
                $session->setAppInfo($app->asArray());
            }
        }
    }
}