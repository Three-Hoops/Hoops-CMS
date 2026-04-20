<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ThemeMode;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_mode' => ['required', 'string', 'in:' . implode(',', array_column(ThemeMode::cases(), 'value'))],
        ]);

        $request->user()->update(['theme_mode' => $validated['theme_mode']]);

        return back();
    }

    public function updateTimeZone(Request $request): RedirectResponse
    {
        $validated = $request->validate([                                                                                                                                                                                                                                        
            'timezone' => ['required', 'string', 'timezone:all'],                                                                                                                                                                                                                
        ]);

        $request->user()->update(['timezone' => $validated['timezone']]);                                                                                                                                                                                                        

        return back(); 
    }
}
