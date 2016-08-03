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

    return $auth->getToken($code)->token;
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

  final public function fetch_messages($thread_id)
  {
    $thread = $this->api->request("messages.getHistory",
      [
        "user_id" => $thread_id,
      ])->fetchData();

    return $thread_recognize($thread, $thread_id);
  }

  private function thread_recognize($thread, $thread_id = 0)
  {
    return
    [
      "id" => $thread_id,
      "title" => "TODO",
      "muted" => false,
      "items" => array_map(function($item)
      {
        return
        [
          "id" => $item->id,
          "type" => "text",
          "text" => $item->body,
          "media" => null,
          "author" => $item->from_id,
          "snap" => $item->date,
        ];
      }, $thread->items),

      "users" => "TODO",

      "snap" => "TODO",
      "seen" => "TODO",
    ];
  }

  final public function mark_read($thread)
  {
    throw new Exception("VK mark_read todo");
  }


  final public function threads()
  {
    $threads = $this->api->request("messages.getDialogs")->fetchData();

    $inbox = [];

    $inbox['unseen'] = -1; // TODO
    $inbox['last_snap'] = 0; // TODO

    $inbox['threads'] = array_map(function($thread)
    {
      return
      [
        "id" => $thread->user_id,
        "title" => "TODO",
        "muted" => false,
        "items" => array_map(function($item)
        {
          return
          [
            "id" => $item->id,
            "type" => "text",
            "text" => $item->body,
            "media" => null,
            "author" => $item->from_id,
            "snap" => $item->date,
          ];
        }, $thread->items),

        "users" => "TODO",

        "snap" => "TODO",
        "seen" => "TODO",
      ];
      return $this->ThreadRecognize($thread);
    }, $threads->items);

    return $inbox;
  }

  final public function contacts()
  {
    throw new Exception("VK contacts todo");
  }
}
