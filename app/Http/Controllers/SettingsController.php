<?php

namespace App\Http\Controllers;

use App\Models\ShopSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'settings' => ShopSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'trn' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'vat_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'credit_note_prefix' => ['required', 'string', 'max:10'],
            'installment_day' => ['required', 'integer', 'min:1', 'max:28'],
        ]);

        $settings = ShopSetting::current();
        $settings->update($data);

        return back()->with('status', 'Shop settings saved.');
    }
}
