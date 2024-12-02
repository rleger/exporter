<?php

namespace App\Http\Controllers;

use App\Models\Entry;

class AppointmentController extends Controller
{
    public function show($entryId)
    {
        $entry = Entry::with('appointments')->findOrFail($entryId);

        return view('appointments.show', compact('entry'));
    }
}
