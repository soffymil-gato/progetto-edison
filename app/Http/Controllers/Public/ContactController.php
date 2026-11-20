<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Contact\SubmitContactRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ContactController extends Controller
{
    public function store(Request $request, SubmitContactRequest $submit): RedirectResponse
    {
        // Honeypot: bots fill the hidden field. The answer looks successful so they learn nothing.
        if (filled($request->input('website'))) {
            return back()->with('success', self::SUCCESS);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\s-]{5,40}$/'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'privacy' => ['accepted'],
        ], [
            'privacy.accepted' => 'Per inviare la richiesta è necessario prendere visione dell\'informativa privacy.',
            'phone.regex' => 'Il numero di telefono contiene caratteri non validi.',
        ], [
            'name' => 'nome e cognome',
            'company' => 'azienda',
            'subject' => 'oggetto',
            'message' => 'messaggio',
            'phone' => 'telefono',
        ]);

        $submit->handle($data);

        return back()->with('success', self::SUCCESS);
    }

    private const SUCCESS = 'Grazie, la tua richiesta è stata inviata. Ti risponderemo il prima possibile.';
}
