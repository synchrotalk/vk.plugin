<?php namespace vk;

require_once('synchrotalk.connector/connector.php');
require_once('vendor/autoload.php');

class vk extends synchrotalk\connector\connector
{
  private $api;
  private $token;

  public function __construct()
  {
    $vk = getjump\Vk\Core::getInstance();

    $this->api = $vk->apiVersion('5.5');
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


  final public function message_send($to, $what)
  {
    if (!is_string($what))
      throw new Exception("VK: Attachments for vk not yet implemented");


    $params =
    [
      "message" => $what,
    ];

    if (is_int($to))
      $params["user_id"] = $to;
    else // use nick as destination
      $params["domain"] = $to;

    $message_id = $this->api->request("messages.send", $params)->fetchData();

    return new \synchrotalk\connector\objects\thread($message_id);
  }
}
