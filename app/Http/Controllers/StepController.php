<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Step;
use Illuminate\Http\Request;

class StepController extends Controller
{
    public function update(Request $request, Step $step)
    {
        $step->update(['completed' => ! $step->completed]);

        return back()->with('success', 'Step updated successfully.');
    }
}
