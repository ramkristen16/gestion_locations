<?php $pageTitle = 'Nouveau contrat'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=contrats" class="btn-ghost">← Retour</a>
    <h2>📄 Nouveau contrat de location</h2>
</div>

<?php foreach ($erreurs as $e): ?>
    <div class="alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="section-box" style="margin-top:0">
    <form method="POST">
        <div class="form-group">
            <label>Locataire</label>
            <select name="id_locataire" required style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem">
                <option value="">-- Choisir --</option>
                <?php foreach ($locataires as $l): ?>
                    <?php if ($l['blacklisté']) continue; ?>
                    <option value="<?= $l['id'] ?>" <?= ($data['id_locataire'] ?? 0) == $l['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['prenom'] . ' ' . $l['nom'] . ' — ' . $l['telephone']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Appartement (Libres uniquement)</label>
            <select name="id_appartement" required style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem">
                <option value="">-- Choisir --</option>
                <?php foreach ($appartements as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= ($data['id_appartement'] ?? 0) == $a['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['numero_appartement'] . ' — ' . $a['designation'] . ' (' . number_format($a['loyer_mensuel'], 0, ',', ' ') . ' Ar)') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label>Date d'entrée (≤ aujourd'hui)</label>
                <input type="date" name="date_entree" value="<?= $data['date_entree'] ?? '' ?>" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label>Date de sortie prévue</label>
                <input type="date" name="date_sortie_prevue" value="<?= $data['date_sortie_prevue'] ?? '' ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Caution versée (Ar)</label>
            <input type="number" name="caution_versee" value="<?= $data['caution_versee'] ?? '' ?>" step="0.01" required>
        </div>
        <p style="font-size:.82rem;color:var(--texte-muted);margin-bottom:1rem;">
            ℹ️ La durée en mois et le loyer contractuel sont calculés et figés automatiquement.
        </p>
        <button type="submit" class="btn">Créer le contrat</button>
    </form>
</div>

<?php include 'views/layout_bottom.php'; ?>
