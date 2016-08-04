<?php namespace vk;

class converter
{
  public function bunchof_threads($array_of_threads)
  {
    $ret = [];
    foreach ($array_of_threads as $fetched_thread)
      $ret[] = $this->thread($fetched_thread);

    return $ret;
  }

  public function thread($fetched_thread)
  {
    $thread = new \synchrotalk\connector\objects\thread($fetched_thread->user_id);

    $thread->title = '';
    $thread->is_muted = false;

    $thread->users = [];
    $thread->last_messages = $this->bunchof_messages($fetched_thread->items);

    return $thread;
  }

  public function bunchof_messages($array_of_messages)
  {
    $ret = [];
    foreach ($array_of_messages as $fetched_message)
      $ret[] = $this->message($fetched_message);

    return $ret;
  }

  public function message($fetched_message)
  {
    $message = new \synchrotalk\connector\objects\message($fetched_message->id);
    $message->attachements = [];
    $message->text = $item->body;

    $message->owner = new \synchrotalk\connector\objects\owner($item->from_id);
    $message->created = $item->date;

    return $message;
  }
}
