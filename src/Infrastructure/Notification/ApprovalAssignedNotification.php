<?php

declare(strict_types=1);

namespace Yammi\Workflow\Infrastructure\Notification;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ApprovalAssignedNotification extends Notification
{
    public function __construct(
        public readonly string $subjectTitle,
        public readonly int $step,
        public readonly string $label,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        /** @var list<string> $channels */
        $channels = (array) config('workflow.notifications.channels', ['database']);

        return $channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->subjectTitle,
            'step' => $this->step,
            'label' => $this->label,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('An approval needs you')
            ->line(sprintf('"%s" is waiting for your "%s" approval (step %d).', $this->subjectTitle, $this->label, $this->step))
            ->line('Open the approvals inbox to approve or reject.');
    }
}
