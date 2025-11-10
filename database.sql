CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(50) PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(10) NOT NULL
);

-- Category Table
CREATE TABLE IF NOT EXISTS category (
    categoryID INT PRIMARY KEY AUTO_INCREMENT,
    categoryDescription VARCHAR(100) NOT NULL
);

-- Books Table
CREATE TABLE IF NOT EXISTS books (
    ISBN VARCHAR(20) PRIMARY KEY,
    booktitle VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    edition INT,
    year INT,
    categoryID INT,
    FOREIGN KEY (categoryID) REFERENCES category(categoryID)
);

-- Reserved Books Table
CREATE TABLE IF NOT EXISTS reservedbooks (
    ISBN VARCHAR(20),
    username VARCHAR(50),
    reservedDate DATE NOT NULL,
    PRIMARY KEY (ISBN, username),
    FOREIGN KEY (ISBN) REFERENCES books(ISBN) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

-- Insert Sample Categories
INSERT INTO category (categoryDescription) VALUES
('Fiction'),
('Business'),
('Science'),
('Technology'),
('History'),
('Biography'),
('Self-Help'),
('Mystery');

-- Insert Sample Books
INSERT INTO books (ISBN, booktitle, author, edition, year, categoryID) VALUES
('978-0-13-468599-1', 'Clean Code', 'Robert C. Martin', 1, 2008, 4),
('978-0-596-52068-7', 'JavaScript: The Good Parts', 'Douglas Crockford', 1, 2008, 4),
('978-0-201-63361-0', 'Design Patterns', 'Gang of Four', 1, 1994, 4),
('978-1-59327-928-8', 'Python Crash Course', 'Eric Matthes', 2, 2019, 4),
('978-0-7432-7356-5', 'The Da Vinci Code', 'Dan Brown', 1, 2003, 1),
('978-0-06-112008-4', 'To Kill a Mockingbird', 'Harper Lee', 1, 1960, 1),
('978-0-545-01022-1', 'Harry Potter and the Deathly Hallows', 'J.K. Rowling', 1, 2007, 1),
('978-0-06-231500-7', '1984', 'George Orwell', 1, 1949, 1),
('978-1-4516-7331-9', 'Steve Jobs', 'Walter Isaacson', 1, 2011, 6),
('978-0-7432-7357-2', 'The Tipping Point', 'Malcolm Gladwell', 1, 2000, 2),
('978-1-59184-763-8', 'Rich Dad Poor Dad', 'Robert Kiyosaki', 1, 1997, 2),
('978-0-385-50420-3', 'Thinking, Fast and Slow', 'Daniel Kahneman', 1, 2011, 3),
('978-0-307-88789-6', 'Sapiens', 'Yuval Noah Harari', 1, 2011, 5),
('978-0-7432-7357-3', 'The Lean Startup', 'Eric Ries', 1, 2011, 2),
('978-0-06-440055-8', 'Where the Crawdads Sing', 'Delia Owens', 1, 2018, 1);

-- Insert Sample User (password: test123)
INSERT INTO users (username, firstname, surname, password, mobile) VALUES
('testuser', 'Test', 'User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890');