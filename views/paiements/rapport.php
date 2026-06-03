<?php $pageTitle = 'Rapport financier'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=paiements" class="btn-ghost">← Retour</a>
    <h2>📊 Rapport financier</h2>
</div>

<div class="section-box" style="margin-top:0">
    <h2>Filtres</h2>
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end">
        <input type="hidden" name="page" value="paiements">
        <input type="hidden" name="action" value="rapport">
        <div class="form-group" style="margin:0">
            <label>Du</label>
            <input type="date" name="debut" value="<?= $debut ?>">
        </div>
        <div class="form-group" style="margin:0">
            <label>Au</label>
            <input type="date" name="fin" value="<?= $fin ?>">
        </div>
        <button type="submit" class="btn">Calculer</button>
    </form>
</div>

<div class="grid" style="margin-top:1.5rem;">
    <div class="card">
        <h3>💰 Total loyers perçus</h3>
        <p class="prix"><?= number_format($total, 0, ',', ' ') ?> Ar</p>
        <p class="stock">Du <?= $debut ?> au <?= $fin ?></p>
    </div>
    <div class="card">
        <h3>📈 Chiffre d'affaires</h3>
        <p class="prix"><?= number_format($ca, 0, ',', ' ') ?> Ar</p>
        <p class="stock">Paiements encaissés sur la période</p>
    </div>
</div>

<?php include 'views/layout_bottom.php'; ?>
