<?php
require_once __DIR__ . '/config/config.php';
$page_titre = 'Services';
$page_description = 'Découvrez les services religieux de la Mosquée Ridwane à Riviera Abatta, Abidjan et contactez-nous directement sur WhatsApp.';
$services = $pdo->query("SELECT * FROM services ORDER BY ordre ASC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Services</div>
        <h1>Services de la mosquée</h1>
        <p style="color:var(--plâtre-100); max-width:560px;">La mosquée accompagne les fidèles dans les grandes étapes de la vie et au quotidien.</p>
    </div>
</section>

<section class="section">
    <div class="conteneur grille grille-2">
        <?php foreach ($services as $s): ?>
        <div class="carte">
            <div class="carte-corps">
                <h3><?= h($s['titre']) ?></h3>
                <p class="resume"><?= h($s['description']) ?></p>
                <?php $messageWhatsApp = 'Bonjour, je souhaite avoir des informations sur le service « ' . $s['titre'] . ' » de la Mosquée Ridwane.'; ?>
                <a href="<?= h(whatsappMessageUrl($messageWhatsApp)) ?>" class="carte-lien" target="_blank" rel="noopener noreferrer">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Contacter sur WhatsApp pour ce service
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$services): ?>
    <p>Les informations sur les services seront bientôt disponibles.</p>
    <?php endif; ?>
    <p style="margin-top:28px; color:var(--encre-600); font-size:0.9rem;">Choisissez un service pour ouvrir WhatsApp avec un message déjà préparé. Vous pourrez le modifier avant de l’envoyer.</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
