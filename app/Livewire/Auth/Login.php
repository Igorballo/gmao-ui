<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], attributes: [
            'email' => 'adresse e-mail',
            'password' => 'mot de passe',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Identifiants incorrects.');

            return;
        }

        // Refuse les comptes désactivés
        if (! Auth::user()->actif) {
            Auth::logout();
            $this->addError('email', 'Ce compte est désactivé. Contactez un administrateur.');

            return;
        }

        request()->session()->regenerate();

        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
