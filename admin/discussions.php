<?php
require_once __DIR__ . '/../config/config.php';
requireAdminPermission('discussions');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    if (isset($_POST['action_live']) && $_POST['action_live'] === 'demarrer') {
        $titreLive = $_POST['titre_live'] ?? '';
        $titreLive = is_string($titreLive) ? trim($titreLive) : '';
        if ($titreLive === '' || mb_strlen($titreLive) > 150) {
            http_response_code(400);
            exit('Le titre de la session est obligatoire et ne doit pas dépasser 150 caractères.');
        }
        $active = $pdo->query("SELECT id FROM live_sessions WHERE statut = 'actif' LIMIT 1")->fetchColumn();
        if ($active) {
            redirect('/admin/discussions?live=deja-actif#live-questions');
        }
        $stmt = $pdo->prepare("INSERT INTO live_sessions (titre, statut) VALUES (?, 'actif')");
        $stmt->execute([$titreLive]);
        redirect('/admin/discussions?live=demarre#live-questions');
    }
    if (isset($_POST['action_live']) && $_POST['action_live'] === 'terminer') {
        $sessionId = filter_var($_POST['session_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($sessionId === false) {
            http_response_code(400);
            exit('Session invalide.');
        }
        $stmt = $pdo->prepare("UPDATE live_sessions SET statut = 'termine', date_fin = CURRENT_TIMESTAMP WHERE id = ? AND statut = 'actif'");
        $stmt->execute([$sessionId]);
        redirect('/admin/discussions?live=termine#live-questions');
    }
    if (isset($_POST['action_live']) && $_POST['action_live'] === 'statut_question') {
        $questionId = filter_var($_POST['question_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $statutQuestion = $_POST['statut_question'] ?? '';
        if ($questionId === false || !in_array($statutQuestion, ['en_attente', 'lue'], true)) {
            http_response_code(400);
            exit('Question ou statut invalide.');
        }
        $stmt = $pdo->prepare("UPDATE live_questions SET statut = ?, date_lecture = CASE WHEN ? = 'lue' THEN CURRENT_TIMESTAMP ELSE NULL END WHERE id = ?");
        $stmt->execute([$statutQuestion, $statutQuestion, $questionId]);
        redirect('/admin/discussions?live=question#live-questions');
    }
    if (isset($_POST['supprimer_discussion'])) {
        $discussionId = filter_var($_POST['discussion_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($discussionId === false) {
            http_response_code(400);
            exit('Discussion invalide.');
        }
        $stmt = $pdo->prepare('DELETE FROM discussions WHERE id = ?');
        $stmt->execute([$discussionId]);
        redirect('/admin/discussions?supprime=discussion');
    }
    if (isset($_POST['supprimer_reponse'])) {
        $reponseId = filter_var($_POST['reponse_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($reponseId === false) {
            http_response_code(400);
            exit('Réponse invalide.');
        }
        $stmt = $pdo->prepare('DELETE FROM discussions_reponses WHERE id = ?');
        $stmt->execute([$reponseId]);
        redirect('/admin/discussions?supprime=reponse');
    }
}

$discussions = $pdo->query('SELECT * FROM discussions ORDER BY date_creation DESC LIMIT 100')->fetchAll();
$sessionLive = $pdo->query("SELECT id, titre, statut, date_debut FROM live_sessions ORDER BY (statut = 'actif') DESC, date_debut DESC LIMIT 1")->fetch();
$questionsLive = [];
if ($sessionLive) {
    $stmt = $pdo->prepare('SELECT id, nom_auteur, contenu, statut, date_creation FROM live_questions WHERE session_id = ? ORDER BY date_creation DESC LIMIT 100');
    $stmt->execute([(int) $sessionLive['id']]);
    $questionsLive = $stmt->fetchAll();
}
$questionsLiveInitiales = array_map(static function ($question) {
    return [
        'id' => (int) $question['id'],
        'nom' => $question['nom_auteur'] ?: 'Anonyme',
        'contenu' => $question['contenu'],
        'statut' => $question['statut'],
        'date' => formatDateFr($question['date_creation'], true),
    ];
}, $questionsLive);
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

$page_admin = 'discussions';
$titre_admin = 'Discussions';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-entete"><h1>Discussions</h1></div>
<?php if (($_GET['supprime'] ?? '') === 'discussion'): ?><div class="alerte alerte-succes">La discussion et ses réponses ont été supprimées.</div><?php endif; ?>
<?php if (($_GET['supprime'] ?? '') === 'reponse'): ?><div class="alerte alerte-succes">La réponse a été supprimée.</div><?php endif; ?>
<?php if (($_GET['live'] ?? '') === 'demarre'): ?><div class="alerte alerte-succes">La session de questions-réponses est en direct.</div><?php endif; ?>
<?php if (($_GET['live'] ?? '') === 'deja-actif'): ?><div class="alerte alerte-erreur">Une session est déjà en direct.</div><?php endif; ?>
<?php if (($_GET['live'] ?? '') === 'termine'): ?><div class="alerte alerte-succes">La session en direct est terminée.</div><?php endif; ?>
<?php if (($_GET['live'] ?? '') === 'question'): ?><div class="alerte alerte-succes">Le statut de la question a été mis à jour.</div><?php endif; ?>

<section class="admin-carte live-admin" id="live-questions">
    <div class="discussion-admin-entete">
        <div>
            <h2>Questions en direct</h2>
            <?php if ($sessionLive && $sessionLive['statut'] === 'actif'): ?>
            <p class="discussion-meta"><span class="live-admin-indicateur">En direct</span> <?= h($sessionLive['titre']) ?> · Depuis le <?= h(formatDateFr($sessionLive['date_debut'], true)) ?></p>
            <?php elseif ($sessionLive): ?>
            <p class="discussion-meta">Dernière session : <?= h($sessionLive['titre']) ?> · <?= h(formatDateFr($sessionLive['date_debut'], true)) ?></p>
            <?php else: ?>
            <p class="discussion-meta">Aucune session n’a encore été lancée.</p>
            <?php endif; ?>
        </div>
        <?php if ($sessionLive && $sessionLive['statut'] === 'actif'): ?>
        <form method="post" onsubmit="return confirm('Terminer la session de questions en direct ?');">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="session_id" value="<?= (int) $sessionLive['id'] ?>">
            <button type="submit" name="action_live" value="terminer" class="bouton bouton-danger">Terminer le live</button>
        </form>
        <?php else: ?>
        <form method="post" class="live-admin-demarrer">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <label for="titre-live-admin">Titre de la session</label>
            <input type="text" id="titre-live-admin" name="titre_live" maxlength="150" value="Questions-réponses en direct" required>
            <button type="submit" name="action_live" value="demarrer" class="bouton bouton-emeraude">Démarrer le live</button>
        </form>
        <?php endif; ?>
    </div>
    <p>Les visiteurs peuvent envoyer des questions anonymement ou sous un pseudonyme. Cochez « Lue » lorsqu’une question a été lue et que l’imam prévoit d’y répondre oralement au micro ; cette indication apparaîtra aussi aux participants.</p>

    <?php if ($questionsLive || ($sessionLive && $sessionLive['statut'] === 'actif')): ?>
    <div class="live-admin-liste" id="live-admin-liste">
        <?php foreach ($questionsLive as $question): ?>
        <article class="live-admin-question<?= $question['statut'] === 'lue' ? ' live-admin-question-lue' : '' ?>" data-question-id="<?= (int) $question['id'] ?>">
            <div class="discussion-admin-entete">
                <p class="discussion-meta"><?= h($question['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($question['date_creation'], true)) ?></p>
                <?php if ($question['statut'] === 'lue'): ?>
                <span class="live-question-statut"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Lue — réponse orale prévue</span>
                <?php else: ?>
                <span class="live-admin-attente">À lire</span>
                <?php endif; ?>
            </div>
            <p class="discussion-message"><?= nl2br(h($question['contenu'])) ?></p>
            <?php if ($sessionLive['statut'] === 'actif'): ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="question_id" value="<?= (int) $question['id'] ?>">
                <?php if ($question['statut'] === 'lue'): ?>
                <button type="submit" name="action_live" value="statut_question" class="bouton-lien" aria-label="Remettre cette question en attente">Remettre en attente</button>
                <input type="hidden" name="statut_question" value="en_attente">
                <?php else: ?>
                <input type="hidden" name="statut_question" value="lue">
                <button type="submit" name="action_live" value="statut_question" class="bouton bouton-emeraude bouton-petit">Marquer comme lue — réponse orale prévue</button>
                <?php endif; ?>
            </form>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
        <?php if (!$questionsLive): ?><p class="live-vide">Aucune question pour cette session.</p><?php endif; ?>
    </div>
    <?php else: ?>
    <p class="live-vide">Aucune question pour cette session.</p>
    <?php endif; ?>
</section>

<?php foreach ($discussions as $discussion): ?>
<article class="admin-carte discussion-admin">
    <div class="discussion-admin-entete">
        <div>
            <h2><?= h($discussion['titre']) ?></h2>
            <p class="discussion-meta"><?= h($discussion['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($discussion['date_creation'], true)) ?></p>
        </div>
        <form method="post" onsubmit="return confirm('Supprimer cette discussion et toutes ses réponses ?');">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="discussion_id" value="<?= (int) $discussion['id'] ?>">
            <button type="submit" name="supprimer_discussion" class="bouton-lien lien-suppr">Supprimer la discussion</button>
        </form>
    </div>
    <p class="discussion-message"><?= nl2br(h($discussion['contenu'])) ?></p>
    <?php foreach ($reponsesParDiscussion[$discussion['id']] ?? [] as $reponse): ?>
    <div class="discussion-reponse discussion-reponse-admin">
        <div class="discussion-admin-entete">
            <p class="discussion-meta"><?= h($reponse['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($reponse['date_creation'], true)) ?></p>
            <form method="post" onsubmit="return confirm('Supprimer cette réponse ?');">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="reponse_id" value="<?= (int) $reponse['id'] ?>">
                <button type="submit" name="supprimer_reponse" class="bouton-lien lien-suppr">Supprimer la réponse</button>
            </form>
        </div>
        <p class="discussion-message"><?= nl2br(h($reponse['contenu'])) ?></p>
    </div>
    <?php endforeach; ?>
</article>
<?php endforeach; ?>
<?php if (!$discussions): ?><div class="admin-carte">Aucune discussion pour le moment.</div><?php endif; ?>

<script>
(() => {
    const initialSessionId = <?= $sessionLive ? (int) $sessionLive['id'] : 0 ?>;
    const initialSessionStatus = <?= json_encode($sessionLive['statut'] ?? '') ?>;
    const csrfToken = <?= json_encode(csrfToken()) ?>;
    const list = document.getElementById('live-admin-liste');
    let lastQuestionsSnapshot = <?= json_encode($questionsLiveInitiales, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const refresh = async () => {
        try {
            const response = await fetch('<?= BASE_URL ?>/live-status.php?admin=1', { cache: 'no-store' });
            if (!response.ok) throw new Error('Impossible d’actualiser les questions du live.');
            const data = await response.json();
            const sessionId = data.session ? Number(data.session.id) : 0;
            const sessionStatus = data.session ? data.session.statut : '';
            if (sessionId !== initialSessionId || sessionStatus !== initialSessionStatus) {
                window.location.reload();
                return;
            }
            if (!list || !data.session || sessionStatus !== 'actif') return;
            if (JSON.stringify(data.questions) === JSON.stringify(lastQuestionsSnapshot)) return;

            lastQuestionsSnapshot = data.questions;
            list.replaceChildren();
            if (!data.questions.length) {
                const empty = document.createElement('p');
                empty.className = 'live-vide';
                empty.textContent = 'Aucune question pour cette session.';
                list.append(empty);
                return;
            }
            for (const question of data.questions) {
                const article = document.createElement('article');
                article.className = `live-admin-question${question.statut === 'lue' ? ' live-admin-question-lue' : ''}`;
                article.dataset.questionId = String(question.id);
                const header = document.createElement('div');
                header.className = 'discussion-admin-entete';
                const meta = document.createElement('p');
                meta.className = 'discussion-meta';
                meta.textContent = `${question.nom} · ${question.date}`;
                header.append(meta);
                const status = document.createElement('span');
                if (question.statut === 'lue') {
                    status.className = 'live-question-statut';
                    const icon = document.createElement('i');
                    icon.className = 'fa-solid fa-circle-check';
                    icon.setAttribute('aria-hidden', 'true');
                    status.append(icon, document.createTextNode(' Lue — réponse orale prévue'));
                } else {
                    status.className = 'live-admin-attente';
                    status.textContent = 'À lire';
                }
                header.append(status);
                const content = document.createElement('p');
                content.className = 'discussion-message';
                content.textContent = question.contenu;
                article.append(header, content);

                const form = document.createElement('form');
                form.method = 'post';
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = 'csrf_token';
                token.value = csrfToken;
                const id = document.createElement('input');
                id.type = 'hidden';
                id.name = 'question_id';
                id.value = String(question.id);
                const nextStatus = document.createElement('input');
                nextStatus.type = 'hidden';
                nextStatus.name = 'statut_question';
                const button = document.createElement('button');
                button.type = 'submit';
                button.name = 'action_live';
                button.value = 'statut_question';
                if (question.statut === 'lue') {
                    nextStatus.value = 'en_attente';
                    button.className = 'bouton-lien';
                    button.textContent = 'Remettre en attente';
                } else {
                    nextStatus.value = 'lue';
                    button.className = 'bouton bouton-emeraude bouton-petit';
                    button.textContent = 'Marquer comme lue — réponse orale prévue';
                }
                form.append(token, id, nextStatus, button);
                article.append(form);
                list.append(article);
            }
        } catch (error) {
            console.error(error);
        }
    };
    window.setInterval(refresh, 10000);
})();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
