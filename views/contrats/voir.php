<?php $pageTitle = 'Contrat ' . $contrat['numero_contrat']; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=contrats" class="btn-ghost">← Retour</a>
    <h2>📄 <?= htmlspecialchars($contrat['numero_contrat']) ?></h2>
</div>

<div class="section-box" style="margin-top:0">
    <h2>Détails du contrat</h2>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;font-size:.92rem;">
        <div>
            <p><strong>Locataire :</strong> <?= htmlspecialchars($contrat['prenom'] . ' ' . $contrat['nom']) ?></p>
            <p><strong>Tél :</strong> <?= htmlspecialchars($contrat['telephone']) ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($contrat['email']) ?></p>
        </div>
        <div>
            <p><strong>Appartement :</strong> <?= htmlspecialchars($contrat['numero_appartement']) ?> — <?= htmlspecialchars($contrat['designation']) ?></p>
            <p><strong>Lieu :</strong> <?= htmlspecialchars($contrat['lieu']) ?></p>
        </div>
        <div>
            <p><strong>Entrée :</strong> <?= $contrat['date_entree'] ?></p>
            <p><strong>Sortie prévue :</strong> <?= $contrat['date_sortie_prevue'] ?></p>
            <p><strong>Sortie réelle :</strong> <?= $contrat['date_sortie_reelle'] ?: '—' ?></p>
            <p><strong>Durée :</strong> <?= $contrat['duree_mois'] ?> mois</p>
        </div>
        <div>
            <p><strong>Loyer contractuel :</strong> <span style="color:var(--vert);font-weight:700"><?= number_format($contrat['loyer_mensuel_contractuel'], 0, ',', ' ') ?> Ar</span></p>
            <p><strong>Caution versée :</strong> <?= number_format($contrat['caution_versee'], 0, ',', ' ') ?> Ar</p>
            <p><strong>Statut :</strong> <strong><?= $contrat['statut_contrat'] ?></strong></p>
        </div>
    </div>

    <?php if (in_array($contrat['statut_contrat'], ['En cours', 'Prolongé'])): ?>
    <hr class="separator">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="?page=contrats&action=prolonger&id=<?= $contrat['id'] ?>" class="btn">↗ Prolonger</a>
        <a href="?page=contrats&action=resilier&id=<?= $contrat['id'] ?>" class="btn-danger">✕ Résilier</a>
        <a href="?page=contrats&action=terminer&id=<?= $contrat['id'] ?>"
           onclick="return confirm('Terminer ce contrat définitivement ?')"
           class="btn-ghost">✔ Terminer</a>
        <a href="?page=paiements&action=enregistrer&id_contrat=<?= $contrat['id'] ?>" class="btn-success">💰 Enregistrer paiement</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'views/layout_bottom.php'; ?>
