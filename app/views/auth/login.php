<h1 class="auth-title">Connexion</h1>
<p class="auth-subtitle">Content de vous revoir !</p>

<form action="<?= url('login') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">Adresse e-mail</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= old('email') ?>"
            placeholder="vous@exemple.com"
            autocomplete="email"
            required
        >
    </div>

    <div class="form-group">
        <label for="password">Mot de passe</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
        >
    </div>

    <button type="submit" class="btn btn-block">Se connecter</button>
</form>

<p class="auth-switch">
    Pas encore de compte ?
    <a href="<?= url('register') ?>">Créer un compte</a>
</p>
