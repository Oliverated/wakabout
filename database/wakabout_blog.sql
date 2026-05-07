-- Wakabout Blog SQLite Schema
-- This is for reference only. The table is auto-created by db.php.

CREATE TABLE posts (
    id INT PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    --slug TEXT NOT NULL UNIQUE,
    category TEXT DEFAULT 'General',
    author TEXT DEFAULT 'Wakabout Team',
    excerpt TEXT,
    body TEXT NOT NULL,
    cover_image TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT (datetime('now','localtime'))
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  author VARCHAR(150) DEFAULT 'Wakabout Team',
  category VARCHAR(100) DEFAULT 'General',
  description TEXT,
  cover_image VARCHAR(250),
  price VARCHAR(80),
  buy_link VARCHAR(300),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(250) NOT NULL,
  event_date VARCHAR(100),
  location VARCHAR(200),
  description TEXT,
  cover_image VARCHAR(250),
  cta_label VARCHAR(100) DEFAULT 'Learn More',
  cta_link VARCHAR(300),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

  "CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(100) NOT NULL UNIQUE,
      email VARCHAR(200) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      reset_token VARCHAR(255) DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )",
  
  "CREATE TABLE post_comments (
      id INT AUTO_INCREMENT PRIMARY KEY,
      post_id INT NOT NULL,
      user_id INT NOT NULL,
      comment TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  )",
  "CREATE TABLE post_likes (
      id INT AUTO_INCREMENT PRIMARY KEY,
      post_id INT NOT NULL,
      user_id INT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY unique_like (post_id, user_id),
      FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  )"





-- update sql
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(250) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category VARCHAR(200) DEFAULT 'General',
    author VARCHAR(150) DEFAULT 'Wakabout Team',
    excerpt VARCHAR(250),
    body TEXT NOT NULL,
    cover_image VARCHAR(250) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);














-- Sample post
INSERT INTO posts (title, slug, category, author, excerpt, body, cover_image)
VALUES (
  'Exploring Lagos: Hidden Gems You Must Visit This Weekend',
  'exploring-lagos-hidden-gems',
  'Travel',
  'Wakabout Team',
  'Discover the best hidden gems in Lagos that are perfect for a weekend getaway.',
  '<p>Nigeria''s energy sector has once again been shaken as the Dangote Refinery announced a new increase in fuel prices, pushing the cost per litre to ₦1,275.</p><p>This latest adjustment has sparked widespread reactions among Nigerians, with many expressing concern over the continuous rise in the cost of living. The refinery, which has been positioned as a key solution to Nigeria''s long-standing fuel import challenges, cited market realities and operational costs as reasons for the increment.</p>',
  'assets/post-img/_uhdtexture596.jpg'
);