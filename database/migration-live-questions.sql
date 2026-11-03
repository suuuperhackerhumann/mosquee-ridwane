-- À exécuter une seule fois sur une installation déjà existante.
USE mosquee_ridwane;

CREATE TABLE IF NOT EXISTS live_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    statut ENUM('actif','termine') NOT NULL DEFAULT 'actif',
    date_debut DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_fin DATETIME NULL,
    INDEX idx_live_sessions_statut (statut, date_debut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS live_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    nom_auteur VARCHAR(80),
    contenu TEXT NOT NULL,
    statut ENUM('en_attente','lue') NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_lecture DATETIME NULL,
    INDEX idx_live_questions_session_date (session_id, date_creation),
    CONSTRAINT fk_live_questions_session
        FOREIGN KEY (session_id) REFERENCES live_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
