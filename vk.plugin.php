<?php

require_once('synchrotalk.connector/connector.php');
require_once('vendor/autoload.php');

class vk extends connector
{
  private $api;
  private $appid;
  private $secret;

  public function __construct()
  {
    $vk = getjump\Vk\Core::getInstance();

    $this->api = $vk->apiVersion('5.5');
  }

  final public function init($appid, $secret)
  {
    $this->appid = $appid;
    $this->secret = $secret;
  }

  final public function log_in($token, $not_used)
  {
    throw new Exception("VK rules is permitting log_in functionality, use sign_in token");
  }

  public function construct_auth_obj()
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
      ->setSecret($this->secret)
      ->setRedirectUri("https://oauth.vk.com/blank.html");

    return $auth;
  }

  final public function token_request_url()
  {
    $auth = $this->construct_auth_obj();

    return $auth->getUrl();
  }

  final public function code_to_token($code)
  {
    $auth = $this->construct_auth_obj();

    return $auth->getToken($code);
  }

  final public function sign_in($token)
  {
    $this->api->setToken($token);

    return $this->current_user();
  }

  private function current_user()
  {
    return $this->api->request("account.getInfo");
  }

  final public function send_message($to, $what)
  {
    throw new Exception("VK send_message todo");
  }

  final public function fetch_messages($thread)
  {
    throw new Exception("VK fetch_messages todo");
  }

  final public function mark_read($thread)
  {
    throw new Exception("VK mark_read todo");
  }




  final public function threads()
  {
    throw new Exception("VK threads todo");
  }

  final public function contacts()
  {
    throw new Exception("VK contacts todo");
  }
}
