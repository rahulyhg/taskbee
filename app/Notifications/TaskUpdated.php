<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskUpdated extends Notification
{
    use Queueable;

    protected $task;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param $task
     * @param $user
     */
    public function __construct($task, $user)
    {
        $this->task = $task;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
			'member' => "{$this->user->full_name}",
			'message' => "updated {$this->task->name} to {$this->task->status}"
        ];
    }
}
