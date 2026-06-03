<?php $pageTitle = 'Enregistrer un paiement'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=paiements" class="btn-ghost">← Retour</a>
    <h2>💰 Enregistrer un paiement de loyer</h2>
</div>

<?php foreach ($erreurs as $e): ?>
    <div class="alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="section-box" style="margin-top:0">
    <form method="POST">
        <div class="form-group">
            <label>Contrat (En cours)</label>
            <select name="id_contrat" required style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem">
                <option value="">-- Choisir --</option>
                <?php foreach ($contrats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($data['id_contrat'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['numero_contrat'] . ' — ' . $c['prenom'] . ' ' . $c['nom'] . ' / ' . $c['numero_appartement'] . ' (' . number_format($c['loyer_mensuel_contractuel'], 0, ',', ' ') . ' Ar)') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label>Mois concerné</label>
                <input type="month" name="mois_concerne" value="<?= $_GET['mois'] ?? date('Y-m') ?>" required style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem;outline:none">
            </div>
            <div class="form-group">
                <label>Montant payé (Ar)</label>
                <input type="number" name="montant_paye" value="<?= $data['montant_paye'] ?? '' ?>" step="0.01" min="0" required>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label>Mode de paiement</label>
                <select name="mode_paiement" style="width:100%;padding:.7rem 1rem;background:var(--fond-input);border:1px solid var(--bordure);color:var(--texte);border-radius:.5rem">
                    <?php foreach (['Espèces','Virement','Chèque','Mobile Money'] as $m): ?>
                        <option value="<?= $m ?>" <?= ($data['mode_paiement'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Référence transaction</label>
                <input type="text" name="reference_transaction" value="<?= htmlspecialchars($data['reference_transaction'] ?? '') ?>" placeholder="Facultatif">
            </div>
        </div>
        <p style="font-size:.82rem;color:var(--texte-muted);margin-bottom:1rem;">
            ℹ️ Si le montant est inférieur au loyer, le paiement sera enregistré comme <strong>Partiel</strong> et le reste à payer sera calculé.
        </p>
        <button type="submit" class="btn">Enregistrer le paiement</button>
    </form>
</div>

<?php include 'views/layout_bottom.php'; ?>
