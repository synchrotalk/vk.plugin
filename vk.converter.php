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

  // Refactor
  public function thread($fetched_thread)
  {
    if (isset($fetched_thread->chat_id))
      return $this->thread_multiuser($fetched_thread);
    return $this->thread_singleuser($fetched_thread);
  }

  private function thread_singleuser($fetched_thread)
  {
    $thread = new \synchrotalk\connector\objects\thread($fetched_thread->user_id);

    $thread->title = '';
    $thread->is_muted = false;

    $thread->users = $this->bunchof_owners([$fetched_thread->user_id]);
    $thread->owner = $this->owner($fetched_thread->user_id);
    $thread->last_messages = $this->bunchof_messages($fetched_thread->items);

    return $thread;
  }

  private function thread_multiuser($fetched_thread)
  {
    $thread = new \synchrotalk\connector\objects\thread('#'.$fetched_thread->chat_id);

    $thread->title = $fetched_thread->title;
    $thread->is_muted = false;

    $thread->users = $this->bunchof_owners($fetched_thread->chat_active);
    $thread->owner = $this->owner($fetched_thread->admin_id);
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

    $message->owner = $this->owner($item->from_id);
    $message->created = $item->date;

    return $message;
  }

  public function bunchof_owners($array_of_owners)
  {
    $ret = [];
    foreach ($array_of_owners as $fetched_owner)
      $ret[] = $this->owner($fetched_owner);

    return $ret;
  }

  public function owner($fetched_owner)
  {
    return new \synchrotalk\connector\objects\owner($fetched_owner);
  }
}
