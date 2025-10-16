<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AuditController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('view.reports');

        $query = AuditLog::with('user');

        // Aplicar filtros
        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }
        if (request('action')) {
            $query->where('action', request('action'));
        }
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Datos para filtros
        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit.index', compact('auditLogs', 'users', 'actions'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view.reports');

        $auditLog->load('user');

        return view('audit.show', compact('auditLog'));
    }

    public function export()
    {
        $this->authorize('view.reports');

        $query = AuditLog::with('user');

        // Aplicar los mismos filtros que en index
        if (request('user_id')) {
            $query->where('user_id', request('user_id'));
        }
        if (request('action')) {
            $query->where('action', request('action'));
        }
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit_log_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($auditLogs) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, ['Fecha', 'Usuario', 'Acción', 'Metadatos']);

            // Data
            foreach ($auditLogs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user ? $log->user->name : 'Sistema',
                    $log->action,
                    json_encode($log->meta)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
