<?php $pageTitle = 'Contrats'; include 'views/layout_top.php'; ?>

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
    <h2>Contrats de location</h2>
    <a href="?page=contrats&action=creer" class="btn">+ Nouveau contrat</a>
</div>

<?php if (empty($contrats)): ?>
    <p style="color:var(--texte-muted)">Aucun contrat.</p>
<?php else: ?>
<table style="width:100%;border-collapse:collapse;font-size:.88rem;">
    <thead>
        <tr style="border-bottom:2px solid var(--bordure)">
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">N° Contrat</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Locataire</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Appartement</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Entrée</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Sortie prévue</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Loyer</th>
            <th style="text-align:left;padding:.6rem;color:var(--texte-muted)">Statut</th>
            <th style="padding:.6rem"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($contrats as $c): ?>
        <?php
        $couleur = match($c['statut_contrat']) {
            'En cours'  => 'var(--vert)',
            'Prolongé'  => 'var(--jaune)',
            'Terminé'   => 'var(--texte-muted)',
            'Résilié'   => 'var(--rouge)',
            default     => 'var(--texte)'
        };
        ?>
        <tr style="border-bottom:1px solid var(--bordure)">
            <td style="padding:.6rem;font-family:monospace"><?= htmlspecialchars($c['numero_contrat']) ?></td>
            <td style="padding:.6rem"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></td>
            <td style="padding:.6rem"><?= htmlspecialchars($c['numero_appartement']) ?></td>
            <td style="padding:.6rem"><?= $c['date_entree'] ?></td>
            <td style="padding:.6rem"><?= $c['date_sortie_prevue'] ?></td>
            <td style="padding:.6rem;color:var(--vert);font-weight:600"><?= number_format($c['loyer_mensuel_contractuel'], 0, ',', ' ') ?> Ar</td>
            <td style="padding:.6rem;color:<?= $couleur ?>;font-weight:600"><?= $c['statut_contrat'] ?></td>
            <td style="padding:.6rem">
                <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                    <a href="?page=contrats&action=voir&id=<?= $c['id'] ?>" class="btn-ghost" style="font-size:.78rem;padding:.3rem .6rem">Voir</a>
                    <?php if (in_array($c['statut_contrat'], ['En cours','Prolongé'])): ?>
                        <a href="?page=contrats&action=prolonger&id=<?= $c['id'] ?>" class="btn-ghost" style="font-size:.78rem;padding:.3rem .6rem">↗ Prolonger</a>
                        <a href="?page=contrats&action=resilier&id=<?= $c['id'] ?>" class="btn-ghost" style="font-size:.78rem;padding:.3rem .6rem;color:var(--rouge)">✕ Résilier</a>
                        <a href="?page=contrats&action=terminer&id=<?= $c['id'] ?>"
                           onclick="return confirm('Terminer ce contrat ?')"
                           class="btn-ghost" style="font-size:.78rem;padding:.3rem .6rem">✔ Terminer</a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php include 'views/layout_bottom.php'; ?>
