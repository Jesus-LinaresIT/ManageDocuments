<?php

namespace App\Notifications;

use App\Models\ProjectDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $projectDocument;
    protected $stage;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjectDocument $projectDocument, string $stage)
    {
        $this->projectDocument = $projectDocument;
        $this->stage = $stage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->projectDocument->project;
        $documentType = $this->projectDocument->documentType;
        
        $stageText = $this->stage === 'stage1' ? 'Etapa 1 (Revisión Académica)' : 'Etapa 2 (Revisión de Proyección Social)';
        
        return (new MailMessage)
            ->subject('Documento Aprobado - ' . $documentType->name)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Te informamos que tu documento ha sido aprobado.')
            ->line('**Proyecto:** ' . $project->name)
            ->line('**Documento:** ' . $documentType->name)
            ->line('**Etapa:** ' . $stageText)
            ->line('**Fecha de aprobación:** ' . now()->format('d/m/Y H:i'))
            ->action('Ver Proyecto', route('projects.show', $project))
            ->line('¡Felicitaciones por el avance en tu proyecto!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->projectDocument->project_id,
            'project_name' => $this->projectDocument->project->name,
            'document_type' => $this->projectDocument->documentType->name,
            'stage' => $this->stage,
            'action' => 'approved',
            'message' => 'Tu documento "' . $this->projectDocument->documentType->name . '" ha sido aprobado en ' . ($this->stage === 'stage1' ? 'Etapa 1' : 'Etapa 2') . '.'
        ];
    }
}
