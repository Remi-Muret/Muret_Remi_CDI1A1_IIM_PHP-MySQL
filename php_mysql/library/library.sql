-- Création de la base de données "library"
CREATE DATABASE IF NOT EXISTS library CHARACTER SET utf8mb4;
USE library;

-- Création de la table "book"
CREATE TABLE IF NOT EXISTS book (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(100) NOT NULL,
  author VARCHAR(100) NOT NULL,
  year_publication INT,
  available BOOLEAN
);

-- Insertion de données
INSERT INTO book (title, author, year_publication, available) VALUES
('Le Nom du Vent', 'Patrick Rothfuss', 2007, 1),
('1984', 'George Orwell', 1949, 1),
('La Route', 'Cormac McCarthy', 2006, 0),
('L\'Étranger', 'Albert Camus', 1942, 1),
('Harry Potter à l\'école des sorciers', 'J.K. Rowling', 1997, 1),
('Le Seigneur des Anneaux', 'J.R.R. Tolkien', 1954, 0),
('Les Misérables', 'Victor Hugo', 1862, 0),
('La Vie est une fête', 'David Lodge', 2000, 1),
('La Horde du Contrevent', 'Alain Damasio', 2004, 1),
('Petit Pays', 'Gaël Faye', 2016, 0);

-- Lecture simple
SELECT * FROM book;

-- Lecture filtrée : livres publiés après 2000, triés par titre
SELECT * FROM book
WHERE year_publication > 2000
ORDER BY title ASC;
