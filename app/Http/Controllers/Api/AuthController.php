<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Connexion : renvoie un token Sanctum + le profil de l'utilisateur.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        if (! $user->actif) {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est désactivé.'],
            ]);
        }

        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->profil($user, $request),
        ]);
    }

    /**
     * Profil de l'utilisateur connecté.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->profil($request->user(), $request));
    }

    /**
     * Mise à jour du profil de l'utilisateur connecté (nom, téléphone).
     * L'e-mail n'est pas modifiable ici — c'est l'identifiant de connexion.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
        ], [
            'name.required' => 'Le nom est requis.',
        ]);

        $user->update([
            'name' => $data['name'],
            'telephone' => $data['telephone'] ?: null,
        ]);

        return response()->json($this->profil($user->fresh(), $request));
    }

    /**
     * Photo de profil de l'utilisateur connecté.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
        ], [
            'photo.required' => 'Aucune image sélectionnée.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.max' => 'L\'image ne doit pas dépasser 5 Mo.',
        ]);

        $user = $request->user();
        $oldPhoto = $user->photo;

        $path = $request->file('photo')->store('users', 'public');
        $user->update(['photo' => $path]);

        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return response()->json($this->profil($user->fresh(), $request));
    }

    /**
     * Changement de mot de passe de l'utilisateur connecté.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation ne correspond pas.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $user->update(['password' => $data['password']]);

        return response()->json(['message' => 'Mot de passe mis à jour.']);
    }

    /**
     * Préférences de notifications push.
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $data = $request->validate([
            'push_enabled' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $user->update(['push_enabled' => $data['push_enabled']]);

        return response()->json($this->profil($user->fresh(), $request));
    }

    /**
     * Déconnexion : révoque le token courant.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    /**
     * Représentation JSON du profil (rôles + permissions pour adapter l'UI mobile).
     */
    protected function profil(User $user, ?Request $request = null): array
    {
        $host = $request?->getSchemeAndHttpHost() ?? rtrim(config('app.url'), '/');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'telephone' => $user->telephone,
            'photo_url' => $user->photo
                ? $host.'/storage/'.$user->photo
                : null,
            'actif' => $user->actif,
            'push_enabled' => $user->push_enabled,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }
}
