<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['id_photo_path'] = $request->file('id_photo')
            ->store('id-photos', 'public');

        unset($data['id_photo']);

        $user = User::create($data + ['status' => User::STATUS_PENDING]);
        $user->assignRole('student');

        AuditLog::record('user.register', $user, ['email' => $user->email], $user->id);

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['current_session_id' => $request->session()->getId()]);

        return redirect()->route('account.pending');
    }
}
