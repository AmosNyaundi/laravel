<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show()
    {
        return view('pages.submanage');
    }

    public function outbox()
    {
        return view('pages.submanage_outbox');
    }
    
    public function submanage(Request $request)
    {

    }


}
