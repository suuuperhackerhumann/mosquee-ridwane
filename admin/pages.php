<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('pages');

$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $stmt = $pdo->prepare("UPDATE pages_contenu SET titre = ?, contenu = ? WHERE cle = ?");
    foreach ($_POST['blocs'] ?? [] as $cle => $bloc) {
        $stmt->execute([trim($bloc['titre'] ?? ''), trim($bloc['contenu'] ?? ''), $cle]);
    }
    $succes = true;
}

$blocs = $pdo->query("SELECT * FROM pages_contenu ORDER BY cle")->fetchAll();

$groupes = [
    'Accueil'   => 'accueil_',
    'À propos'  => 'apropos_',
    'Horaires'  => 'horaires_',
    'Dons'      => 'dons_',
    'Contact'   => 'contact_',
];

$page_admin = 'pages';
$titre_admin = 'Contenu des pages';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Contenu des pages</h1></div>
<?php if ($succes): ?><div class="alerte alerte-succes">Contenu mis à jour.</div><?php endif; ?>
<p style="color:var(--encre-600); font-size:0.9rem; margin-top:-14px;">Modifiez ici les textes affichés sur les pages « Accueil », « À propos », « Horaires », « Dons » et « Contact ».</p>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

<?php foreach ($groupes as $label => $prefixe): ?>
<div class="admin-carte">
    <h3 style="margin-top:0;"><?= h($label) ?></h3>
    <?php foreach ($blocs as $b): if (strpos($b['cle'], $prefixe) !== 0) continue; ?>
    <div class="champ" style="border-top:1px solid var(--plâtre-200); padding-top:14px; margin-top:14px;">
        <label><?= h($b['cle']) ?></label>
        <input type="text" name="blocs[<?= h($b['cle']) ?>][titre]" value="<?= h($b['titre']) ?>" placeholder="Titre" style="margin-bottom:8px;">
        <textarea name="blocs[<?= h($b['cle']) ?>][contenu]" rows="3" placeholder="Texte"><?= h($b['contenu']) ?></textarea>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<button type="submit" class="bouton bouton-emeraude">Enregistrer toutes les modifications</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
