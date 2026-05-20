<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        if ($request->filled('website')) {
            abort(403, 'Bot detected');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:190'],
            'mobile' => ['required', 'digits:10'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::create($validated + ['user_id' => $request->user()?->id]);
        $to = config('mail.from.address');

        if ($to) {
            try {
                Mail::raw(
                    "Name: {$message->name}\nEmail: {$message->email}\nMobile: {$message->mobile}\n\n{$message->message}",
                    fn ($mail) => $mail->to($to)->subject($message->subject ?: 'New contact message')
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('status', 'Your message has been sent.');
    }
}
