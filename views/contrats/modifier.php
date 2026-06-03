<?php
$isProlonger = ($action ?? '') === 'prolonger';
$pageTitle   = $isProlonger ? 'Prolonger contrat' : 'Résilier contrat';
include 'views/layout_top.php';
?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=contrats" class="btn-ghost">← Retour</a>
    <h2><?= $isProlonger ? '↗ Prolonger' : '✕ Résilier' ?> le contrat <?= htmlspecialchars($contrat['numero_contrat']) ?></h2>
</div>

<?php foreach ($erreurs as $e): ?>
    <div class="alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="section-box" style="margin-top:0">
    <p><strong>Locataire :</strong> <?= htmlspecialchars($contrat['prenom'] . ' ' . $contrat['nom']) ?></p>
    <p><strong>Appartement :</strong> <?= htmlspecialchars($contrat['numero_appartement']) ?></p>
    <p><strong>Période :</strong> <?= $contrat['date_entree'] ?> → <?= $contrat['date_sortie_prevue'] ?></p>
    <p><strong>Loyer :</strong> <?= number_format($contrat['loyer_mensuel_contractuel'], 0, ',', ' ') ?> Ar</p>
    <hr class="separator">
    <form method="POST">
        <?php if ($isProlonger): ?>
            <div class="form-group">
                <label>Nouvelle date de sortie prévue</label>
                <input type="date" name="date_sortie_prevue" min="<?= $contrat['date_sortie_prevue'] ?>" required>
            </div>
            <button type="submit" class="btn">Prolonger le contrat</button>
        <?php else: ?>
            <div class="form-group">
                <label>Date de sortie réelle</label>
                <input type="date" name="date_sortie_reelle" value="<?= date('Y-m-d') ?>" required>
            </div>
            <button type="submit" class="btn-danger">Résilier le contrat</button>
        <?php endif; ?>
    </form>
</div>

<?php include 'views/layout_bottom.php'; ?>
