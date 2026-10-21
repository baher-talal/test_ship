<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (! empty($data['password'])) {
            if (! Hash::check((string) $request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => __('The current password is incorrect').'.'])->withInput();
            }
            $user->password = Hash::make($data['password']);
        }

        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->save();

        return back()->with('success', __('Your details were updated successfully').'.');
    }
}
