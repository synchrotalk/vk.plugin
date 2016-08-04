<?php namespace vk;

require_once('synchrotalk.connector/connector.php');
require_once('vk.converter.php');
require_once('vendor/autoload.php');
require_once('vk.auth.php');

class vk extends synchrotalk\connector\connector
{
  private $api;
  private $token;

  public function __construct()
  {
    $vk = getjump\Vk\Core::getInstance();

    $this->api = $vk->apiVersion('5.5');
  }

  final public /* auth */ function auth()
  {
    return new auth();
  }

  final public function init($config)
  {}

  final public /* user */ function sign_in($token)
  {
    $this->token = $token;

    return $this->current_user();
  }

  private function current_user()
  {
    return $this->api->request("account.getInfo")->fetchData();
  }

  final public /* thread[] */ function threads()
  {
    $threads = $this->api->request("messages.getDialogs")->fetchData();

    $converter = new converter();
    return $converter->bunchof_threads($threads->items);
  }

  public /* message[] */ function messages( /* string */ $thread_id, /* int */ $skip_pages = 0)
  {
    $page_size = 30;

    $thread = $this->api->request("messages.getHistory",
      [
        "user_id" => $thread_id,
        "count" => $page_size,
        "offset" => $page_size * $skip_pages,
      ])->fetchData();

    $converter = new converter();
    return $converter->bunchof_messages($thread->items);
  }

  final public /* object */ function message_send_first( /* user_id */ $to,  /* string or message */ $what )
  {
    return parent::message_send($to, $what);
  }

  final /* object */ public function message_send($to, $what)
  {
    return $this->message_send_constructed($what, $this->threadid_to_address($to));
  }

  private function threadid_to_address($to)
  {
    $params = [];

    if (is_int($to))
      $params["user_id"] = $to;
    else (!is_string($to))
      throw new Exception("VK: Unrecognizible thread id");
    else if ($to[0] == '#') // special symbol for group chats
      $params["chat_id"] = substr($to, 1);
    else // use nick as destination
      $params["domain"] = $to;

    return $params;
  }

  private function message_send_constructed($what, $address)
  {
    if (!is_string($what))
      throw new Exception("VK: Attachments not yet implemented");

    $params =
    [
      "message" => $what,
    ];

    $bundle = array_merge($params, $address);

    $message_id = $this->api->request("messages.send", $params)->fetchData();

    return new \synchrotalk\connector\objects\thread($message_id);
  }

  final public /* user */ function user( /* user_id */ $userid )
  {
    return current($this->users([$userid]));
  }

  final public /* user[] */ function users( /* user_id[] */ $userids )
  {
    $fields =
    [
      'has_photo',
      'photo_50',
      'photo_200',
      'domain',
      'last_seen',
      'online',
      'status',
    ];

    $params =
    [
      'user_ids' => implode(',', $userids),
      'fields' => implode(',', $fields),
      'name_case' => 'Nom',
    ];

    $users = $this->api->request("users.get", $params)->fetchData();

    $converter = new converter();
    return $converter->bunchof_users($users);
  }
}
