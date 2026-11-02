<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Contact';

$succes = false;
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($nom === '' || $email === '' || $message === '') {
        $erreur = "Merci de renseigner votre nom, votre e-mail et votre message.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse e-mail saisie n'est pas valide.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, telephone, sujet, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $telephone, $sujet, $message]);
        $succes = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Contact</div>
        <h1>Contact</h1>
    </div>
</section>

<section class="section">
    <div class="conteneur grille grille-2" style="gap:56px; align-items:start;">
        <div>
            <h2>Nous écrire</h2>

            <?php if ($succes): ?>
            <div class="alerte alerte-succes">Votre message a bien été envoyé. Nous vous répondrons dans les meilleurs délais, in cha' Allah.</div>
            <?php endif; ?>
            <?php if ($erreur): ?>
            <div class="alerte alerte-erreur"><?= h($erreur) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/contact.php">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="grille grille-2">
                    <div class="champ">
                        <label for="nom">Nom complet *</label>
                        <input type="text" id="nom" name="nom" required value="<?= h($_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="champ">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="grille grille-2">
                    <div class="champ">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?= h($_POST['telephone'] ?? '') ?>">
                    </div>
                    <div class="champ">
                        <label for="sujet">Sujet</label>
                        <input type="text" id="sujet" name="sujet" value="<?= h($_POST['sujet'] ?? '') ?>">
                    </div>
                </div>
                <div class="champ">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" rows="6" required><?= h($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="bouton bouton-emeraude">Envoyer le message</button>
            </form>
        </div>

        <div>
            <div class="carte" style="margin-bottom:20px;">
                <div class="carte-corps">
                    <h3>Coordonnées</h3>
                    <p class="resume">
                        <span class="meta-icone"><i class="fa-solid fa-location-dot icone" aria-hidden="true"></i> <?= h(SITE_ADRESSE) ?></span><br>
                        <span class="meta-icone"><i class="fa-solid fa-phone icone" aria-hidden="true"></i> <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_TELEPHONE)) ?>"><?= h(SITE_TELEPHONE) ?></a></span><br>
                        <span class="meta-icone"><i class="fa-brands fa-whatsapp icone" aria-hidden="true"></i> <a href="<?= h(SITE_WHATSAPP) ?>" target="_blank" rel="noopener noreferrer">WhatsApp : <?= h(SITE_TELEPHONE) ?></a></span><br>
                        <span class="meta-icone"><i class="fa-solid fa-user-tie icone" aria-hidden="true"></i> Imam : <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_IMAM_TELEPHONE)) ?>"><?= h(SITE_IMAM_TELEPHONE) ?></a></span><br>
                        <span class="meta-icone"><i class="fa-solid fa-user-tie icone" aria-hidden="true"></i> Secrétaire : <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_SECRETAIRE_TELEPHONE)) ?>"><?= h(SITE_SECRETAIRE_TELEPHONE) ?></a></span><br>
                        <span class="meta-icone"><i class="fa-solid fa-user-tie icone" aria-hidden="true"></i> Président : <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_PRESIDENT_TELEPHONE)) ?>"><?= h(SITE_PRESIDENT_TELEPHONE) ?></a></span><br>
                        <span class="meta-icone"><i class="fa-solid fa-envelope icone" aria-hidden="true"></i> <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a></span>
                    </p>
                    <a class="bouton bouton-emeraude" href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= rawurlencode(SITE_LAT . ',' . SITE_LNG) ?>" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-route icone" aria-hidden="true"></i> Itinéraire vers la mosquée
                    </a>
                    <div class="footer-social" style="margin-top:10px;">
                        <?php if (SITE_FACEBOOK): ?><a href="<?= h(SITE_FACEBOOK) ?>" aria-label="Facebook" style="border-color:var(--plâtre-200); color:var(--mint-800);"><i class="fa-brands fa-facebook-f icone" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (SITE_INSTAGRAM): ?><a href="<?= h(SITE_INSTAGRAM) ?>" aria-label="Instagram" style="border-color:var(--plâtre-200); color:var(--mint-800);"><i class="fa-brands fa-instagram icone" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (SITE_YOUTUBE): ?><a href="<?= h(SITE_YOUTUBE) ?>" aria-label="YouTube" style="border-color:var(--plâtre-200); color:var(--mint-800);"><i class="fa-brands fa-youtube icone" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (SITE_WHATSAPP): ?><a href="<?= h(SITE_WHATSAPP) ?>" aria-label="WhatsApp" style="border-color:var(--plâtre-200); color:var(--mint-800);"><i class="fa-brands fa-whatsapp icone" aria-hidden="true"></i></a><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="carte" style="aspect-ratio:4/3;">
                <iframe
                    style="width:100%; height:100%; border:0;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.google.com/maps?q=<?= h(SITE_LAT) ?>,<?= h(SITE_LNG) ?>&z=15&output=embed">
                </iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
