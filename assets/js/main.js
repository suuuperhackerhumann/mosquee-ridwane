document.addEventListener('DOMContentLoaded', function () {
    // --- Menu mobile ---
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.nav-principale');

    function fermerMenu() {
        if (!nav) return;
        nav.classList.remove('ouvert');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var ouvert = nav.classList.toggle('ouvert');
            toggle.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
        });

        // Ferme le menu quand on choisit un lien (mobile)
        nav.querySelectorAll('a').forEach(function (lien) {
            lien.addEventListener('click', fermerMenu);
        });

        // Ferme le menu avec la touche Échap
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') fermerMenu();
        });
    }

    // --- Ombre portée sur l'en-tête au défilement ---
    var entete = document.getElementById('entete');
    if (entete) {
        var appliquerOmbre = function () {
            entete.classList.toggle('defile', window.scrollY > 8);
        };
        appliquerOmbre();
        window.addEventListener('scroll', appliquerOmbre, { passive: true });
    }

    // --- Bouton retour en haut ---
    var versHaut = document.getElementById('vers-haut');
    if (versHaut) {
        window.addEventListener('scroll', function () {
            versHaut.classList.toggle('visible', window.scrollY > 480);
        }, { passive: true });
        versHaut.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- Met en évidence la prochaine prière dans le bloc horaires de la page d'accueil ---
    var items = document.querySelectorAll('[data-heure-priere]');
    if (items.length) {
        var maintenant = new Date();
        var minutesActuelles = maintenant.getHours() * 60 + maintenant.getMinutes();
        var prochain = null;
        var ecartMin = Infinity;

        items.forEach(function (el) {
            var valeur = el.getAttribute('data-heure-priere'); // format HH:MM
            if (!valeur) return;
            var parts = valeur.split(':');
            var minutes = parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
            var ecart = minutes - minutesActuelles;
            if (ecart >= 0 && ecart < ecartMin) {
                ecartMin = ecart;
                prochain = el;
            }
        });
        // Si toutes les prières du jour sont passées, on met en avant la première (Fajr du lendemain)
        if (!prochain && items.length) prochain = items[0];
        if (prochain) prochain.classList.add('prochaine');
    }
});
