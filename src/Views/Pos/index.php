<?php

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StoreManager Pro | Terminal POS</title>
<style>
    :root {
        --bg: #0a0f1e; --panel: #11182b; --border-color: #23304a;
        --accent: #3b82f6; --success: #22c55e; --danger: #ef4444; --text-muted: #8592ad;
    }
    * { box-sizing: border-box; }
    body { background: var(--bg); color: #e6ebf5; font-family: "Segoe UI", Arial, sans-serif; margin: 0; padding: 24px; }
    h1 { font-size: 18px; margin-bottom: 20px; }
    h2 { font-size: 14px; margin: 0 0 12px; }
    .layout { display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start; }
    .panel { background: var(--panel); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .form-group { margin-bottom: 12px; }
    label { display: block; font-size: 11px; color: var(--text-muted); margin-bottom: 4px; font-weight: 700; text-transform: uppercase; }
    select, input { width: 100%; background: #0b0f1a; color: #fff; border: 1px solid var(--border-color); border-radius: 6px; padding: 8px; font-size: 13px; }
    .inline-form { display: flex; gap: 8px; align-items: end; }
    .inline-form .form-group { flex: 1; margin-bottom: 0; }
    table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 12px; }
    th, td { text-align: left; padding: 6px 4px; border-bottom: 1px solid var(--border-color); }
    .btn { background: var(--accent); color: #fff; border: none; border-radius: 6px; padding: 10px 14px; font-weight: 700; cursor: pointer; }
    .btn-success { background: var(--success); width: 100%; padding: 12px; margin-top: 16px; }
    .btn-danger { background: transparent; color: var(--danger); border: none; cursor: pointer; font-weight: 700; padding: 0; }
    .btn-muted { background: var(--border-color); color: var(--text-muted); }
    .total-box { text-align: center; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); border-radius: 10px; padding: 12px; margin: 16px 0; }
    .total-box span { font-size: 22px; font-weight: 800; color: #60a5fa; }
    .alert { padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
    .alert-success { background: rgba(34,197,94,0.12); color: var(--success); border: 1px solid rgba(34,197,94,0.3); }
    .alert-danger { background: rgba(239,68,68,0.12); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
    .info-line { font-size: 11px; color: var(--text-muted); margin-top: 6px; }
    .form-inline-btn { margin: 0; }
</style>
</head>
<body>

<h1>🛒 StoreManager Pro — Terminal POS</h1>

<?php if (!empty($datas["succes"])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($datas["succes"]) ?></div>
<?php endif; ?>

<?php if (!empty($datas["erreur"])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($datas["erreur"]) ?></div>
<?php endif; ?>

<div class="layout">

    <!-- Colonne gauche : sélection client + ajout d'articles + panier -->
    <div>

        <div class="panel">
            <h2>Client</h2>
            <form method="POST" action="/pos/client">
                <div class="form-group">
                    <select name="client_id">
                        <option value="">-- Vente comptant / sans client --</option>
                        <?php foreach ($datas["clients"] as $client): ?>
                            <option value="<?= $client->getId() ?>" <?= ($datas["clientSelectionne"]?->getId() === $client->getId()) ? "selected" : "" ?>>
                                <?= $client->getNom() ?> (<?= $client->getTelephone() ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-muted">Choisir ce client</button>
            </form>
            <?php if ($datas["clientSelectionne"] !== null): ?>
                <p class="info-line">
                    Limite de crédit : <?= $datas["clientSelectionne"]->getLimiteCredit() ?> FCFA
                    — Encours actuel : <?= $datas["encoursClient"] ?> FCFA
                </p>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h2>Ajouter un article</h2>
            <form method="POST" action="/pos/panier/ajouter" class="inline-form">
                <div class="form-group">
                    <label for="produit_id">Article</label>
                    <select name="produit_id" id="produit_id">
                        <?php foreach ($datas["produits"] as $produit): ?>
                            <option value="<?= $produit->getId() ?>">
                                <?= $produit->estEnRupture() ? "🔴" : "🟢" ?> <?= $produit->getNom() ?>
                                (<?= $produit->getQuantiteStock() ?> en stock — <?= $produit->getPrixUnitaire() ?> F)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="max-width: 90px;">
                    <label for="quantite">Qté</label>
                    <input type="number" name="quantite" id="quantite" value="1" min="1">
                </div>
                <button type="submit" class="btn">Ajouter</button>
            </form>
        </div>

        <div class="panel">
            <h2>Panier</h2>
            <table>
                <thead>
                    <tr><th>Produit</th><th>Qté</th><th>Total</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (count($datas["lignesPanier"]) === 0): ?>
                        <tr><td colspan="4" style="text-align:center; color: var(--text-muted);">Panier vide. Ajoutez des articles.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($datas["lignesPanier"] as $ligne): ?>
                        <tr>
                            <td><?= $ligne["produit"]->getNom() ?></td>
                            <td><?= $ligne["quantite"] ?></td>
                            <td><?= $ligne["sousTotal"] ?> F</td>
                            <td>
                                <form method="POST" action="/pos/panier/retirer" class="form-inline-btn">
                                    <input type="hidden" name="produit_id" value="<?= $ligne["produit"]->getId() ?>">
                                    <button type="submit" class="btn-danger">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-box">
                <div style="font-size:10px; color: var(--text-muted); text-transform: uppercase;">Montant total net à payer</div>
                <span><?= number_format($datas["totalPanier"], 0, ",", " ") ?></span> FCFA
            </div>

            <form method="POST" action="/pos/vente">
                <div class="form-group">
                    <label for="montant_verse">Montant versé (avance)</label>
                    <input type="number" name="montant_verse" id="montant_verse" value="0" min="0">
                </div>
                <button type="submit" class="btn btn-success">Valider la vente</button>
            </form>

            <?php if (count($datas["lignesPanier"]) > 0): ?>
                <form method="POST" action="/pos/panier/vider" style="margin-top: 10px;">
                    <button type="submit" class="btn btn-muted" style="width:100%;">Vider le panier</button>
                </form>
            <?php endif; ?>
        </div>

    </div>

    <!-- Colonne droite : registre des ventes -->
    <div class="panel">
        <h2>Registre général des ventes</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Client</th><th>Vendeur</th><th>Total</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php if (count($datas["commandes"]) === 0): ?>
                    <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Aucune vente enregistrée.</td></tr>
                <?php endif; ?>
                <?php foreach ($datas["commandes"] as $commande): ?>
                    <tr>
                        <td>#CMD-<?= $commande->getId() ?></td>
                        <td><?= $commande->getClient()?->getNom() ?? "Client comptant" ?></td>
                        <td><?= $commande->getUtilisateur()->getNom() ?></td>
                        <td><?= $commande->getMontantTotal() ?> F</td>
                        <td><?= $commande->getStatutPaiement() ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
