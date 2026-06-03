<?php $pageTitle = 'Locataires'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <h2>👤 Locataires</h2>
    <a href="?page=locataires&action=ajouter" class="btn">+ Ajouter</a>
</div>

<form method="GET" style="margin-bottom:1.5rem;display:flex;gap:.75rem;">
    <input type="hidden" name="page" value="locataires">
    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Rechercher par nom ou prénom...">
    <button type="submit" class="btn">🔍</button>
</form>

<?php if (empty($locataires)): ?>
    <p style="color:var(--texte-muted)">Aucun locataire trouvé.</p>
<?php else: ?>
<div class="grid">
<?php foreach ($locataires as $l): ?>
    <div class="card">
        <?php if ($l['blackliste']): ?>
            <span style="background:var(--rouge);color:#fff;border-radius:.3rem;padding:.2rem .5rem;font-size:.75rem;width:fit-content">🚫 BLACKLISTÉ</span>
        <?php endif; ?>
        <h3><?= htmlspecialchars($l['prenom'] . ' ' . $l['nom']) ?></h3>
        <p style="font-size:.82rem;color:var(--texte-muted)"><?= htmlspecialchars($l['numero_locataire']) ?></p>
        <p>📞 <?= htmlspecialchars($l['telephone']) ?></p>
        <p>✉️ <?= htmlspecialchars($l['email']) ?></p>
        <p class="stock">Inscrit le <?= $l['date_inscription'] ?></p>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:auto;">
            <a href="?page=locataires&action=voir&id=<?= $l['id'] ?>" class="btn-ghost">Voir</a>
            <a href="?page=locataires&action=modifier&id=<?= $l['id'] ?>" class="btn-ghost">✏️</a>
            <?php if (!$l['blackliste']): ?>
                <a href="?page=locataires&action=blacklister&id=<?= $l['id'] ?>"
                   onclick="return confirm('Blacklister ce locataire ?')" class="btn-danger" style="font-size:.78rem;padding:.4rem .7rem">🚫</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php include 'views/layout_bottom.php'; ?>
