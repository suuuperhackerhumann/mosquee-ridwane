document.addEventListener('DOMContentLoaded', function () {
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
