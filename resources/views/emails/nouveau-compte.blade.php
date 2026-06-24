<x-mail::message>
# Bienvenue sur GMAO+

Bonjour {{ $user->name }},

Un compte vient d'être créé pour vous sur **GMAO+**, la plateforme de gestion de maintenance. Voici vos identifiants de connexion :

- **Adresse e-mail :** {{ $user->email }}
- **Mot de passe provisoire :** {{ $motDePasse }}

Pour des raisons de sécurité, pensez à modifier ce mot de passe après votre première connexion.

<x-mail::button :url="route('login')">
Se connecter
</x-mail::button>

À bientôt,<br>
L'équipe GMAO+
</x-mail::message>
