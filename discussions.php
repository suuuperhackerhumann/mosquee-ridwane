<?php
require_once __DIR__ . '/config/config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if (!empty($_POST['site_web'] ?? '')) {
        redirect('/discussions.php');
    }

    $nomBrut = $_POST['nom_auteur'] ?? '';
    $contenuBrut = $_POST['contenu'] ?? '';
    if (!is_string($nomBrut) || !is_string($contenuBrut)) {
        $erreur = 'Le formulaire contient des données invalides.';
    }
    $nom = is_string($nomBrut) ? trim($nomBrut) : '';
    $contenu = is_string($contenuBrut) ? trim($contenuBrut) : '';
    $discussionId = filter_var($_POST['discussion_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$erreur && mb_strlen($nom) > 80) {
        $erreur = 'Le nom ou pseudonyme ne doit pas dépasser 80 caractères.';
    } elseif (!$erreur && ($contenu === '' || mb_strlen($contenu) > 3000)) {
        $erreur = 'Le message est obligatoire et ne doit pas dépasser 3 000 caractères.';
    } elseif (!$erreur && array_key_exists('discussion_id', $_POST) && $discussionId === false) {
        $erreur = 'La discussion sélectionnée est invalide.';
    } elseif (!$erreur && $discussionId !== false) {
        $stmt = $pdo->prepare('SELECT id FROM discussions WHERE id = ?');
        $stmt->execute([$discussionId]);
        if (!$stmt->fetch()) {
            $erreur = 'Cette discussion n’existe plus.';
        } elseif (!$erreur) {
            $stmt = $pdo->prepare('INSERT INTO discussions_reponses (discussion_id, nom_auteur, contenu) VALUES (?, ?, ?)');
            $stmt->execute([$discussionId, $nom !== '' ? $nom : null, $contenu]);
            redirect('/discussions.php?reponse=1#discussion-' . $discussionId);
        }
    } else {
        $titreBrut = $_POST['titre'] ?? '';
        $titre = is_string($titreBrut) ? trim($titreBrut) : '';
        if (!is_string($titreBrut)) {
            $erreur = 'Le sujet est invalide.';
        }
        if (!$erreur && ($titre === '' || mb_strlen($titre) > 150)) {
            $erreur = 'Le sujet est obligatoire et ne doit pas dépasser 150 caractères.';
        } elseif (!$erreur) {
            $stmt = $pdo->prepare('INSERT INTO discussions (titre, nom_auteur, contenu) VALUES (?, ?, ?)');
            $stmt->execute([$titre, $nom !== '' ? $nom : null, $contenu]);
            redirect('/discussions.php?sujet=1');
        }
    }
}

$discussions = $pdo->query('SELECT * FROM discussions ORDER BY date_creation DESC LIMIT 50')->fetchAll();
$reponsesParDiscussion = [];
if ($discussions) {
    $ids = array_column($discussions, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM discussions_reponses WHERE discussion_id IN ($placeholders) ORDER BY date_creation ASC");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $reponse) {
        $reponsesParDiscussion[$reponse['discussion_id']][] = $reponse;
    }
}

$page_titre = 'Discussions';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Discussion</div>
        <h1>Discussions de la communauté</h1>
        <p style="color:var(--plâtre-100);">Posez vos questions, partagez vos réflexions et échangez avec la communauté.</p>
    </div>
</section>

<section class="section">
    <div class="conteneur">
        <?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>
        <?php if (isset($_GET['sujet'])): ?><div class="alerte alerte-succes">Votre sujet a été publié.</div><?php endif; ?>
        <?php if (isset($_GET['reponse'])): ?><div class="alerte alerte-succes">Votre réponse a été publiée.</div><?php endif; ?>

        <div class="discussions-conteneur">
          <div class="discussion-formulaire">
            <h2>Ouvrir une discussion</h2>
            <p>Vous pouvez publier sous votre nom, un pseudonyme ou rester anonyme. Les messages sont visibles de tous et publiés immédiatement.</p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <div class="champ">
                    <label for="titre">Sujet</label>
                    <input type="text" id="titre" name="titre" maxlength="150" required>
                </div>
                <div class="champ">
                    <label for="nom-auteur">Nom ou pseudonyme <span>(facultatif)</span></label>
                    <input type="text" id="nom-auteur" name="nom_auteur" maxlength="80" autocomplete="nickname" placeholder="Laissez vide pour publier anonymement">
                </div>
                <div class="champ">
                    <label for="contenu">Votre message</label>
                    <textarea id="contenu" name="contenu" rows="5" maxlength="3000" required></textarea>
                </div>
                <div class="champ discussion-piege" aria-hidden="true">
                    <label for="site-web">Ne pas remplir</label>
                    <input type="text" id="site-web" name="site_web" tabindex="-1" autocomplete="off">
                </div>
                <button type="submit" class="bouton bouton-emeraude">Publier le sujet</button>
            </form>
          </div>

          <div class="discussions-liste">
            <h2>Échanges récents</h2>
            <?php foreach ($discussions as $discussion): ?>
            <article class="discussion-sujet" id="discussion-<?= (int) $discussion['id'] ?>">
                <h3><?= h($discussion['titre']) ?></h3>
                <p class="discussion-meta"><?= h($discussion['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($discussion['date_creation'], true)) ?></p>
                <p class="discussion-message"><?= nl2br(h($discussion['contenu'])) ?></p>

                <?php foreach ($reponsesParDiscussion[$discussion['id']] ?? [] as $reponse): ?>
                <div class="discussion-reponse">
                    <p class="discussion-meta"><?= h($reponse['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($reponse['date_creation'], true)) ?></p>
                    <p class="discussion-message"><?= nl2br(h($reponse['contenu'])) ?></p>
                </div>
                <?php endforeach; ?>

                <details class="discussion-repondre">
                    <summary>Répondre</summary>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="discussion_id" value="<?= (int) $discussion['id'] ?>">
                        <div class="champ">
                            <label for="nom-reponse-<?= (int) $discussion['id'] ?>">Nom ou pseudonyme <span>(facultatif)</span></label>
                            <input type="text" id="nom-reponse-<?= (int) $discussion['id'] ?>" name="nom_auteur" maxlength="80" autocomplete="nickname" placeholder="Laissez vide pour répondre anonymement">
                        </div>
                        <div class="champ">
                            <label for="contenu-reponse-<?= (int) $discussion['id'] ?>">Votre réponse</label>
                            <textarea id="contenu-reponse-<?= (int) $discussion['id'] ?>" name="contenu" rows="3" maxlength="3000" required></textarea>
                        </div>
                        <div class="champ discussion-piege" aria-hidden="true">
                            <label for="site-web-reponse-<?= (int) $discussion['id'] ?>">Ne pas remplir</label>
                            <input type="text" id="site-web-reponse-<?= (int) $discussion['id'] ?>" name="site_web" tabindex="-1" autocomplete="off">
                        </div>
                        <button type="submit" class="bouton bouton-emeraude bouton-petit">Publier la réponse</button>
                    </form>
                </details>
            </article>
            <?php endforeach; ?>
            <?php if (!$discussions): ?><p>Aucune discussion pour le moment. Lancez le premier sujet !</p><?php endif; ?>
          </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
