<?php


namespace Auth\Strategy;


use Auth\AuthConfig;
use Auth\DataProvider\AccessApplicationDataProvider;
use iConto\Env;
use iConto\OAuth\OAuth2Storage\iContoServices;
use iConto\Service\Auth\AuthService;
use OAuth2\OAuth2;

use Auth\AuthContext;
use OAuth2\OAuth2ServerException;

class OauthSessionStrategy extends AuthStrategyProto
{
    /**
     * @var AuthService
     */
    private $authService;
    
    /**
     * @var OAuth2
     */
    private $provider;
    
    public function shouldProcess()
    {
        return $this->context->is(AuthContext::AUTH_TOKEN);
    }
    
    public function prepare()
    {
        $this->authService = Env::getServiceContainer()->getService('Auth');
        $this->provider    = new OAuth2(new iContoServices(), [OAuth2::CONFIG_SUPPORTED_SCOPES => \iConto\OAuth\OAuth2::getAllowedScopes()]);
    }
    
    
    public function run()
    {
        $token = $this->context->get(AuthContext::AUTH_TOKEN);
        $resource = $this->context->get(AuthContext::RESOURCE);
        
        try {
            $this->provider->verifyAccessToken($token, $resource);
        
            $accessTokenData = $this->authService->getOAuth2AccessTokenInfo($token);
            
            $this->authStatus = AuthConfig::AUTH_API_AUTHORIZED;
        } catch (OAuth2ServerException $e) {
            // маппинг OAuth исключений на iConto-вские
            switch ($e->getMessage()) {
                case OAuth2::ERROR_INVALID_GRANT:
                    $this->authStatus = AuthConfig::AUTH_API_INVALID_AUTH;
                    break;
                case OAuth2::ERROR_INSUFFICIENT_SCOPE:
                    $this->authStatus = AuthConfig::AUTH_API_FORBIDDEN;
                    break;
                default:
                    $this->authStatus = AuthConfig::AUTH_API_UNAUTHORIZED;
            }
        }
    
        $appKey = isset($accessTokenData['app_key']) ? $accessTokenData['app_key'] : null;
        
        $this->dataProvider = new AccessApplicationDataProvider();
        $this->dataProvider->setToken($appKey);
    }
}