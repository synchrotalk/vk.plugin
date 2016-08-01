<?php

require_once('synchrotalk.connector/connector.php');
require_once('vendor/autoload.php');

class vk extends connector
{
  private $api;

  public function __construct()
  {
  }

  final public function log_in($username, $password)
  {
    throw new Exception("VK log_in todo");
  }

  final public function sign_in($token)
  {
    throw new Exception("VK sign_in todo");
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
