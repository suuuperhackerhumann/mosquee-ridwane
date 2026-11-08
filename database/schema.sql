-- ============================================================
-- Mosquée Ridwane — Schéma de base de données
-- Compatible MySQL 5.7+ / MariaDB (InfinityFree, o2switch, etc.)
-- ============================================================


CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    role_key VARCHAR(120) NOT NULL UNIQUE,
    est_systeme TINYINT(1) NOT NULL DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission VARCHAR(50) NOT NULL,
    PRIMARY KEY (role_id, permission),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO roles (id, nom, role_key, est_systeme)
VALUES (1, 'Administrateur', 'administrateur', 1);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    role_id INT NOT NULL DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compte admin par défaut : email admin@mosquee-ridwane.org / mot de passe Admin123!
-- (change le mot de passe hashé via setup.php, voir README)
INSERT INTO admins (nom, email, mot_de_passe, role, role_id) VALUES
('Administrateur', 'admin@mosquee-ridwane.org', '$2y$10$8K1p/a0dURXAM1lZ8AxYPuVKp9ZOJKtP9J6MZFZuF4jDT8pO4WKfy', 'super_admin', 1);

CREATE TABLE IF NOT EXISTS pages_contenu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(100) NOT NULL UNIQUE,      -- ex: 'accueil_intro', 'apropos_histoire'
    titre VARCHAR(255),
    contenu TEXT,
    image VARCHAR(255),
    date_maj DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pages_contenu (cle, titre, contenu) VALUES
('accueil_bienvenue', 'Bienvenue à la Mosquée Ridwane', 'Un lieu de culte, de savoir et de fraternité ouvert à toute la communauté.'),
('accueil_presentation', 'Notre mosquée', 'La Mosquée Ridwane accueille chaque jour les fidèles pour les cinq prières, l''enseignement religieux et la vie communautaire.'),
('apropos_histoire', 'Notre histoire', 'À compléter depuis l''espace d''administration.'),
('apropos_mission', 'Notre mission et nos valeurs', 'À compléter depuis l''espace d''administration.'),
('apropos_imam', 'L''imam', 'À compléter depuis l''espace d''administration.'),
('apropos_equipe', 'L''équipe et les responsables', 'À compléter depuis l''espace d''administration.'),
('horaires_jumuah', 'Prière du vendredi — Jumu''ah', 'Khutbah à 13h00, suivie de la prière. Arrivée conseillée 20 minutes à l''avance.'),
('horaires_ramadan', 'Ramadan', 'Les horaires spécifiques du Ramadan (Iftar, Tarawih, Suhoor) seront publiés ici avant le début du mois béni.'),
('dons_intro', 'Soutenir la mosquée', 'Vos dons permettent de faire vivre la mosquée, son fonctionnement, ses œuvres sociales et ses projets.'),
('dons_zakat_fitr', 'Zakat al-Fitr', 'Informations sur le montant et les modalités de la Zakat al-Fitr, mises à jour chaque année avant l''Aïd al-Fitr.'),
('contact_adresse', 'Adresse', 'À compléter depuis l''espace d''administration.');

CREATE TABLE IF NOT EXISTS instances_dirigeantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groupe VARCHAR(30) NOT NULL,
    nom VARCHAR(150) NOT NULL,
    fonction VARCHAR(150) NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    date_maj DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_instances_groupe (groupe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS horaires_prieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_jour DATE NOT NULL UNIQUE,
    fajr TIME,
    lever_soleil TIME,
    dhuhr TIME,
    asr TIME,
    maghrib TIME,
    isha TIME,
    jumuah TIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO horaires_prieres (date_jour, fajr, lever_soleil, dhuhr, asr, maghrib, isha, jumuah) VALUES
(CURDATE(), '05:30:00', '07:00:00', '13:15:00', '16:45:00', '19:20:00', '20:45:00', '13:00:00');

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type ENUM('actualite','evenement') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (nom, type) VALUES
('Annonce', 'actualite'), ('Communiqué', 'actualite'), ('Information', 'actualite'),
('Conférence', 'evenement'), ('Cours religieux', 'evenement'), ('Ramadan', 'evenement'),
('Communautaire', 'evenement'), ('Aïd', 'evenement');

CREATE TABLE IF NOT EXISTS actualites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    resume VARCHAR(500),
    contenu TEXT,
    image VARCHAR(255),
    categorie_id INT,
    statut ENUM('brouillon','publie') DEFAULT 'publie',
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_id INT,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    date_evenement DATE NOT NULL,
    heure_evenement TIME,
    lieu VARCHAR(255),
    categorie_id INT,
    statut ENUM('brouillon','publie') DEFAULT 'publie',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_id INT,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS enseignements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    public_cible VARCHAR(150),   -- ex: 'Enfants', 'Adultes', 'Tous niveaux'
    description TEXT,
    horaires VARCHAR(255),
    ordre INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO enseignements (titre, public_cible, description, horaires, ordre) VALUES
('École coranique (Madrassa)', 'Enfants 6-15 ans', 'Apprentissage du Coran, de l''arabe et des bases religieuses.', 'Mercredi et samedi, 14h-17h', 1),
('Cours de Coran', 'Tous niveaux', 'Lecture, mémorisation et Tajwid, en groupes de niveau.', 'Tous les soirs après Maghrib', 2),
('Cours d''arabe', 'Adultes et enfants', 'Apprentissage de la langue arabe littéraire.', 'Samedi, 10h-12h', 3),
('Cours religieux pour adultes', 'Adultes', 'Fiqh, sira et éducation spirituelle.', 'Dimanche après Asr', 4);

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    icone VARCHAR(50),
    ordre INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO services (titre, description, icone, ordre) VALUES
('Mariage religieux (Nikah)', 'Célébration du Nikah et accompagnement des futurs mariés.', 'rings', 1),
('Funérailles et Janazah', 'Accompagnement des familles, lavage rituel et prière funéraire.', 'janazah', 2),
('Zakat et aumônes', 'Collecte et redistribution de la Zakat et des aumônes.', 'zakat', 3),
('Consultations religieuses', 'Réponses aux questions de fiqh et accompagnement spirituel.', 'consultation', 4);

CREATE TABLE IF NOT EXISTS dons_projets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    objectif DECIMAL(10,2),
    montant_collecte DECIMAL(10,2) DEFAULT 0,
    statut ENUM('actif','termine') DEFAULT 'actif',
    ordre INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mediatheque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255),
    type ENUM('photo','video') NOT NULL,
    fichier VARCHAR(255) NOT NULL,     -- chemin image, ou URL YouTube/Vimeo pour vidéo
    album VARCHAR(150),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages_contact (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(30),
    sujet VARCHAR(255),
    message TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    nom_auteur VARCHAR(80),
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_discussions_date (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS discussions_reponses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussion_id INT NOT NULL,
    nom_auteur VARCHAR(80),
    contenu TEXT NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_discussions_reponses_date (discussion_id, date_creation),
    CONSTRAINT fk_discussions_reponses_discussion
        FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
