# Site web — Mosquée Ridwane

Site institutionnel en **PHP + MySQL**, sans framework, pensé pour être simple à héberger (compatible InfinityFree, o2switch, Hostinger, etc.) et facile à administrer.

## 1. Installation

1. **Base de données** : crée une base MySQL et importe `database/schema.sql` (phpMyAdmin → Importer, ou en ligne de commande : `mysql -u utilisateur -p nom_base < database/schema.sql`).
2. **Connexion** : ouvre `config/db.php` et renseigne `DB_HOTE`, `DB_NOM`, `DB_UTILISATEUR`, `DB_MOT_DE_PASSE` avec les identifiants fournis par ton hébergeur.
3. **Informations générales** : ouvre `config/config.php` et adapte `SITE_URL`, `SITE_ADRESSE`, `SITE_TELEPHONE`, `SITE_EMAIL`, les réseaux sociaux et les coordonnées GPS (`SITE_LAT` / `SITE_LNG`, pour la carte de la page Contact).
4. **Mise en ligne** : dépose tout le contenu de ce dossier à la racine de ton hébergement (souvent `htdocs/` ou `public_html/`). Vérifie que `assets/uploads/` est accessible en écriture par PHP (droits 755 ou 775).
5. Passe `MODE_DEBUG` à `false` dans `config/config.php` une fois le site en ligne.

## 2. Se connecter à l'espace d'administration

URL : `https://ton-domaine.tld/admin/`

Compte créé par défaut dans `database/schema.sql` :
- **E-mail** : `admin@mosquee-ridwane.org`
- **Mot de passe** : `Admin123!`

**Change ce mot de passe dès la première connexion** (ou avant la mise en ligne) en générant un nouveau hash :

```bash
php -r "echo password_hash('ton-nouveau-mot-de-passe', PASSWORD_DEFAULT);"
```

Colle le résultat dans la colonne `mot_de_passe` de la table `admins` (via phpMyAdmin) à la place du hash existant.

## 3. Ce que permet l'espace d'administration

- Publier / modifier / supprimer des **actualités**
- Créer / modifier / supprimer des **événements**
- Mettre à jour les **horaires de prière** jour par jour (+ textes Jumu'ah et Ramadan)
- Modifier les **textes des pages** (Accueil, À propos, Horaires, Dons, Contact)
- Ajouter / supprimer des **photos et vidéos** dans la médiathèque
- Gérer les **discussions publiques** et retirer les sujets ou réponses inappropriés
- Consulter les **messages envoyés depuis le formulaire de contact**
- Créer des **rôles** avec des permissions par rubrique et des **utilisateurs** associés (réservé à l’administrateur)

### Rôles et utilisateurs

Depuis « Utilisateurs et rôles », l’administrateur peut définir ou modifier les tâches autorisées pour chaque rôle : actualités, événements, horaires, contenu des pages, médiathèque, messages et discussions. Il peut aussi modifier les informations et le rôle d’un utilisateur, lui définir un nouveau mot de passe ou supprimer son compte. Le dernier compte administrateur et le compte connecté sont protégés contre la suppression ou la rétrogradation. Les utilisateurs ne voient que les rubriques permises et l’accès est aussi vérifié côté serveur.

Chaque utilisateur connecté peut changer son mot de passe depuis le menu d’administration, après vérification de son mot de passe actuel. Les changements et suppressions sont traités par des formulaires POST protégés contre les requêtes CSRF.

Pour une base déjà installée, importer une seule fois `database/migration-roles.sql` et `database/migration-discussions.sql` dans phpMyAdmin après avoir sélectionné la base. La migration des rôles préserve les accès existants. Pour une nouvelle installation, `database/schema.sql` inclut directement les tables nécessaires.

La page d’accueil affiche également un verset coranique en français et en arabe, renouvelé automatiquement chaque jour.

La rubrique « Discussion » est accessible sans compte : les visiteurs peuvent publier un sujet et y répondre sous leur nom, un pseudonyme ou anonymement. Les publications apparaissent immédiatement. Les administrateurs disposant de la permission « Gérer les discussions » peuvent supprimer les sujets et réponses inappropriés.

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
- Pense à activer HTTPS (souvent gratuit via Let's Encrypt chez la plupart des hébergeurs) et à changer le mot de passe admin par défaut avant la mise en ligne.
