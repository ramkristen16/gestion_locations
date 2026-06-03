<?php $pageTitle = 'Appartements'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <h2>🏢 Appartements</h2>
    <a href="?page=appartements&action=ajouter" class="btn">+ Ajouter</a>
</div>

<form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;">
    <input type="hidden" name="page" value="appartements">
    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Numéro, désignation, lieu...">
    <select name="statut" style="background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);padding:.65rem 1rem;border-radius:.5rem">
        <option value="">Tous statuts</option>
        <?php foreach (['Libre','Occupé','En Travaux','Hors service'] as $s): ?>
            <option value="<?= $s ?>" <?= ($_GET['statut'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn">🔍</button>
</form>

<?php if (empty($appartements)): ?>
    <p style="color:var(--texte-muted)">Aucun appartement trouvé.</p>
<?php else: ?>
<div class="grid">
<?php foreach ($appartements as $a): ?>
    <?php
    $statutColor = match($a['statut']) {
        'Libre'       => 'var(--vert)',
        'Occupé'      => 'var(--jaune)',
        'En Travaux'  => '#8b949e',
        default       => 'var(--rouge)'
    };
    ?>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:.78rem;font-weight:600;color:var(--texte-muted)"><?= htmlspecialchars($a['numero_appartement']) ?></span>
            <span style="font-size:.75rem;color:<?= $statutColor ?>;font-weight:600"><?= $a['statut'] ?></span>
        </div>
        <h3><?= htmlspecialchars($a['designation']) ?></h3>
        <p style="font-size:.82rem;color:var(--texte-muted)">📍 <?= htmlspecialchars($a['lieu']) ?></p>
        <p class="prix"><?= number_format($a['loyer_mensuel'], 0, ',', ' ') ?> Ar / mois</p>
        <p class="stock">Caution : <?= number_format($a['caution'], 0, ',', ' ') ?> Ar &nbsp;|&nbsp; <?= $a['surface_m2'] ?> m² &nbsp;|&nbsp; <?= $a['nombre_pieces'] ?> pcs</p>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:auto;">
            <a href="?page=appartements&action=voir&id=<?= $a['id'] ?>" class="btn-ghost">Voir</a>
            <a href="?page=appartements&action=modifier&id=<?= $a['id'] ?>" class="btn-ghost">✏️</a>
            <?php if ($a['statut'] === 'Libre'): ?>
                <a href="?page=contrats&action=creer&id_appartement=<?= $a['id'] ?>" class="btn" style="font-size:.78rem;padding:.4rem .7rem">Louer</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php include 'views/layout_bottom.php'; ?>
