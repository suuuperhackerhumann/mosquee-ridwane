# Site web — Mosquée Ridwane

Site institutionnel en **PHP + MySQL**, sans framework, pensé pour être simple à héberger (compatible InfinityFree, o2switch, Hostinger, etc.) et facile à administrer.

## 1. Installation

1. **Base de données** : crée une base MySQL et importe `database/schema.sql` (phpMyAdmin → Importer, ou en ligne de commande : `mysql -u utilisateur -p nom_base < database/schema.sql`).
2. **Connexion** : ouvre `config/db.php` et renseigne `DB_HOTE`, `DB_NOM`, `DB_UTILISATEUR`, `DB_MOT_DE_PASSE` avec les identifiants fournis par ton hébergeur.
3. **Informations générales** : ouvre `config/config.php` et adapte `SITE_URL`, `SITE_ADRESSE`, `SITE_TELEPHONE`, `SITE_EMAIL`, les réseaux sociaux et les coordonnées GPS (`SITE_LAT` / `SITE_LNG`, pour la carte de la page Contact).
4. **Mise en ligne** : dépose tout le contenu de ce dossier à la racine de ton hébergement (souvent `htdocs/` ou `public_html/`). Vérifie que `assets/uploads/` est accessible en écriture par PHP (droits 755 ou 775).
5. Passe `MODE_DEBUG` à `false` dans `config/config.php` une fois le site en ligne.
6. Vérifie que `SITE_URL` dans `config/config.php` correspond au domaine public canonique avant l’indexation par les moteurs de recherche. Le logo partagé par Open Graph, les données structurées, le sitemap et les URL canoniques utilisent cette valeur ; adapte aussi l’URL du sitemap dans `robots.txt` si le domaine change.
7. Après la mise en ligne, vérifie le domaine dans **Google Search Console**, soumets `https://ton-domaine.tld/sitemap`, puis inspecte les pages principales et les images de partage avec les validateurs de résultats enrichis et d’aperçus sociaux.
8. Les règles Apache de [.htaccess](./.htaccess) séparent les routes admin et publiques. Elles sont réglées pour XAMPP sous `/mosquee-ridwane/` ; à la racine du domaine, adapte les chemins `ErrorDocument` en `/403.php`, `/404.php` et `/erreur-serveur.php`. Les URLs sans extension nécessitent `mod_rewrite` et l’autorisation des règles `.htaccess` (`AllowOverride`). **Nginx ne lit pas `.htaccess`** : utilise le modèle distinct [config/nginx-server.conf](./config/nginx-server.conf), adapte `server_name`, `root` et `fastcgi_pass`, puis charge-le dans la configuration Nginx et teste-la avec `nginx -t` avant de recharger le service. Ne copie pas les règles Apache dans la configuration Nginx. Ne préfixe pas le `location /admin/` avec `^~`, car PHP ne serait alors plus transmis à PHP-FPM. Vérifie que le bloc `server` modifié est celui réellement chargé par Nginx (`nginx -T`). La page serveur autonome gère les erreurs 500, 502, 503 et 504, et une panne de connexion à la base affiche la réponse 503.
9. L’administration utilise des sessions limitées à 30 minutes d’inactivité et 8 heures au total. Les pages admin sont envoyées sans cache navigateur. Après **5 tentatives de connexion échouées**, l’adresse IP concernée est bloquée **15 minutes** (réglable via `ADMIN_LOGIN_MAX_TENTATIVES` et `ADMIN_LOGIN_DUREE_BLOCAGE` dans `includes/functions.php`).
10. **Supprime `setup-admin.php` du serveur** une fois le premier compte créé. Tant qu’il est en ligne, quiconque connaît sa clé `SETUP_SECRET_KEY` peut créer un compte administrateur complet ; si tu dois le garder temporairement, change cette clé.
11. Le [.htaccess](./.htaccess) redirige automatiquement vers **HTTPS** en production (la redirection est ignorée sur `localhost`). Après la mise en ligne, ouvre le site en `http://` pour vérifier la redirection ; si l’hébergeur force déjà HTTPS et qu’une boucle de redirection apparaît, supprime le bloc « Forcer HTTPS ».

### Référencement et visibilité locale

- Les pages partagées utilisent une image Open Graph horizontale ; les fiches d’actualités et d’événements reprennent leur propre image quand elle existe.
- Le balisage structuré décrit la mosquée, sa localisation GPS et les fiches d’actualité et d’événement. Les URLs canoniques, `robots.txt` et le sitemap doivent tous employer la même URL `SITE_URL`. Le dossier `/admin/` est exclu de l’exploration.
- Pour développer la visibilité locale, crée ou revendique la fiche **Google Business Profile**, complète-la avec la même adresse et le même téléphone que le site, ajoute des photos réelles, les horaires vérifiés et un lien vers le site. Encourage les visiteurs à laisser des avis honnêtes, sans contrepartie.
- Publie régulièrement des annonces utiles et originales, avec un titre précis, une date, une image représentative et un texte alternatif descriptif. Vérifie aussi les horaires et coordonnées avant publication.
- Les données structurées améliorent la compréhension du site, mais ne garantissent pas une meilleure position ni l’affichage d’un résultat enrichi.

## 2. Se connecter à l'espace d'administration

URL : `https://ton-domaine.tld/admin/`

Compte créé par défaut dans `database/schema.sql` :
- **E-mail** : `admin@mosquee-ridwane.org`
- **Mot de passe** : `Admin123!`

**Change ce mot de passe dès la première connexion** (ou avant la mise en ligne) : ce mot de passe est public puisqu’il figure dans ce fichier. Le plus simple est de passer par le menu « Mot de passe » de l’administration ; sinon, génère un nouveau hash :

```bash
php -r "echo password_hash('ton-nouveau-mot-de-passe', PASSWORD_DEFAULT);"
```

Colle le résultat dans la colonne `mot_de_passe` de la table `admins` (via phpMyAdmin) à la place du hash existant.

## 3. Ce que permet l'espace d'administration

- Publier / modifier / supprimer des **actualités**
- Créer / modifier / supprimer des **événements**
- Mettre à jour les **horaires de prière** jour par jour (+ textes Jumu'ah et Ramadan)
- Modifier les **textes des pages** (Accueil, À propos, Horaires, Dons, Contact)
- Ajouter / supprimer des **photos et vidéos** dans la médiathèque, y compris des vidéos MP4, WebM ou OGG téléversées depuis l’administration
- Gérer les **discussions publiques** et retirer les sujets ou réponses inappropriés
- Consulter les **messages envoyés depuis le formulaire de contact**
- Créer des **rôles** avec des permissions par rubrique et des **utilisateurs** associés (réservé à l’administrateur)

### Rôles et utilisateurs

Depuis « Utilisateurs et rôles », l’administrateur peut définir ou modifier les tâches autorisées pour chaque rôle : actualités, événements, horaires, contenu des pages, médiathèque, messages et discussions. Il peut aussi modifier les informations et le rôle d’un utilisateur, lui définir un nouveau mot de passe ou supprimer son compte. Le dernier compte administrateur et le compte connecté sont protégés contre la suppression ou la rétrogradation. Les utilisateurs ne voient que les rubriques permises et l’accès est aussi vérifié côté serveur.

Chaque utilisateur connecté peut changer son mot de passe depuis le menu d’administration, après vérification de son mot de passe actuel. Les changements et suppressions sont traités par des formulaires POST protégés contre les requêtes CSRF.

Pour une base déjà installée, importer une seule fois `database/migration-roles.sql`, `database/migration-discussions.sql` et `database/migration-instances-dirigeantes.sql` dans phpMyAdmin après avoir sélectionné la base. La migration des rôles préserve les accès existants. Pour une nouvelle installation, `database/schema.sql` inclut directement les tables nécessaires.

La page d’accueil affiche également un verset coranique en français et en arabe, renouvelé automatiquement chaque jour.

La rubrique « Discussion » est accessible sans compte : les visiteurs peuvent publier un sujet et y répondre sous leur nom, un pseudonyme ou anonymement. Les publications apparaissent immédiatement. Les administrateurs disposant de la permission « Gérer les discussions » peuvent supprimer les sujets et réponses inappropriés.

La page « Questions en direct » permet à un administrateur de lancer et terminer une séance de questions-réponses. Le bouton flottant circulaire « LIVE » apparaît uniquement pendant une séance active. Les visiteurs posent leurs questions sous leur nom ou anonymement ; l’imam répond oralement au micro. Depuis l’administration > Discussions, l’équipe peut marquer une question comme lue et indiquer ainsi aux participants que l’imam prévoit d’y répondre. Les questions et leur statut s’actualisent automatiquement pendant la séance. Pour une base déjà installée, importer une seule fois `database/migration-live-questions.sql` après avoir sélectionné la base. Pour une nouvelle installation, `database/schema.sql` contient déjà ces tables.

Les images et vidéos téléversées depuis l’administration sont limitées à **100 Mo par fichier**. La médiathèque accepte jusqu’à **100 photos** ou **10 vidéos par envoi**. La taille maximale effective dépend aussi de `upload_max_filesize` et `post_max_size` dans la configuration PHP ; elle est affichée dans le formulaire de la médiathèque. Pour autoriser un fichier de 100 Mo, règle par exemple `upload_max_filesize = 100M`, `post_max_size = 110M` et `max_file_uploads = 100` dans le `php.ini` utilisé par Apache, puis redémarre Apache. La taille totale d’un envoi multiple reste soumise à `post_max_size`. Vérifie également les limites de ton hébergeur et de son serveur web en production.

## 4. Structure du projet

```
/config              → configuration (site, base de données)
/includes            → header/footer et fonctions PHP partagées
/admin               → espace d'administration (protégé par mot de passe)
/assets/css          → style.css (site public), admin.css (espace admin)
/assets/js           → menu mobile, mise en avant de la prochaine prière
/assets/uploads      → images téléversées depuis l'administration
/database/schema.sql → structure + contenu de démarrage de la base
*.php à la racine    → pages publiques du site
```

## 5. Évolutions futures (hors version initiale, volontairement)

Paiement en ligne pour les dons, calculateur de Zakat, espace membre, newsletter, inscription en ligne aux cours, prise de rendez-vous en ligne, diffusion en direct, application mobile. La structure du site (PHP/MySQL classique, pages et tables séparées) est conçue pour accueillir ces évolutions sans tout reconstruire.

## 6. Sécurité

- Mots de passe admin hashés (`password_hash`), jamais stockés en clair.
- Protection CSRF sur les formulaires et actions d’administration, notamment les créations, modifications, suppressions, changements de mot de passe et déconnexions.
- Requêtes SQL préparées (PDO) partout — aucune concaténation de variables dans les requêtes.
- `.htaccess` interdisant l'accès direct à `/config`, `/includes`, `/database`, et l'exécution de PHP dans `/assets/uploads`.
- Échappement HTML systématique (`h()`) de tout contenu affiché, y compris les messages publics des discussions et questions en direct.
- Sessions admin sécurisées : cookie `HttpOnly` et `SameSite=Lax`, mode strict, régénération de l’identifiant à la connexion, expiration après inactivité.
- Limitation des tentatives de connexion admin : blocage de 15 minutes par adresse IP après 5 échecs.
- Téléversements contrôlés : extension autorisée **et** contenu réel vérifié (`getimagesize` pour les images, `finfo` pour les vidéos), noms de fichiers aléatoires.
- Formulaires publics robustes : champs de type inattendu rejetés proprement, statuts limités aux valeurs autorisées (`publie` / `brouillon`).
- En-têtes de sécurité HTTP envoyés par `.htaccess` (nécessite `mod_headers`) : `X-Frame-Options` et `frame-ancestors` contre le clickjacking, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, et `Strict-Transport-Security` en HTTPS.
- Redirection automatique vers HTTPS en production. Active un certificat (souvent gratuit via Let's Encrypt chez la plupart des hébergeurs).
- **Avec Nginx**, ces en-têtes et la redirection HTTPS ne sont pas lus depuis `.htaccess` : ajoute les directives `add_header` équivalentes et une redirection `return 301 https://...` dans [config/nginx-server.conf](./config/nginx-server.conf).
- Avant la mise en ligne : changer le mot de passe admin par défaut, supprimer `setup-admin.php` et passer `MODE_DEBUG` à `false`.
