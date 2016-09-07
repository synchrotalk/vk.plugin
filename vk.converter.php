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

    // I am checked twice. For single user it is '...'
    $thread->title = null;
    $thread->is_muted = false;

    $thread->users = $this->bunchof_owners([$fetched_thread->user_id]);
    $thread->owner = $this->owner($fetched_thread->user_id);

    $my_id = 0; // TODO: Fill with real id

    $virtual_message =
    [
      'body' => $fetched_thread->body,
      'from_id' => $fetched_thread->out
        ? $my_id : $fetched_thread->user_id,
      'date' => $fetched_thread->date,
    ];

    $thread->last_messages =
      $this->bunchof_messages([(object)$virtual_message]);
    $thread->updated = $thread->last_messages[0]->created;

    return $thread;
  }

  private function thread_multiuser($fetched_thread)
  {
    $thread = new \synchrotalk\connector\objects\thread('#'.$fetched_thread->chat_id);

    $thread->title = $fetched_thread->title;
    $thread->is_muted = false;

    $thread->users = $this->bunchof_owners($fetched_thread->chat_active);
    $thread->owner = $this->owner($fetched_thread->admin_id);

    $my_id = 0; // TODO: Fill with real id

    $virtual_message =
    [
      'body' => $fetched_thread->body,
      'from_id' => $fetched_thread->out
        ? $my_id : $fetched_thread->user_id,
    ];

    $thread->last_messages =
      $this->bunchof_messages([(object)$virtual_message]);
    $thread->updated = $thread->last_messages[0]->created;

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
    $message->text = $fetched_message->body;

    $message->owner = $this->owner($fetched_message->from_id);
    $message->created = $fetched_message->date;

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

  public function bunchof_users($array_of_users)
  {
    $ret = [];
    foreach ($array_of_users as $fetched_user)
      $ret[] = $this->user($fetched_user);

    return $ret;
  }

  public function user($fetched_user)
  {
    $user = new \synchrotalk\connector\objects\user($fetched_user->id);

    $user->name =
    [
      $fetched_user->first_name,
      $fetched_user->last_name,
    ];

    $user->nickname = $fetched_user->domain;

    $user->avatars =
    [
      '50x50' => $fetched_user->photo_50,
      '200x200' => $fetched_user->photo_200,
      '400x400' => $fetched_user->photo_400_orig,
      '1000x1000' => $fetched_user->photo_max_orig,
    ];

    $user->online = !!$fetched_user->online;
    $user->status = $fetched_user->status;

    $platforms =
    [
      1 => 'mobile web',
      2 => 'mobile ios',
      3 => 'ios',
      4 => 'mobile linux',
      5 => 'mobile windows',
      6 => 'windows',
      7 => 'web',
    ];
    $user->platform = $platforms[$fetched_user->last_seen->platform];

    $user->updated = $fetched_user->last_seen->time;

    return $user;
  }
}
