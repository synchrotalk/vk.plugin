<?php namespace vk;

require_once('synchrotalk.connector/auth.php');
require_once('vendor/autoload.php');

class auth extends synchrotalk\connector\auth
{
  public $token;
  private $appid;
  private $secret;

  final public function init($config)
  {
    $this->appid = $config['appid'];
    $this->appsecret = $config['appsecret'];
  }

  final public /* user */ function sign_in( /* string */ $token)
  {
    $this->token = $token;
  }

  final public /* string[] */ function redirect_auth_requirments()
  {
    return [];
  }

  private function construct_request_obj()
  {
    $scope =
    [
      'messages',
      'offline',
    ];

    $auth = getjump\Vk\Auth::getInstance();

    $auth
      ->setAppId($this->appid)
      ->setScope(implode(',', $scope))
      ->setSecret($this->appsecret)
      ->setRedirectUri("https://oauth.vk.com/blank.html");

    return $auth;
  }

  final public /* string */ function redirect_auth_question($requirments)
  {
    $auth = $this->construct_request_obj();

    return $auth->getUrl();
  }

  final public /* token */ function redirect_auth_answer(/* string[] */ $requirments)
  {
    $auth = $this->construct_request_obj();

    return $auth->getToken($code)->token;
  }

  public /* string */ function preferred_authtype()
  {
    return 'redirect';
  }

  public function token()
  {
    return $this->token;
  }
}
