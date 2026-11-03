<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('horaires');

$erreur = ''; $succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $date = $_POST['date_jour'] ?? date('Y-m-d');
    $champs = ['fajr','lever_soleil','dhuhr','asr','maghrib','isha','jumuah'];
    $valeurs = [];
    foreach ($champs as $c) $valeurs[$c] = $_POST[$c] ?: null;

    $stmt = $pdo->prepare("SELECT id FROM horaires_prieres WHERE date_jour = ?");
    $stmt->execute([$date]);
    if ($stmt->fetch()) {
        $sql = "UPDATE horaires_prieres SET fajr=?, lever_soleil=?, dhuhr=?, asr=?, maghrib=?, isha=?, jumuah=? WHERE date_jour=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$valeurs['fajr'],$valeurs['lever_soleil'],$valeurs['dhuhr'],$valeurs['asr'],$valeurs['maghrib'],$valeurs['isha'],$valeurs['jumuah'],$date]);
    } else {
        $sql = "INSERT INTO horaires_prieres (date_jour, fajr, lever_soleil, dhuhr, asr, maghrib, isha, jumuah) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date,$valeurs['fajr'],$valeurs['lever_soleil'],$valeurs['dhuhr'],$valeurs['asr'],$valeurs['maghrib'],$valeurs['isha'],$valeurs['jumuah']]);
    }
    signalerModificationPublique();
    $succes = true;
}

// Contenu Jumu'ah / Ramadan (pages_contenu)
if (isset($_POST['maj_textes'])) {
    checkCsrf();
    foreach (['horaires_jumuah', 'horaires_ramadan'] as $cle) {
        $stmt = $pdo->prepare("UPDATE pages_contenu SET contenu = ? WHERE cle = ?");
        $stmt->execute([trim($_POST[$cle] ?? ''), $cle]);
    }
    signalerModificationPublique();
    $succes = true;
}

$dateSelectionnee = $_GET['date'] ?? date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM horaires_prieres WHERE date_jour = ?");
$stmt->execute([$dateSelectionnee]);
$horaires = $stmt->fetch() ?: [];

$prochains = $pdo->query("SELECT * FROM horaires_prieres WHERE date_jour >= CURDATE() ORDER BY date_jour ASC LIMIT 14")->fetchAll();

$page_admin = 'horaires';
$titre_admin = 'Horaires de prière';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Horaires de prière</h1></div>
<?php if ($succes): ?><div class="alerte alerte-succes">Enregistré avec succès.</div><?php endif; ?>
<?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>

<div class="admin-carte">
    <h3 style="margin-top:0;">Mettre à jour un jour</h3>
    <form method="get" style="margin-bottom:18px;">
        <div class="champ" style="max-width:220px;">
            <label for="date">Choisir la date</label>
            <input type="date" id="date" name="date" value="<?= h($dateSelectionnee) ?>" onchange="this.form.submit()">
        </div>
    </form>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="date_jour" value="<?= h($dateSelectionnee) ?>">
        <div class="grille grille-4">
            <div class="champ"><label>Fajr</label><input type="time" name="fajr" value="<?= h(substr($horaires['fajr'] ?? '', 0, 5)) ?>"></div>
            <div class="champ"><label>Lever du soleil</label><input type="time" name="lever_soleil" value="<?= h(substr($horaires['lever_soleil'] ?? '', 0, 5)) ?>"></div>
            <div class="champ"><label>Dhuhr</label><input type="time" name="dhuhr" value="<?= h(substr($horaires['dhuhr'] ?? '', 0, 5)) ?>"></div>
            <div class="champ"><label>Asr</label><input type="time" name="asr" value="<?= h(substr($horaires['asr'] ?? '', 0, 5)) ?>"></div>
            <div class="champ"><label>Maghrib</label><input type="time" name="maghrib" value="<?= h(substr($horaires['maghrib'] ?? '', 0, 5)) ?>"></div>
            <div class="champ"><label>Isha</label><input type="time" name="isha" value="<?= h(substr($horaires['isha'] ?? '', 0, 5)) ?>"></div>
            <div class="champ"><label>Jumu'ah (khutbah)</label><input type="time" name="jumuah" value="<?= h(substr($horaires['jumuah'] ?? '', 0, 5)) ?>"></div>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Enregistrer ce jour</button>
    </form>
</div>

<div class="admin-carte">
    <h3 style="margin-top:0;">Textes — Vendredi &amp; Ramadan</h3>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="maj_textes" value="1">
        <div class="champ">
            <label>Informations Jumu'ah (prière du vendredi)</label>
            <textarea name="horaires_jumuah" rows="3"><?= h(contenuTexte('horaires_jumuah')) ?></textarea>
        </div>
        <div class="champ">
            <label>Informations Ramadan</label>
            <textarea name="horaires_ramadan" rows="3"><?= h(contenuTexte('horaires_ramadan')) ?></textarea>
        </div>
        <button type="submit" class="bouton bouton-emeraude">Enregistrer les textes</button>
    </form>
</div>

<div class="admin-carte">
    <h3 style="margin-top:0;">Prochains jours renseignés</h3>
    <table class="table-admin">
        <thead><tr><th>Date</th><th>Fajr</th><th>Dhuhr</th><th>Asr</th><th>Maghrib</th><th>Isha</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($prochains as $h): ?>
        <tr>
            <td><?= h(formatDateFr($h['date_jour'])) ?></td>
            <td><?= formatHeure($h['fajr']) ?></td>
            <td><?= formatHeure($h['dhuhr']) ?></td>
            <td><?= formatHeure($h['asr']) ?></td>
            <td><?= formatHeure($h['maghrib']) ?></td>
            <td><?= formatHeure($h['isha']) ?></td>
            <td class="actions"><a href="?date=<?= h($h['date_jour']) ?>">Modifier</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$prochains): ?><tr><td colspan="7">Aucun horaire à venir renseigné.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
