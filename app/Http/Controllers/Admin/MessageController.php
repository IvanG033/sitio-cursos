<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Support\Correo;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with('user')->latest()->paginate(30);

        return view('admin.messages.index', compact('messages'));
    }

    public function create(Request $request)
    {
        $students = User::where('role', User::ROLE_STUDENT)->orderBy('name')->get();

        return view('admin.messages.create', [
            'students' => $students,
            'selected' => $request->query('para'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'to' => ['required', 'string'], // id de usuario, "approved", "pending" o "all"
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:10000'],
            'send_email' => ['nullable', 'boolean'],
        ]);

        $students = User::where('role', User::ROLE_STUDENT);
        $recipients = match ($data['to']) {
            'all' => $students->where('status', '!=', User::STATUS_BLOCKED)->get(),
            'approved' => $students->where('status', User::STATUS_APPROVED)->get(),
            'pending' => $students->where('status', User::STATUS_PENDING)->get(),
            default => $students->whereKey((int) $data['to'])->get(),
        };

        if ($recipients->isEmpty()) {
            return back()->withInput()->withErrors(['to' => 'No hay destinatarios para ese grupo.']);
        }

        $emailed = 0;
        foreach ($recipients as $user) {
            $message = Message::create([
                'user_id' => $user->id,
                'sender_id' => $request->user()->id,
                'subject' => $data['subject'],
                'body' => $data['body'],
            ]);

            if ($request->boolean('send_email')) {
                $ok = Correo::enviar(
                    $user->email,
                    $data['subject'],
                    "Hola {$user->name},\n\n".$data['body'],
                    $user->isApproved() ? 'Ver en la plataforma' : null,
                    $user->isApproved() ? route('inbox.show', $message) : null,
                );
                if ($ok) {
                    $message->update(['emailed_at' => now()]);
                    $emailed++;
                }
            }
        }

        $text = 'Mensaje enviado a '.$recipients->count().' '.($recipients->count() === 1 ? 'persona' : 'personas').'.';
        if ($request->boolean('send_email')) {
            $text .= $emailed === $recipients->count()
                ? ' También se envió por correo.'
                : " Por correo se enviaron {$emailed}; revisa la configuración del correo (ver docs/DREAMHOST.md).";
        }

        return redirect()->route('admin.messages.index')->with('status', $text);
    }
}
