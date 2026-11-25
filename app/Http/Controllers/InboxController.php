<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $messages = $request->user()->messages()->paginate(20);

        return view('inbox.index', compact('messages'));
    }

    public function show(Request $request, Message $message)
    {
        abort_unless($message->user_id === $request->user()->id, 404);
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('inbox.show', compact('message'));
    }
}
