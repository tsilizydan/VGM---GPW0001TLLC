<h1 class="auth-title">Créer un compte</h1>
<p class="auth-subtitle">Rejoignez Vanilla Groupe Madagascar</p>

<form action="<?= url('register') ?>" method="POST" novalidate>
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="name">Nom complet</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?= old('name') ?>"
            placeholder="Jean Rakoto"
            autocomplete="name"
            required
        >
    </div>

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
        <label for="password">Mot de passe <span class="hint">(min. 8 caractères)</span></label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            autocomplete="new-password"
            required
        >
    </div>

    <div class="form-group">
        <label for="password_confirm">Confirmer le mot de passe</label>
        <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            placeholder="••••••••"
            autocomplete="new-password"
            required
        >
    </div>

    <button type="submit" class="btn btn-block">Créer mon compte</button>
</form>

<p class="auth-switch">
    Vous avez déjà un compte ?
    <a href="<?= url('login') ?>">Se connecter</a>
</p>
