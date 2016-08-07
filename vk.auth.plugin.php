<?php namespace vk;

require_once('synchrotalk.connector/auth.php');
require_once(__DIR__.'/vendor/autoload.php');

class auth extends \synchrotalk\connector\auth
{
  public $token;
  private $appid;
  private $secret;

  final public function init($config)
  {
    $this->appid = $config['appid'];
    $this->appsecret = $config['appsecret'];
  }

  final public function sign_in( /* string */ $token)
  {
    $this->token = $token;
  }

  final public /* string[] */ function redirect_auth_requirments()
  {
    $auth = $this->construct_request_obj();

    return
    [
      [
        'type' => 'modal',
        'data' => 'Please authorize access and then give us code. This is the only way to send personal messages from your account. Fear not!',
      ],
      [
        'type' => 'redirect',
        'data' => $auth->getUrl(),
      ],
    ];
  }

  private function construct_request_obj()
  {
    $scope =
    [
      'messages',
      'offline',
    ];

    $auth = \getjump\Vk\Auth::getInstance();

    $auth
      ->setAppId($this->appid)
      ->setScope(implode(',', $scope))
      ->setSecret($this->appsecret)
      ->setRedirectUri("https://oauth.vk.com/blank.html");

    return $auth;
  }

  final public /* string */ function redirect_auth_question($requirments)
  {
    return
    [
      [
        'type' => 'form',
        'data' =>
        [
          [
            'type' => 'field',
            'name' => 'code',
          ],
        ],
      ],
    ];
  }

  final public /* token */ function redirect_auth_answer(/* string[] */ $requirments)
  {
    $code = $requirments->code;

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
