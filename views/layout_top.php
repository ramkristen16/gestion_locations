<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Gestion Locations') ?></title>
    <link rel="stylesheet" href="?asset=style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>GestiLoc — Gestion des Locations</h1>
        <nav style="display:flex;gap:.75rem;flex-wrap:wrap;">
            <a href="?page=dashboard"      class="btn-ghost"> Dashboard</a>
            <a href="?page=appartements"   class="btn-ghost"> Appartements</a>
            <a href="?page=locataires"     class="btn-ghost"> Locataires</a>
            <a href="?page=contrats"       class="btn-ghost"> Contrats</a>
            <a href="?page=paiements"      class="btn-ghost"> Paiements</a>
            <a href="?page=contrats&action=impayes" class="btn-ghost" style="color:#ff8a8a">Impayés</a>
        </nav>
    </header>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
