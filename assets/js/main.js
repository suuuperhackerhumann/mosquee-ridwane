document.addEventListener('DOMContentLoaded', function () {
    var revisionMeta = document.querySelector('meta[name="site-revision"]');
    var revisionUrlMeta = document.querySelector('meta[name="site-revision-url"]');
    if (revisionMeta && revisionUrlMeta) {
        var revision = revisionMeta.content;
        var revisionUrl = revisionUrlMeta.content;
        function formulaireModifie() {
            return Array.prototype.some.call(document.querySelectorAll('input, textarea, select'), function (champ) {
                if (champ.disabled || champ.type === 'submit' || champ.type === 'button' || champ.type === 'reset') return false;
                if (champ === document.activeElement) return true;
                if (champ.type === 'checkbox' || champ.type === 'radio') return champ.checked !== champ.defaultChecked;
                if (champ.type === 'file') return champ.files && champ.files.length > 0;
                return champ.value !== champ.defaultValue;
            });
        }
        window.setInterval(function () {
            if (document.visibilityState !== 'visible') return;
            fetch(revisionUrl, { cache: 'no-store', credentials: 'same-origin' })
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (typeof data.revision !== 'string') {
                        throw new Error('Réponse de version invalide');
                    }
                    if (data.revision !== revision && !formulaireModifie()) window.location.reload();
                })
                .catch(function (error) {
                    console.error('Vérification de la mise à jour du site impossible :', error);
                });
        }, 15000);
    }

    // Menu mobile : état accessible, navigation clavier et fermeture au changement de taille.
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav-principale');
    var menuCompact = window.matchMedia('(max-width: 1360px)');

    function synchroniserMenu(ouvert) {
        if (!toggle || !nav) return;
        var affiche = menuCompact.matches && ouvert;
        nav.classList.toggle('ouvert', affiche);
        toggle.setAttribute('aria-expanded', affiche ? 'true' : 'false');
        toggle.setAttribute('aria-label', affiche ? 'Fermer le menu' : 'Ouvrir le menu');
        // Un menu hors écran ne doit pas recevoir le focus clavier.
        if (menuCompact.matches && !affiche) {
            nav.setAttribute('inert', '');
            nav.setAttribute('aria-hidden', 'true');
        } else {
            nav.removeAttribute('inert');
            nav.removeAttribute('aria-hidden');
        }
    }

    if (toggle && nav) {
        synchroniserMenu(false);
        toggle.addEventListener('click', function () {
            synchroniserMenu(!nav.classList.contains('ouvert'));
        });
        nav.querySelectorAll('a').forEach(function (lien) {
            lien.addEventListener('click', function () { synchroniserMenu(false); });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('ouvert')) {
                synchroniserMenu(false);
                toggle.focus();
            }
        });
        var changementTaille = function () { synchroniserMenu(false); };
        if (menuCompact.addEventListener) menuCompact.addEventListener('change', changementTaille);
        else menuCompact.addListener(changementTaille);
    }

    // Ombre portée sur l'en-tête au défilement.
    var entete = document.getElementById('entete');
    if (entete) {
        var appliquerOmbre = function () { entete.classList.toggle('defile', window.scrollY > 8); };
        appliquerOmbre();
        window.addEventListener('scroll', appliquerOmbre, { passive: true });
    }

    var versHaut = document.getElementById('vers-haut');
    if (versHaut) {
        window.addEventListener('scroll', function () {
            versHaut.classList.toggle('visible', window.scrollY > 480);
        }, { passive: true });
        versHaut.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    var mediaPreviewDialog = document.getElementById('media-preview-dialog');
    if (mediaPreviewDialog) {
        var mediaPreviewImage = document.getElementById('media-preview-image');
        var mediaPreviewCaption = document.getElementById('media-preview-caption');
        var videoDialogPlayer = document.getElementById('video-preview-player');
        var videoDialogEmbed = document.getElementById('video-preview-embed');
        mediaPreviewDialog.querySelector('.media-preview-dialog-fermer').addEventListener('click', function () {
            mediaPreviewDialog.close();
        });
        document.querySelectorAll('.media-photo-ouvrir').forEach(function (lien) {
            lien.addEventListener('click', function (event) {
                event.preventDefault();
                var image = lien.querySelector('img');
                mediaPreviewImage.hidden = false;
                videoDialogPlayer.hidden = true;
                videoDialogEmbed.hidden = true;
                videoDialogEmbed.removeAttribute('src');
                mediaPreviewImage.src = lien.href;
                mediaPreviewImage.alt = image.alt;
                mediaPreviewCaption.textContent = image.alt;
                mediaPreviewDialog.showModal();
            });
        });
        document.querySelectorAll('.media-video-ouvrir').forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                mediaPreviewImage.hidden = true;
                mediaPreviewImage.removeAttribute('src');
                mediaPreviewCaption.textContent = bouton.dataset.videoTitre;
                if (bouton.dataset.videoEmbed) {
                    videoDialogPlayer.hidden = true;
                    videoDialogPlayer.pause();
                    videoDialogPlayer.removeAttribute('src');
                    videoDialogPlayer.load();
                    videoDialogEmbed.hidden = false;
                    videoDialogEmbed.title = bouton.dataset.videoTitre;
                    videoDialogEmbed.src = bouton.dataset.videoEmbed;
                } else {
                    videoDialogEmbed.hidden = true;
                    videoDialogEmbed.removeAttribute('src');
                    videoDialogPlayer.hidden = false;
                    videoDialogPlayer.src = bouton.dataset.videoSrc;
                    videoDialogPlayer.load();
                }
                mediaPreviewDialog.showModal();
                if (!bouton.dataset.videoEmbed) {
                    videoDialogPlayer.play().catch(function (error) {
                        console.error('La lecture de la vidéo est impossible :', error);
                    });
                }
            });
        });
        mediaPreviewDialog.addEventListener('click', function (event) {
            if (event.target === mediaPreviewDialog) mediaPreviewDialog.close();
        });
        mediaPreviewDialog.addEventListener('close', function () {
            mediaPreviewImage.removeAttribute('src');
            videoDialogPlayer.pause();
            videoDialogPlayer.removeAttribute('src');
            videoDialogPlayer.load();
            videoDialogEmbed.removeAttribute('src');
        });
    }

    // Mise en évidence de la prochaine prière.
    var items = document.querySelectorAll('[data-heure-priere]');
    if (items.length) {
        var maintenant = new Date();
        var minutesActuelles = maintenant.getHours() * 60 + maintenant.getMinutes();
        var prochain = null;
        var ecartMin = Infinity;
        items.forEach(function (el) {
            var valeur = el.getAttribute('data-heure-priere');
            if (!valeur) return;
            var parts = valeur.split(':');
            var minutes = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
            var ecart = minutes - minutesActuelles;
            if (ecart >= 0 && ecart < ecartMin) {
                ecartMin = ecart;
                prochain = el;
            }
        });
        if (!prochain && items.length) prochain = items[0];
        if (prochain) prochain.classList.add('prochaine');
    }
});
