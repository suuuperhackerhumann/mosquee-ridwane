    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.admin-menu-toggle');
    var sidebar = document.getElementById('admin-navigation');
    var overlay = document.querySelector('.admin-sidebar-overlay');
    var compact = window.matchMedia('(max-width: 800px)');

    if (!toggle || !sidebar || !overlay) return;

    function synchroniserMenu(ouvert) {
        var affiche = compact.matches && ouvert;
        document.body.classList.toggle('admin-menu-ouvert', affiche);
        sidebar.classList.toggle('ouvert', affiche);
        overlay.classList.toggle('visible', affiche);
        sidebar.toggleAttribute('inert', compact.matches && !affiche);
        sidebar.setAttribute('aria-hidden', compact.matches && !affiche ? 'true' : 'false');
        toggle.setAttribute('aria-expanded', affiche ? 'true' : 'false');
        toggle.setAttribute('aria-label', affiche ? 'Fermer le menu d’administration' : 'Ouvrir le menu d’administration');
        toggle.innerHTML = '<i class="fa-solid ' + (affiche ? 'fa-xmark' : 'fa-bars') + '" aria-hidden="true"></i>';
    }

    synchroniserMenu(false);
    toggle.addEventListener('click', function () {
        synchroniserMenu(!sidebar.classList.contains('ouvert'));
    });
    overlay.addEventListener('click', function () { synchroniserMenu(false); });
    sidebar.querySelectorAll('a, button[type="submit"]').forEach(function (element) {
        element.addEventListener('click', function () { synchroniserMenu(false); });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && sidebar.classList.contains('ouvert')) {
            synchroniserMenu(false);
            toggle.focus();
        }
    });
    var changementTaille = function () { synchroniserMenu(false); };
    if (compact.addEventListener) compact.addEventListener('change', changementTaille);
    else compact.addListener(changementTaille);
});
</script>
</body>
</html>
