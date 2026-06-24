-- ============================================================
--  Wakabout Blog — MySQL Schema  (reference / setup script)
--  Engine : InnoDB | Charset : utf8mb4
--  Run once on a fresh database:
--      mysql -u root -p wakabout < wakabout_blog.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────
--  1. USERS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id                  INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name          VARCHAR(100) DEFAULT NULL,
    last_name           VARCHAR(100) DEFAULT NULL,
    username            VARCHAR(100) NOT NULL UNIQUE,
    email               VARCHAR(200) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    reset_token         VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME     DEFAULT NULL,
    created_at          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────
--  2. CATEGORIES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id         INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(120) NOT NULL UNIQUE,
    group_name VARCHAR(120) NOT NULL DEFAULT 'General',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────
--  3. POSTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS posts (
    id           INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(250) NOT NULL,
    slug         VARCHAR(220) NOT NULL UNIQUE,
    category     VARCHAR(220) NOT NULL DEFAULT 'General',
    author       VARCHAR(150) NOT NULL DEFAULT 'Wakabout Team',
    excerpt      VARCHAR(320) DEFAULT NULL,
    body         LONGTEXT     NOT NULL,
    cover_image  VARCHAR(300) DEFAULT NULL,
    views        INT UNSIGNED NOT NULL DEFAULT 0,
    published_at TIMESTAMP    DEFAULT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_slug         (slug),
    INDEX idx_published_at (published_at),
    INDEX idx_category     (category(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────
--  4. BOOKS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS books (
    id          INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(250) NOT NULL,
    author      VARCHAR(150) NOT NULL DEFAULT 'Wakabout Team',
    category    VARCHAR(100) NOT NULL DEFAULT 'General',
    description TEXT         DEFAULT NULL,
    cover_image VARCHAR(300) DEFAULT NULL,
    price       VARCHAR(80)  DEFAULT NULL,
    buy_link    VARCHAR(300) DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────
--  5. EVENTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id          INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(250) NOT NULL,
    event_date  VARCHAR(100) DEFAULT NULL,
    location    VARCHAR(200) DEFAULT NULL,
    description TEXT         DEFAULT NULL,
    cover_image VARCHAR(300) DEFAULT NULL,
    cta_label   VARCHAR(100) NOT NULL DEFAULT 'Learn More',
    cta_link    VARCHAR(300) DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────
--  6. POST COMMENTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS post_comments (
    id         INT       UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id    INT       UNSIGNED NOT NULL,
    user_id    INT       UNSIGNED NOT NULL,
    comment    TEXT      NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_post_id (post_id),
    CONSTRAINT fk_comment_post
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────
--  7. POST LIKES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS post_likes (
    id         INT       UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id    INT       UNSIGNED NOT NULL,
    user_id    INT       UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_post_user_like (post_id, user_id),
    CONSTRAINT fk_like_post
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_like_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;


-- ─────────────────────────────────────────
--  MIGRATION HELPERS
--  Run these ALTER statements if the database
--  already exists and you need to add new columns.
-- ─────────────────────────────────────────

-- Add views column (idempotent via stored procedure trick — just run once):
-- ALTER TABLE posts ADD COLUMN views INT UNSIGNED NOT NULL DEFAULT 0;

-- Add published_at if missing:
-- ALTER TABLE posts ADD COLUMN published_at TIMESTAMP DEFAULT NULL AFTER cover_image;

-- Add updated_at auto-update behaviour:
-- ALTER TABLE posts MODIFY updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;


-- ─────────────────────────────────────────
--  SAMPLE DATA
-- ─────────────────────────────────────────
INSERT INTO posts (title, slug, category, author, excerpt, body, cover_image, published_at)
VALUES (
  'Exploring Lagos: Hidden Gems You Must Visit This Weekend',
  'exploring-lagos-hidden-gems',
  'Travel',
  'Wakabout Team',
  'Discover the best hidden gems in Lagos that are perfect for a weekend getaway.',
  '<p>Nigeria''s energy sector has once again been shaken as the Dangote Refinery announced a new increase in fuel prices, pushing the cost per litre to ₦1,275.</p><p>This latest adjustment has sparked widespread reactions among Nigerians, with many expressing concern over the continuous rise in the cost of living. The refinery, which has been positioned as a key solution to Nigeria''s long-standing fuel import challenges, cited market realities and operational costs as reasons for the increment.</p>',
  'assets/post-img/_uhdtexture596.jpg',
  NOW()
);