<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RetrySmsController extends Controller
{
    public function show()
    {
        return view('pages.retry');
    }

    public function que()
    {
        return view('pages.retry_outbox');
    }

    public function retrysms(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
    }
}
