<?php $pageTitle = 'Paiements'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <h2>💰 Paiements de loyers</h2>
    <a href="?page=paiements&action=enregistrer" class="btn">+ Enregistrer paiement</a>
</div>

<div style="margin-bottom:1rem;">
    <a href="?page=paiements&action=rapport" class="btn-ghost">📊 Rapport & CA</a>
</div>

<?php if (empty($paiements)): ?>
    <p style="color:var(--texte-muted)">Aucun paiement enregistré.</p>
<?php else: ?>
<table style="width:100%;border-collapse:collapse;font-size:.88rem;">
    <thead>
        <tr style="border-bottom:2px solid var(--bordure)">
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Quittance</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Locataire</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Appt</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Mois</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Montant</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Reste</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Mode</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Statut</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($paiements as $p): ?>
        <?php
        $couleur = match($p['statut_paiement']) {
            'Payé'   => 'var(--vert)',
            'Partiel' => 'var(--jaune)',
            default   => 'var(--rouge)',
        };
        ?>
        <tr style="border-bottom:1px solid var(--bordure)">
            <td style="padding:.6rem;font-family:monospace;font-size:.78rem"><?= htmlspecialchars($p['id_quittance']) ?></td>
            <td style="padding:.6rem"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></td>
            <td style="padding:.6rem"><?= htmlspecialchars($p['numero_appartement']) ?></td>
            <td style="padding:.6rem"><?= date('M Y', strtotime($p['mois_concerne'])) ?></td>
            <td style="padding:.6rem;color:var(--vert);font-weight:600"><?= number_format($p['montant_paye'], 0, ',', ' ') ?> Ar</td>
            <td style="padding:.6rem;color:var(--rouge)"><?= $p['reste_a_payer'] > 0 ? number_format($p['reste_a_payer'], 0, ',', ' ') . ' Ar' : '—' ?></td>
            <td style="padding:.6rem;color:var(--texte-muted)"><?= $p['mode_paiement'] ?></td>
            <td style="padding:.6rem;color:<?= $couleur ?>;font-weight:600"><?= $p['statut_paiement'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php include 'views/layout_bottom.php'; ?>
