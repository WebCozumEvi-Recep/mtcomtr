<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\LandingContactMail;

class LandingContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        try {
            // Send email
            Mail::to('webcozumevi@gmail.com')->send(new LandingContactMail($validated));

            return back()->with('success', 'Mesajınız başarıyla gönderildi. En kısa sürede sizinle iletişime geçeceğiz.');
        } catch (\Exception $e) {
            // Log error if needed
            return back()->withInput()->with('error', 'Hata: Mesajınız gönderilemedi. Lütfen daha sonra tekrar deneyin.');
        }
    }
}
