-- À exécuter une seule fois sur une installation déjà existante.
-- Les anciens comptes gardent l'accès complet qu'ils avaient déjà.
USE mosquee_ridwane;

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

ALTER TABLE admins
    ADD COLUMN role_id INT NOT NULL DEFAULT 1,
    ADD INDEX idx_admins_role_id (role_id),
    ADD CONSTRAINT fk_admins_role FOREIGN KEY (role_id) REFERENCES roles(id);
