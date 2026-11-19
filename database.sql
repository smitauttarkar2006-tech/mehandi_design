CREATE DATABASE IF NOT EXISTS mehndi_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE mehndi_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS designs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    design_name VARCHAR(150) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    image VARCHAR(255) NOT NULL,
    style VARCHAR(50) NOT NULL,
    occasion VARCHAR(50) NOT NULL,
    complexity VARCHAR(30) NOT NULL,
    application_area VARCHAR(50) NOT NULL,
    design_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(7,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_designs_category FOREIGN KEY (category_id)
        REFERENCES categories (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_designs_price CHECK (price >= 500 AND price <= 10000),
    INDEX idx_designs_category (category_id),
    INDEX idx_designs_preferences (style, occasion, complexity, application_area, design_type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorites (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    design_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_favorite_user_design UNIQUE (user_id, design_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_favorites_design FOREIGN KEY (design_id)
        REFERENCES designs (id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_favorites_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS design_feedback (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    design_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment VARCHAR(1000) NOT NULL DEFAULT '',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_design_feedback_user_design UNIQUE (user_id, design_id),
    INDEX idx_design_feedback_design (design_id, created_at)
) ENGINE=InnoDB;

-- Admins can only maintain design and category content. Create the first
-- password-hashed admin through admin/create_admin.php after importing this file.
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (id, category_name)
SELECT sample.id, sample.category_name
FROM (
    SELECT 1 AS id, 'Bridal Mehndi' AS category_name
    UNION ALL SELECT 2, 'Arabic Mehndi'
    UNION ALL SELECT 3, 'Simple Mehndi'
    UNION ALL SELECT 4, 'Traditional Mehndi'
    UNION ALL SELECT 5, 'Festival Mehndi'
    UNION ALL SELECT 6, 'Front Hand'
    UNION ALL SELECT 7, 'Back Hand'
    UNION ALL SELECT 8, 'Full Hand'
    UNION ALL SELECT 9, 'Feet Mehndi'
) AS sample
WHERE NOT EXISTS (
    SELECT 1
    FROM categories
    WHERE categories.id = sample.id
       OR categories.category_name = sample.category_name
);

INSERT INTO designs
(design_name, category_id, image, style, occasion, complexity, application_area, design_type, description, price)
SELECT sample.design_name, sample.category_id, sample.image, sample.style, sample.occasion, sample.complexity, sample.application_area, sample.design_type, sample.description, sample.price
FROM (
    SELECT 'Garden of Vows' AS design_name, 1 AS category_id, 'motif-floral.svg' AS image, 'Bridal' AS style, 'Wedding' AS occasion, 'Heavy' AS complexity, 'Full Hand' AS application_area, 'Floral' AS design_type, 'A richly detailed floral composition for a joyful wedding celebration.' AS description, 5000.00 AS price
    UNION ALL SELECT 'Lotus Promise', 1, 'motif-mandala.svg', 'Bridal', 'Engagement', 'Heavy', 'Full Hand', 'Mandala', 'A symmetrical lotus-inspired centrepiece surrounded by delicate bridal details.', 7500.00
    UNION ALL SELECT 'Saffron Trail', 2, 'motif-vine.svg', 'Arabic', 'Wedding', 'Medium', 'Back Hand', 'Paisley', 'A flowing paisley trail with graceful open spaces and balanced detail.', 1000.00
    UNION ALL SELECT 'Evening Vine', 2, 'motif-vine.svg', 'Arabic', 'Party', 'Medium', 'Front Hand', 'Floral', 'An elegant vine-inspired pattern for festive evenings and celebrations.', 1000.00
    UNION ALL SELECT 'Little Bloom', 3, 'motif-floral.svg', 'Minimal', 'Casual', 'Simple', 'Front Hand', 'Floral', 'A light floral motif for a relaxed, everyday touch of mehndi.', 500.00
    UNION ALL SELECT 'Open Lotus', 3, 'motif-mandala.svg', 'Traditional', 'Festival', 'Simple', 'Back Hand', 'Mandala', 'A simple central lotus motif framed with clean, easy-to-wear detail.', 500.00
    UNION ALL SELECT 'Temple Paisley', 4, 'motif-mandala.svg', 'Traditional', 'Wedding', 'Heavy', 'Full Hand', 'Paisley', 'A classic, richly filled pattern inspired by traditional ornamental forms.', 5000.00
    UNION ALL SELECT 'Festival Petals', 5, 'motif-floral.svg', 'Traditional', 'Festival', 'Medium', 'Front Hand', 'Traditional', 'A cheerful floral arrangement made for festival gatherings and family celebrations.', 2000.00
    UNION ALL SELECT 'Wrist Garden', 6, 'motif-vine.svg', 'Floral', 'Casual', 'Simple', 'Front Hand', 'Floral', 'A fresh, minimal vine that sits beautifully across the front of the hand.', 500.00
    UNION ALL SELECT 'Peacock Breeze', 7, 'motif-vine.svg', 'Arabic', 'Party', 'Medium', 'Back Hand', 'Paisley', 'A sweeping back-hand trail with airy, paisley-like curves.', 1500.00
    UNION ALL SELECT 'Bridal Jaal', 8, 'motif-floral.svg', 'Bridal', 'Wedding', 'Heavy', 'Full Hand', 'Mixed', 'A full-hand bridal composition combining floral, paisley and fine connecting detail.', 10000.00
    UNION ALL SELECT 'Anklet Blossoms', 9, 'motif-floral.svg', 'Traditional', 'Wedding', 'Medium', 'Feet', 'Floral', 'A graceful floral arrangement designed to frame the feet for a special occasion.', 2500.00
    UNION ALL SELECT 'Golden Geometry', 4, 'motif-geometric.svg', 'Geometric', 'Party', 'Medium', 'Back Hand', 'Geometric', 'A balanced geometric pattern with repeated diamond shapes and a clear centre.', 2000.00
    UNION ALL SELECT 'Moonlit Mandala', 5, 'motif-mandala.svg', 'Minimal', 'Festival', 'Simple', 'Back Hand', 'Mandala', 'A neat mandala for festivals when you want a lovely motif without heavy coverage.', 1000.00
    UNION ALL SELECT 'Paisley Promise', 2, 'motif-vine.svg', 'Arabic', 'Engagement', 'Heavy', 'Full Hand', 'Paisley', 'A rich paisley-led pattern with sweeping forms for an engagement celebration.', 5000.00
    UNION ALL SELECT 'Petal Party', 3, 'motif-floral.svg', 'Floral', 'Party', 'Medium', 'Front Hand', 'Floral', 'A lively floral front-hand motif with comfortable medium coverage.', 1500.00
) AS sample
WHERE NOT EXISTS (SELECT 1 FROM designs LIMIT 1);
