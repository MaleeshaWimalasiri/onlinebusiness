-- =====================================================================
--  Maheesha Jewels - Online Jewelry Store
--  Database schema and sample data
--
--  How to import (XAMPP / phpMyAdmin):
--    1. Open phpMyAdmin (http://localhost/phpmyadmin)
--    2. Click "Import" and choose this file, OR
--    3. Run from terminal:  mysql -u root < database/jewelry_store.sql
-- =====================================================================

DROP DATABASE IF EXISTS jewelry_store;
CREATE DATABASE jewelry_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jewelry_store;

-- ---------------------------------------------------------------------
-- Table: categories
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80)  NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    image       VARCHAR(255) DEFAULT 'placeholder.svg'
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: users  (role = 'customer' or 'admin')
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    phone      VARCHAR(30)  DEFAULT NULL,
    address    VARCHAR(255) DEFAULT NULL,
    role       ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: products
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)   NOT NULL,
    category_id INT            DEFAULT NULL,
    description TEXT,
    price       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    stock       INT            NOT NULL DEFAULT 0,
    image       VARCHAR(255)   DEFAULT 'placeholder.svg',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: orders
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT DEFAULT NULL,
    customer_name VARCHAR(120)  NOT NULL,
    email         VARCHAR(150)  NOT NULL,
    phone         VARCHAR(30)   NOT NULL,
    address       VARCHAR(255)  NOT NULL,
    total         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status        ENUM('Pending','Processing','Shipped','Delivered','Cancelled')
                  NOT NULL DEFAULT 'Pending',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: order_items
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT DEFAULT NULL,
    name       VARCHAR(150)  NOT NULL,
    quantity   INT           NOT NULL DEFAULT 1,
    price      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: messages  (contact / inquiry form)
-- ---------------------------------------------------------------------
CREATE TABLE messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    subject    VARCHAR(200) NOT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Table: reviews  (product ratings & reviews)
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id    INT DEFAULT NULL,
    name       VARCHAR(120) NOT NULL,
    rating     TINYINT      NOT NULL DEFAULT 5,
    comment    TEXT         NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
--  SAMPLE DATA
-- =====================================================================

-- Categories ----------------------------------------------------------
INSERT INTO categories (name, description, image) VALUES
('Rings',     'Engagement, wedding and fashion rings',      'rings.svg'),
('Necklaces', 'Pendants, chains and statement necklaces',   'necklaces.svg'),
('Earrings',  'Studs, hoops and drop earrings',             'earrings.svg'),
('Bracelets', 'Bangles, cuffs and charm bracelets',         'bracelets.svg');

-- Users ---------------------------------------------------------------
-- Admin login    : admin@maheeshajewels.com / admin123
-- Customer login : sara@example.com       / password123
INSERT INTO users (name, email, password, phone, address, role) VALUES
('Store Admin', 'admin@maheeshajewels.com', '$2y$12$Cuj56BPKS99YqsRcp/V.EurqvJ.csmv76wCxpG0iTfMMEIWkEhAIa', '0112345678', 'Colombo, Sri Lanka', 'admin'),
('Sara Perera', 'sara@example.com',       '$2y$12$goaeydTx/2nYIYo44in02epWn6GdPg6GYalZtG5DZIDI5s8xGsBzK', '0771234567', 'Kandy, Sri Lanka',   'customer');

-- Products ------------------------------------------------------------
INSERT INTO products (name, category_id, description, price, stock, image) VALUES
('Solitaire Diamond Ring',   1, 'A timeless 18k white gold ring featuring a brilliant-cut solitaire diamond. Perfect for engagements.', 185000.00, 8,  'rings.svg'),
('Rose Gold Eternity Band',  1, 'Delicate rose gold band lined with micro-pave stones all the way around.',                          92500.00,  15, 'rings.svg'),
('Sapphire Halo Ring',       1, 'Deep blue sapphire centre stone surrounded by a sparkling halo of white stones.',                    142000.00, 6,  'rings.svg'),
('Pearl Pendant Necklace',   2, 'Single freshwater pearl suspended on a fine 18k gold chain.',                                        58000.00,  20, 'necklaces.svg'),
('Diamond Tennis Necklace',  2, 'Classic tennis necklace with a continuous line of round diamonds.',                                  320000.00, 4,  'necklaces.svg'),
('Gold Layered Chain',       2, 'Three-strand layered gold chain that adds elegance to any outfit.',                                  76000.00,  12, 'necklaces.svg'),
('Diamond Stud Earrings',    3, 'Pair of brilliant-cut diamond studs set in 18k white gold.',                                         110000.00, 10, 'earrings.svg'),
('Gold Hoop Earrings',       3, 'Lightweight polished gold hoops suitable for everyday wear.',                                        48000.00,  25, 'earrings.svg'),
('Emerald Drop Earrings',    3, 'Elegant emerald drops framed with small white stones.',                                              135000.00, 7,  'earrings.svg'),
('Tennis Diamond Bracelet',  4, 'A flexible bracelet featuring a row of matched round diamonds.',                                     240000.00, 5,  'bracelets.svg'),
('Gold Bangle Set',          4, 'Set of three textured gold bangles, sold together.',                                                 88000.00,  18, 'bracelets.svg'),
('Charm Bracelet',           4, 'Sterling silver charm bracelet with five hand-finished charms.',                                     39500.00,  30, 'bracelets.svg');

-- Reviews -------------------------------------------------------------
INSERT INTO reviews (product_id, user_id, name, rating, comment) VALUES
(1, 2, 'Sara Perera', 5, 'Absolutely stunning ring, the diamond sparkles beautifully!'),
(1, NULL, 'Nimal F.',  4, 'Great quality but delivery took a little longer than expected.'),
(4, 2, 'Sara Perera', 5, 'The pearl is gorgeous and the chain feels very sturdy.'),
(8, NULL, 'Ayesha M.', 5, 'Perfect everyday hoops, light and comfortable.');

-- Orders --------------------------------------------------------------
INSERT INTO orders (user_id, customer_name, email, phone, address, total, status) VALUES
(2, 'Sara Perera', 'sara@example.com', '0771234567', 'No. 12, Lake Road, Kandy', 243000.00, 'Delivered'),
(2, 'Sara Perera', 'sara@example.com', '0771234567', 'No. 12, Lake Road, Kandy', 48000.00,  'Pending');

INSERT INTO order_items (order_id, product_id, name, quantity, price) VALUES
(1, 1, 'Solitaire Diamond Ring', 1, 185000.00),
(1, 4, 'Pearl Pendant Necklace', 1, 58000.00),
(2, 8, 'Gold Hoop Earrings',     1, 48000.00);

-- Messages ------------------------------------------------------------
INSERT INTO messages (name, email, subject, message) VALUES
('Kavindu Silva', 'kavindu@example.com', 'Custom ring inquiry', 'Hi, do you offer custom-made engagement rings? I would like to discuss a design.'),
('Dilani R.',     'dilani@example.com',  'Store opening hours', 'What are your showroom opening hours on weekends?');
