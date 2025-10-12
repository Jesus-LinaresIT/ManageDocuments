<?php

namespace App\Notifications;

use App\Models\ProjectDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentDeniedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $projectDocument;
    protected $stage;
    protected $observation;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProjectDocument $projectDocument, string $stage, string $observation)
    {
        $this->projectDocument = $projectDocument;
        $this->stage = $stage;
        $this->observation = $observation;
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
            ->subject('Documento Requiere Correcciones - ' . $documentType->name)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Te informamos que tu documento requiere correcciones antes de ser aprobado.')
            ->line('**Proyecto:** ' . $project->name)
            ->line('**Documento:** ' . $documentType->name)
            ->line('**Etapa:** ' . $stageText)
            ->line('**Observaciones del revisor:**')
            ->line($this->observation)
            ->line('**Fecha de revisión:** ' . now()->format('d/m/Y H:i'))
            ->action('Ver Proyecto y Corregir', route('projects.docs', $project))
            ->line('Por favor, revisa las observaciones y sube una nueva versión del documento.');
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
            'action' => 'denied',
            'observation' => $this->observation,
            'message' => 'Tu documento "' . $this->projectDocument->documentType->name . '" requiere correcciones en ' . ($this->stage === 'stage1' ? 'Etapa 1' : 'Etapa 2') . '.'
        ];
    }
}
