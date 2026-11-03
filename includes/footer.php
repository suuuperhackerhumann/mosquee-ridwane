</main>

    <footer class="site-footer">
        <div class="conteneur">
            <svg class="motif-etoile footer-motif" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M20 3 L23.5 14.5 L35 14.5 L25.7 21.5 L29.2 33 L20 26 L10.8 33 L14.3 21.5 L5 14.5 L16.5 14.5 Z"/>
            </svg>
            <div class="footer-grille">
                <div>
                    <img class="footer-mosquee-logo" src="<?= BASE_URL ?>/assets/img/logo-mosquee.png" alt="Emblème de la Communauté Musulmane de la Riviera Abatta" loading="lazy">
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
                        <li><a href="<?= BASE_URL ?>/">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/a-propos">À propos</a></li>
                        <li><a href="<?= BASE_URL ?>/horaires">Horaires</a></li>
                        <li><a href="<?= BASE_URL ?>/enseignements">Enseignements</a></li>
                        <li><a href="<?= BASE_URL ?>/services">Services</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Participer</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/evenements">Événements</a></li>
                        <li><a href="<?= BASE_URL ?>/actualites">Actualités</a></li>
                        <li><a href="<?= BASE_URL ?>/dons">Faire un don</a></li>
                        <li><a href="<?= BASE_URL ?>/mediatheque">Médiathèque</a></li>
                        <li><a href="<?= BASE_URL ?>/discussions">Discussion</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul class="footer-contact-list">
                        <li>
                            <a class="footer-contact-action" href="https://www.google.com/maps/search/?api=1&amp;query=<?= rawurlencode(SITE_ADRESSE) ?>" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                <span><?= h(SITE_ADRESSE) ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="footer-contact-action footer-contact-whatsapp" href="<?= h(SITE_WHATSAPP) ?>" target="_blank" rel="noopener noreferrer">
                                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                                <span>Écrire sur WhatsApp</span>
                            </a>
                        </li>
                        <li>
                            <a class="footer-contact-action" href="tel:<?= h(preg_replace('/\s+/', '', SITE_TELEPHONE)) ?>">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                <span>Téléphone : <?= h(SITE_TELEPHONE) ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="footer-contact-action" href="tel:<?= h(preg_replace('/\s+/', '', SITE_IMAM_TELEPHONE)) ?>">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                <span>Imam : <?= h(SITE_IMAM_TELEPHONE) ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="footer-contact-action" href="tel:<?= h(preg_replace('/\s+/', '', SITE_SECRETAIRE_TELEPHONE)) ?>">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                <span>Secrétaire : <?= h(SITE_SECRETAIRE_TELEPHONE) ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="footer-contact-action" href="tel:<?= h(preg_replace('/\s+/', '', SITE_PRESIDENT_TELEPHONE)) ?>">
                                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                <span>Président : <?= h(SITE_PRESIDENT_TELEPHONE) ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="footer-contact-action" href="mailto:<?= h(SITE_EMAIL) ?>">
                                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                <span><?= h(SITE_EMAIL) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bas">
                <span>© <?= date('Y') ?> Mosquée Ridwane. Tous droits réservés.</span>
                <span class="footer-signature">
                    <span>Site conçu pour servir la communauté. · Made by</span>
                    <a href="https://cd-solutions.freedev.app/?i=3" target="_blank" rel="noopener noreferrer" aria-label="CDsolutions — ouvrir le site">
                        <img src="<?= h(siteAssetUrl('/assets/img/logo-cd.jpeg')) ?>" alt="CDsolutions">
                        <span>CDsolutions</span>
                    </a>
                </span>
            </div>
        </div>
    </footer>

    <button type="button" class="vers-haut" id="vers-haut" aria-label="Retour en haut de page">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </button>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
