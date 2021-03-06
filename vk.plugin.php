<?php namespace vk;

require_once('synchrotalk.connector/connector.php');
require_once('vk.converter.php');
require_once('vk.auth.plugin.php');
require_once(__DIR__.'/vendor/autoload.php');

class vk extends \synchrotalk\connector\connector
{
  private $api;
  private $token;
  private $auth;

  public function __construct()
  {
    $vk = \getjump\Vk\Core::getInstance();

    $this->api = $vk->apiVersion('5.5');
    $this->auth = new auth();
  }

  final public /* auth */ function auth()
  {
    return $this->auth;
  }

  final public function init($config)
  {
    $this->auth->init($config);
  }

  final public /* bool */ function sign_in($token)
  {
    $this->token = $token;

    $this->api->setToken($token);

    return true;
  }

  final public /* token */ function get_token()
  {
    return $this->token;
  }

  private function current_user()
  {
    $raw_data = $this->users([]);

    return reset($raw_data);
  }

  final public /* thread[] */ function threads()
  {
    $threads = $this->api->request("messages.getDialogs")->fetchData();

    $converter = new converter();
    return $converter->bunchof_threads($threads->items);
  }

  final public /* message[] */ function messages( /* string */ $thread_id, /* int */ $skip_pages = 0)
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
    else if (!is_string($to))
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

    $message_id = $this->api->request("messages.send", $bundle)->fetchData();

    return new \synchrotalk\connector\objects\abstract_object($message_id);
  }

  final public /* user */ function user( /* user_id */ $userid )
  {
    if ($userid)
      $userid = [$userid];
    return current($this->users($userid));
  }

  final public /* user[] */ function users( /* user_id[] */ $userids )
  {
    $fields =
    [
      'has_photo',
      'photo_50',
      'photo_200',
      'photo_400_orig',
      'photo_max_orig',
      'domain',
      'last_seen',
      'online',
      'status',
    ];

    $params =
    [
      'fields' => implode(',', $fields),
      'name_case' => 'Nom',
    ];

    if (!empty($userids))
      $params['user_ids'] = implode(',', $userids);

    $users = $this->api->request("users.get", $params)->fetchData();

    $converter = new converter();
    return $converter->bunchof_users($users->items);
  }

  public /* string */ function nickname_to_userid( /* string */ $nickname )
  {
    throw new Exception("VK: Implement nickname_to_userid");
  }


  final public /* string */ function nickname_to_link( /* string */ $nickname )
  {
    $converter = new converter();
    return $converter->nickname_to_link($nickname);
  }
}
