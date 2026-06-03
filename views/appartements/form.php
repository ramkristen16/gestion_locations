<?php
$isModif = ($formMode ?? '') === 'modifier';
$pageTitle = $isModif ? 'Modifier Appartement' : 'Ajouter Appartement';
include 'views/layout_top.php';
?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=appartements" class="btn-ghost">← Retour</a>
    <h2><?= $isModif ? '✏️ Modifier' : '➕ Ajouter' ?> un appartement</h2>
</div>

<?php foreach ($erreurs as $e): ?>
    <div class="alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="section-box">
    <form method="POST">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label>Numéro appartement</label>
                <input type="text" name="numero_appartement" value="<?= htmlspecialchars($data['numero_appartement'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Lieu</label>
                <input type="text" name="lieu" value="<?= htmlspecialchars($data['lieu'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Désignation</label>
            <input type="text" name="designation" value="<?= htmlspecialchars($data['designation'] ?? '') ?>" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label>Loyer mensuel (Ar)</label>
                <input type="number" name="loyer_mensuel" value="<?= $data['loyer_mensuel'] ?? '' ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Caution (Ar) — max 3× loyer</label>
                <input type="number" name="caution" value="<?= $data['caution'] ?? '' ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Surface (m²)</label>
                <input type="number" name="surface_m2" value="<?= $data['surface_m2'] ?? '' ?>" step="0.01">
            </div>
            <div class="form-group">
                <label>Nombre de pièces</label>
                <input type="number" name="nombre_pieces" value="<?= $data['nombre_pieces'] ?? '' ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Charges incluses</label>
            <select name="charges_incluses" style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem">
                <option value="Non" <?= ($data['charges_incluses'] ?? 'Non') === 'Non' ? 'selected' : '' ?>>Non</option>
                <option value="Oui" <?= ($data['charges_incluses'] ?? '') === 'Oui' ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>
        <?php if ($isModif): ?>
        <div class="form-group">
            <label>Statut</label>
            <select name="statut" style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem">
                <?php foreach (['Libre','Occupé','En Travaux','Hors service'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($data['statut'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label>Description</label>
            <input type="text" name="description" value="<?= htmlspecialchars($data['description'] ?? '') ?>">
        </div>
        <button type="submit" class="btn"><?= $isModif ? 'Enregistrer' : 'Ajouter l\'appartement' ?></button>
    </form>
</div>

<?php include 'views/layout_bottom.php'; ?>
