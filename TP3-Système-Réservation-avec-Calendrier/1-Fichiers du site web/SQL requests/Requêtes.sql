-- Base de données MySQL --
CREATE DATABASE reservation_system;
USE reservation_system;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    telephone VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email_verifie BOOLEAN DEFAULT FALSE
);


CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_heure DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- Insertion de test --
INSERT INTO users (nom, prenom, date_naissance, adresse, telephone, email, password, email_verifie) VALUES
('Dupont', 'Jean', '1990-05-14', '10 rue de Paris, 75000 Paris', '0601020304', 'jean.dupont@example.com', 'hashedpassword123', TRUE),
('Martin', 'Sophie', '1985-07-21', '5 avenue des Champs, 75008 Paris', '0612345678', 'sophie.martin@example.com', 'hashedpassword456', TRUE);


INSERT INTO reservations (user_id, date_heure) VALUES
(1, '2025-02-15 10:00:00'),
(2, '2025-02-16 14:30:00'),
(3, '2025-02-17 09:00:00'),
(1, '2025-02-18 11:15:00'),
(4, '2025-02-19 16:00:00'),
(2, '2025-02-20 13:45:00'),
(5, '2025-02-21 08:30:00'),
(3, '2025-02-22 15:00:00'),
(1, '2025-02-23 12:00:00'),
(4, '2025-02-24 10:45:00');



-- Ajout de la vérification par email --
ALTER TABLE users ADD COLUMN email_token VARCHAR(255) DEFAULT NULL;


-- Suppression d'une ligne du tableau --
#DELETE FROM users WHERE id>3

-- Changement d'une ligne du tableau --
#UPDATE users SET PASSWORD='$2y$12$Up09MSmnknQsUfyFl8oHtuG4gwHe3Kc6DFCw5MraMJYK2Zrp/cvTu' WHERE id=1



-- Sélection des utilisateurs et réservations --
USE reservation_system;
SELECT * FROM users;
SELECT * FROM reservations;






