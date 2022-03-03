<?php

namespace Rkesa\Project\Notifications;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Rkesa\Project\Models\Project;
use Rkesa\Service\Models\Service;

class ProjectChanged extends Notification
{
    use Queueable;

    public $project;
    public $user;
    public $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project, User $user, string $type)
    {
        $this->project = $project;
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'left_part' => trans('project.'.$this->type.'_notification', ['project' => $this->project->name_with_link]),
            'right_part' => trans('template.User_changed_project', ['initiator' => $this->user->name]),
            'created_at' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'project_id' => $this->project->id,
            'initiator_user_id' => $this->user->id,
            'type' => $this->type
        ];
    }

    public static function fromDatabase($not)
    {
        $project = Project::find($not->data['project_id']);
        $initiator = User::withTrashed()->find($not->data['initiator_user_id']);
        $not->left_part = trans('project.'.$not->data['type'].'_notification', ['project' => $project->name_with_link]);
        $not->right_part = trans('template.User_changed_project', ['initiator' => $initiator->name]);
        return $not;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
//    public function toArray($notifiable)
//    {
//        return [
//            //
//        ];
//    }
}
