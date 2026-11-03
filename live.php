<?php
require_once __DIR__ . '/config/config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    if (!empty($_POST['site_web'] ?? '')) {
        redirect('/live');
    }

    $nomBrut = $_POST['nom_auteur'] ?? '';
    $contenuBrut = $_POST['contenu'] ?? '';
    if (!is_string($nomBrut) || !is_string($contenuBrut)) {
        $erreur = 'Le formulaire contient des données invalides.';
    }

    $nom = is_string($nomBrut) ? trim($nomBrut) : '';
    $contenu = is_string($contenuBrut) ? trim($contenuBrut) : '';

    if (!$erreur && mb_strlen($nom) > 80) {
        $erreur = 'Le nom ou pseudonyme ne doit pas dépasser 80 caractères.';
    } elseif (!$erreur && ($contenu === '' || mb_strlen($contenu) > 1500)) {
        $erreur = 'La question est obligatoire et ne doit pas dépasser 1 500 caractères.';
    } elseif (!$erreur && isset($_SESSION['live_question_last_sent']) && time() - (int) $_SESSION['live_question_last_sent'] < 10) {
        $erreur = 'Merci d’attendre quelques secondes avant d’envoyer une autre question.';
    } elseif (!$erreur) {
        $stmt = $pdo->query("SELECT id FROM live_sessions WHERE statut = 'actif' ORDER BY date_debut DESC LIMIT 1");
        $sessionId = $stmt->fetchColumn();

        if (!$sessionId) {
            $erreur = 'La session en direct vient de se terminer. Merci de réessayer plus tard.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO live_questions (session_id, nom_auteur, contenu) VALUES (?, ?, ?)");
            $stmt->execute([(int) $sessionId, $nom !== '' ? $nom : null, $contenu]);
            $_SESSION['live_question_last_sent'] = time();
            redirect('/live?question=envoyee');
        }
    }
}

$session = $pdo->query("SELECT id, titre, statut FROM live_sessions ORDER BY (statut = 'actif') DESC, date_debut DESC LIMIT 1")->fetch();
$questions = [];
if ($session) {
    $stmt = $pdo->prepare('SELECT id, nom_auteur, contenu, statut, date_creation FROM live_questions WHERE session_id = ? ORDER BY date_creation DESC LIMIT 100');
    $stmt->execute([(int) $session['id']]);
    $questions = $stmt->fetchAll();
}
$questionsInitiales = array_map(static function ($question) {
    return [
        'id' => (int) $question['id'],
        'nom' => $question['nom_auteur'] ?: 'Anonyme',
        'contenu' => $question['contenu'],
        'statut' => $question['statut'],
        'date' => formatDateFr($question['date_creation'], true),
    ];
}, $questions);

$page_titre = 'Questions en direct';
$page_description = 'Posez votre question pendant une cérémonie en direct à la Mosquée Ridwane. L’imam la recevra et pourra y répondre au micro.';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-entete">
    <div class="conteneur">
        <div class="fil-ariane"><a href="<?= BASE_URL ?>/index.php" style="color:inherit;">Accueil</a> / Questions en direct</div>
        <h1>Questions en direct</h1>
        <p style="color:var(--plâtre-100);">Posez votre question pendant une cérémonie. L’imam pourra la lire et y répondre oralement au micro.</p>
    </div>
</section>

<section class="section">
    <div class="conteneur live-conteneur">
        <?php if ($erreur): ?><div class="alerte alerte-erreur"><?= h($erreur) ?></div><?php endif; ?>
        <?php if (($_GET['question'] ?? '') === 'envoyee'): ?><div class="alerte alerte-succes">Votre question a été envoyée à l’imam.</div><?php endif; ?>

        <?php if ($session && $session['statut'] === 'actif'): ?>
        <div class="live-bandeau" id="live-bandeau" data-session-id="<?= (int) $session['id'] ?>">
            <span class="live-indicateur"><span></span> En direct</span>
            <h2 id="live-titre"><?= h($session['titre']) ?></h2>
            <p class="live-bandeau-description">Les questions sont visibles par les participants. Lorsqu’il a lu une question et prévoit d’y répondre, l’imam la signalera ici. Les réponses se font au micro.</p>
        </div>

        <div class="live-grille">
            <div class="discussion-formulaire">
                <h2>Poser une question</h2>
                <p>Votre nom est facultatif. Évitez de partager des informations personnelles ou confidentielles.</p>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <div class="champ">
                        <label for="nom-live">Nom ou pseudonyme <span>(facultatif)</span></label>
                        <input type="text" id="nom-live" name="nom_auteur" maxlength="80" autocomplete="nickname" placeholder="Laissez vide pour rester anonyme">
                    </div>
                    <div class="champ">
                        <label for="question-live">Votre question</label>
                        <textarea id="question-live" name="contenu" rows="5" maxlength="1500" required></textarea>
                    </div>
                    <div class="champ discussion-piege" aria-hidden="true">
                        <label for="site-web-live">Ne pas remplir</label>
                        <input type="text" id="site-web-live" name="site_web" tabindex="-1" autocomplete="off">
                    </div>
                    <button type="submit" class="bouton bouton-emeraude">Envoyer la question</button>
                </form>
            </div>

            <div class="live-questions">
                <h2>Questions des participants</h2>
                <div id="live-liste-questions" class="live-liste-questions" aria-live="polite">
                    <?php foreach ($questions as $question): ?>
                    <article class="live-question<?= $question['statut'] === 'lue' ? ' live-question-lue' : '' ?>" data-question-id="<?= (int) $question['id'] ?>">
                        <p class="discussion-meta"><?= h($question['nom_auteur'] ?: 'Anonyme') ?> · <?= h(formatDateFr($question['date_creation'], true)) ?></p>
                        <p class="discussion-message"><?= nl2br(h($question['contenu'])) ?></p>
                        <?php if ($question['statut'] === 'lue'): ?><p class="live-question-statut"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> L’imam a lu cette question et va y répondre au micro.</p><?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                    <?php if (!$questions): ?><p class="live-vide">Soyez le premier à poser une question.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="live-inactif">
            <i class="fa-regular fa-circle-pause" aria-hidden="true"></i>
            <h2>Aucune session en direct pour le moment</h2>
            <p>Lorsqu’une cérémonie avec questions-réponses sera ouverte, vous pourrez revenir ici pour envoyer votre question à l’imam.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
(() => {
    const activeSessionId = document.getElementById('live-bandeau')?.dataset.sessionId || '';
    const lastSessionId = <?= $session ? (int) $session['id'] : 0 ?>;
    const lastSessionStatus = <?= json_encode($session['statut'] ?? '') ?>;
    const list = document.getElementById('live-liste-questions');
    let lastQuestionsSnapshot = <?= json_encode($questionsInitiales, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const refresh = async () => {
        try {
            const response = await fetch('<?= BASE_URL ?>/live-status.php', { cache: 'no-store' });
            if (!response.ok) throw new Error('Impossible d’actualiser les questions.');
            const data = await response.json();
            const currentSessionId = data.session ? String(data.session.id) : '0';
            const currentSessionStatus = data.session ? data.session.statut : '';
            const knownSessionId = activeSessionId || String(lastSessionId);
            if (currentSessionId !== knownSessionId || currentSessionStatus !== lastSessionStatus) {
                window.location.reload();
                return;
            }
            if (!list || !data.session || data.session.statut !== 'actif') return;
            if (JSON.stringify(data.questions) === JSON.stringify(lastQuestionsSnapshot)) return;

            lastQuestionsSnapshot = data.questions;
            document.getElementById('live-titre').textContent = data.session.titre;
            list.replaceChildren();
            if (!data.questions.length) {
                const empty = document.createElement('p');
                empty.className = 'live-vide';
                empty.textContent = 'Soyez le premier à poser une question.';
                list.append(empty);
                return;
            }
            for (const question of data.questions) {
                const article = document.createElement('article');
                article.className = `live-question${question.statut === 'lue' ? ' live-question-lue' : ''}`;
                article.dataset.questionId = String(question.id);
                const meta = document.createElement('p');
                meta.className = 'discussion-meta';
                meta.textContent = `${question.nom} · ${question.date}`;
                const content = document.createElement('p');
                content.className = 'discussion-message';
                content.textContent = question.contenu;
                article.append(meta, content);
                if (question.statut === 'lue') {
                    const status = document.createElement('p');
                    status.className = 'live-question-statut';
                    const icon = document.createElement('i');
                    icon.className = 'fa-solid fa-circle-check';
                    icon.setAttribute('aria-hidden', 'true');
                    status.append(icon, document.createTextNode(' L’imam a lu cette question et va y répondre au micro.'));
                    article.append(status);
                }
                list.append(article);
            }
        } catch (error) {
            console.error(error);
        }
    };
    window.setInterval(refresh, 10000);
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
