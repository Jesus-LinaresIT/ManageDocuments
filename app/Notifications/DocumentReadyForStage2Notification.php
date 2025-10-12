<?php

namespace App\Notifications;

use App\Models\ProjectDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReadyForStage2Notification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $projectDocument;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjectDocument $projectDocument)
    {
        $this->projectDocument = $projectDocument;
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
        
        return (new MailMessage)
            ->subject('Documento Listo para Etapa 2 - ' . $documentType->name)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Te informamos que hay un documento listo para revisión en Etapa 2.')
            ->line('**Proyecto:** ' . $project->name)
            ->line('**Documento:** ' . $documentType->name)
            ->line('**Docente:** ' . $project->teacher->name)
            ->line('**Fecha de aprobación Etapa 1:** ' . now()->format('d/m/Y H:i'))
            ->action('Revisar Documento', route('reviews.index'))
            ->line('Por favor, procede con la revisión de proyección social.');
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
            'teacher_name' => $this->projectDocument->project->teacher->name,
            'action' => 'ready_for_stage2',
            'message' => 'El documento "' . $this->projectDocument->documentType->name . '" del proyecto "' . $this->projectDocument->project->name . '" está listo para revisión en Etapa 2.'
        ];
    }
}
