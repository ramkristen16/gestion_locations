<?php $pageTitle = 'Impayés'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=contrats" class="btn-ghost">← Retour</a>
    <h2> Loyers impayés / partiels</h2>
</div>

<?php if (empty($impayes)): ?>
    <div class="alert-success">Aucun impayé. Tous les loyers sont à jour.</div>
<?php else: ?>
    <div class="alert-danger"> <?= count($impayes) ?> enregistrement(s) impayé(s) ou partiel(s).</div>
    <table style="width:100%;border-collapse:collapse;font-size:.88rem;margin-top:1rem;">
        <thead>
            <tr style="border-bottom:2px solid var(--bordure)">
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Contrat</th>
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Locataire</th>
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Appartement</th>
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Mois</th>
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Payé</th>
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Reste</th>
                <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Statut</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($impayes as $i): ?>
            <tr style="border-bottom:1px solid var(--bordure)">
                <td style="padding:.6rem;font-family:monospace"><?= htmlspecialchars($i['numero_contrat']) ?></td>
                <td style="padding:.6rem"><?= htmlspecialchars($i['prenom'] . ' ' . $i['nom']) ?> <br><small><?= $i['telephone'] ?></small></td>
                <td style="padding:.6rem"><?= htmlspecialchars($i['numero_appartement']) ?></td>
                <td style="padding:.6rem"><?= date('M Y', strtotime($i['mois_concerne'])) ?></td>
                <td style="padding:.6rem"><?= number_format($i['montant_paye'], 0, ',', ' ') ?> Ar</td>
                <td style="padding:.6rem;color:var(--rouge);font-weight:600"><?= number_format($i['reste_a_payer'], 0, ',', ' ') ?> Ar</td>
                <td style="padding:.6rem;color:var(--rouge)"><?= $i['statut_paiement'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'views/layout_bottom.php'; ?>
