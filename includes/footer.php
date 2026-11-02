</main>

    <footer class="site-footer">
        <div class="conteneur">
            <svg class="motif-etoile footer-motif" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M20 3 L23.5 14.5 L35 14.5 L25.7 21.5 L29.2 33 L20 26 L10.8 33 L14.3 21.5 L5 14.5 L16.5 14.5 Z"/>
            </svg>
            <div class="footer-grille">
                <div>
                    <div class="footer-logo-emplacement" aria-label="Emplacement du logo de la mosquée">
                        <i class="fa-solid fa-mosque" aria-hidden="true"></i>
                        <span>Logo de la mosquée</span>
                    </div>
                    <h4 style="margin-top:0;">Mosquée Ridwane</h4>
                    <p style="font-size:0.92rem; max-width:280px;"><?= h(SITE_DESCRIPTION) ?></p>
                    <div class="footer-social">
                        <?php if (SITE_FACEBOOK): ?><a href="<?= h(SITE_FACEBOOK) ?>" aria-label="Facebook"><i class="fa-brands fa-facebook-f icone" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (SITE_INSTAGRAM): ?><a href="<?= h(SITE_INSTAGRAM) ?>" aria-label="Instagram"><i class="fa-brands fa-instagram icone" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (SITE_YOUTUBE): ?><a href="<?= h(SITE_YOUTUBE) ?>" aria-label="YouTube"><i class="fa-brands fa-youtube icone" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (SITE_WHATSAPP): ?><a href="<?= h(SITE_WHATSAPP) ?>" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp icone" aria-hidden="true"></i></a><?php endif; ?>
                    </div>
                </div>
                <div>
                    <h4>Découvrir</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/a-propos.php">À propos</a></li>
                        <li><a href="<?= BASE_URL ?>/horaires.php">Horaires</a></li>
                        <li><a href="<?= BASE_URL ?>/enseignements.php">Enseignements</a></li>
                        <li><a href="<?= BASE_URL ?>/services.php">Services</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Participer</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/evenements.php">Événements</a></li>
                        <li><a href="<?= BASE_URL ?>/actualites.php">Actualités</a></li>
                        <li><a href="<?= BASE_URL ?>/dons.php">Faire un don</a></li>
                        <li><a href="<?= BASE_URL ?>/mediatheque.php">Médiathèque</a></li>
                        <li><a href="<?= BASE_URL ?>/discussions.php">Discussion</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul>
                        <li><address><?= h(SITE_ADRESSE) ?></address></li>
                        <li><a href="tel:<?= h(preg_replace('/\s+/', '', SITE_TELEPHONE)) ?>"><?= h(SITE_TELEPHONE) ?></a></li>
                        <li><a href="<?= h(SITE_WHATSAPP) ?>" target="_blank" rel="noopener noreferrer">WhatsApp : <?= h(SITE_TELEPHONE) ?></a></li>
                        <li>Imam : <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_IMAM_TELEPHONE)) ?>"><?= h(SITE_IMAM_TELEPHONE) ?></a></li>
                        <li>Secrétaire : <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_SECRETAIRE_TELEPHONE)) ?>"><?= h(SITE_SECRETAIRE_TELEPHONE) ?></a></li>
                        <li>Président : <a href="tel:<?= h(preg_replace('/\s+/', '', SITE_PRESIDENT_TELEPHONE)) ?>"><?= h(SITE_PRESIDENT_TELEPHONE) ?></a></li>
                        <li><a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bas">
                <span>© <?= date('Y') ?> Mosquée Ridwane. Tous droits réservés.</span>
                <span>Site conçu pour servir la communauté. · Made by <a href="https://cd-solutions.freedev.app/?i=3" target="_blank" rel="noopener noreferrer">CDsolutions</a></span>
            </div>
        </div>
    </footer>

    <button type="button" class="vers-haut" id="vers-haut" aria-label="Retour en haut de page">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
