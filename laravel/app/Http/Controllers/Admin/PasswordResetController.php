<?php
namespace App\Http\Controllers\Admin;                                                                                                                                                                                                                                        

use App\Enums\FlashType;                                                                                                                                                                                                                                                     
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;                                                                                                                                                                                                                                        
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;                                                                                                                                                                                                                                     
use Inertia\Inertia;                                                                                                                                                                                                                                                         
use Inertia\Response;

class PasswordResetController extends Controller
{
    public function request(): Response
    {
        return Inertia::render('Admin/Auth/ForgotPassword');
    }

    public function email(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);   

        Password::sendResetLink($request->only('email'));

        // Always return the same message to avoid email enumeration                                                                                                                                                                                                         
        return back()->with(FlashType::Success->value, 'If that email is registered, a reset link has been sent.');
    }

    public function reset(Request $request, string $token): Response
    {
        return Inertia::render('Admin/Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email',''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token'     => ['required'],
            'email'     => ['required', 'email'],
            'password'  => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with(FlashType::Success->value, 'Password reset successfully. Please log in.')
            : back()->withErrors(['email' => __($status)]);
    }
}

?>