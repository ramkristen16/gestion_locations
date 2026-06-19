<?php $pageTitle = 'Appartement #' . $appartement['numero_appartement']; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=appartements" class="btn-ghost">← Retour</a>
    <h2> <?= htmlspecialchars($appartement['designation']) ?></h2>
</div>

<div class="section-box" style="margin-top:0">
    <h2>Informations</h2>
    <p><strong>Numéro :</strong> <?= htmlspecialchars($appartement['numero_appartement']) ?></p>
    <p><strong>Lieu :</strong> <?= htmlspecialchars($appartement['lieu']) ?></p>
    <p><strong>Loyer :</strong> <?= number_format($appartement['loyer_mensuel'], 0, ',', ' ') ?> Ar / mois</p>
    <p><strong>Caution :</strong> <?= number_format($appartement['caution'], 0, ',', ' ') ?> Ar</p>
    <p><strong>Surface :</strong> <?= $appartement['surface_m2'] ?> m² — <?= $appartement['nombre_pieces'] ?> pièces</p>
    <p><strong>Statut :</strong> <span style="font-weight:700"><?= $appartement['statut'] ?></span></p>
</div>

<div class="section-box">
    <h2> Historique des locataires</h2>
    <?php if (empty($historique)): ?>
        <p style="color:var(--texte-muted)">Aucun locataire pour cet appartement.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
            <tr style="border-bottom:1px solid var(--bordure)">
                <th style="text-align:left;padding:.5rem;color:var(--texte-muted)">Locataire</th>
                <th style="text-align:left;padding:.5rem;color:var(--texte-muted)">Entrée</th>
                <th style="text-align:left;padding:.5rem;color:var(--texte-muted)">Sortie prévue</th>
                <th style="text-align:left;padding:.5rem;color:var(--texte-muted)">Sortie réelle</th>
                <th style="text-align:left;padding:.5rem;color:var(--texte-muted)">Loyer</th>
                <th style="text-align:left;padding:.5rem;color:var(--texte-muted)">Statut</th>
            </tr>
            <?php foreach ($historique as $h): ?>
            <tr style="border-bottom:1px solid var(--bordure)">
                <td style="padding:.5rem"><?= htmlspecialchars($h['prenom'] . ' ' . $h['nom']) ?></td>
                <td style="padding:.5rem"><?= $h['date_entree'] ?></td>
                <td style="padding:.5rem"><?= $h['date_sortie_prevue'] ?></td>
                <td style="padding:.5rem"><?= $h['date_sortie_reelle'] ?: '—' ?></td>
                <td style="padding:.5rem"><?= number_format($h['loyer_mensuel_contractuel'], 0, ',', ' ') ?> Ar</td>
                <td style="padding:.5rem"><?= $h['statut_contrat'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php include 'views/layout_bottom.php'; ?>
