<?php 
$isModif = ($formMode ?? '') === 'modifier';
$pageTitle = $isModif ? 'Modifier Locataire' : 'Ajouter Locataire';
include 'views/layout_top.php'; 
?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="?page=locataires" class="btn-ghost">← Retour</a>
    <h2><?= $isModif ? '✏️ Modifier' : '➕ Ajouter' ?> un locataire</h2>
</div>

<?php foreach ($erreurs as $e): ?>
    <div class="alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<div class="section-box">
    <form method="POST">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($data['prenom'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="number_format" name="telephone" value="<?= htmlspecialchars($data['telephone'] ?? '') ?>" required>
            <p id= "eror"></p>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Adresse</label>
            <input type="text" name="adresse" value="<?= htmlspecialchars($data['adresse'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Pièce d'identité (CIN / Passeport)</label>
            <input type="number_format" name="piece_identite" value="<?= htmlspecialchars($data['piece_identite'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn"><?= $isModif ? 'Enregistrer' : 'Ajouter le locataire' ?></button>
    </form>
</div>



<?php include 'views/layout_bottom.php'; ?>
