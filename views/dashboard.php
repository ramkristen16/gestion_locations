<?php $pageTitle = 'Dashboard'; include 'views/layout_top.php'; ?>

<h2 style="margin-bottom:1.5rem;">Tableau de bord</h2>

<div class="grid">
    <div class="card">
        <h3>🏢 Appartements</h3>
        <p class="prix"><?= $stats['total_appts'] ?></p>
        <p class="stock">Libres : <strong style="color:var(--vert)"><?= $stats['libres'] ?></strong> &nbsp;|&nbsp; Occupés : <?= $stats['occupes'] ?></p>
        <a href="?page=appartements" class="btn">Gérer</a>
    </div>
    <div class="card">
        <h3>👤 Locataires</h3>
        <p class="prix"><?= $stats['total_locs'] ?></p>
        <p class="stock">Blacklistés : <span class="rupture"><?= $stats['blacklistes'] ?></span></p>
        <a href="?page=locataires" class="btn">Gérer</a>
    </div>
    <div class="card">
        <h3>📄 Contrats En cours</h3>
        <p class="prix"><?= $stats['contrats_en_cours'] ?></p>
        <p class="stock">Terminés ce mois : <?= $stats['contrats_termines'] ?></p>
        <a href="?page=contrats" class="btn">Gérer</a>
    </div>
    <div class="card">
        <h3>💰 Loyers (ce mois)</h3>
        <p class="prix"><?= number_format($stats['loyers_mois'], 0, ',', ' ') ?> Ar</p>
        <p class="stock">Impayés : <span class="rupture"><?= $stats['impayes'] ?></span></p>
        <a href="?page=paiements" class="btn">Paiements</a>
    </div>
</div>

<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1rem;">
    <a href="?page=contrats&action=creer" class="btn">+ Nouveau contrat</a>
    <a href="?page=paiements&action=enregistrer" class="btn-success">+ Enregistrer paiement</a>
    <a href="?page=contrats&action=impayes" class="btn-danger">⚠️ Voir impayés</a>
</div>

<?php include 'views/layout_bottom.php'; ?>
