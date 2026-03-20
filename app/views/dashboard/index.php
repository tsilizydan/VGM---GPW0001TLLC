<?php
/** @var array<string,mixed> $user */
$isAdmin = ($user['role'] ?? '') === 'admin';
?>

<div class="dashboard-header">
    <div>
        <h1>Bonjour, <?= e($user['name'] ?? 'Utilisateur') ?> 👋</h1>
        <p class="muted">Tableau de bord <?= $isAdmin ? '<span class="badge badge-admin">Admin</span>' : '<span class="badge badge-customer">Client</span>' ?></p>
    </div>
    <a href="<?= url('logout') ?>" class="btn btn-outline btn-sm">Se déconnecter</a>
</div>

<?php if ($isAdmin): ?>
<div class="dashboard-panel panel-admin">
    <h2>🔐 Panneau Administrateur</h2>
    <p>Vous avez accès aux fonctions d'administration de Vanilla Groupe Madagascar.</p>
    <ul>
        <li>Gérer les utilisateurs</li>
        <li>Gérer les produits</li>
        <li>Consulter les commandes</li>
    </ul>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <span class="card-icon">👤</span>
        <h3>Mon profil</h3>
        <p><strong>Nom :</strong> <?= e($user['name'] ?? '') ?></p>
        <p><strong>E-mail :</strong> <?= e($user['email'] ?? '') ?></p>
        <p><strong>Rôle :</strong> <?= e(ucfirst($user['role'] ?? '')) ?></p>
        <p><strong>Membre depuis :</strong> <?= e(date('d/m/Y', strtotime($user['created_at'] ?? 'now'))) ?></p>
    </div>

    <div class="dashboard-card">
        <span class="card-icon">🌿</span>
        <h3>Vanille Premium</h3>
        <p>Découvrez notre sélection exclusive de vanille de Madagascar, cultivée avec soin.</p>
    </div>

    <div class="dashboard-card">
        <span class="card-icon">📦</span>
        <h3>Mes commandes</h3>
        <p>Aucune commande pour le moment.</p>
    </div>
</div>
