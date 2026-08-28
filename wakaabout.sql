CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(250) NOT NULL,
  `author` varchar(150) DEFAULT 'Wakabout Team',
  `category` varchar(100) DEFAULT 'General',
  `description` text DEFAULT NULL,
  `cover_image` varchar(250) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `buy_link` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL UNIQUE,
  `group_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`, `group_name`, `created_at`) VALUES
(1, 'Nigeria', 'Destinations', '2026-04-29 23:25:38'),
(2, 'Africa', 'Destinations', '2026-04-29 23:25:40'),
(3, 'Europe', 'Destinations', '2026-04-29 23:25:40'),
(4, 'Road Trips', 'Destinations', '2026-04-29 23:25:41'),
(5, 'Festivals', 'Culture & Heritage', '2026-04-29 23:25:41'),
(6, 'Museums', 'Culture & Heritage', '2026-04-29 23:25:42'),
(7, 'Exhibitions', 'Culture & Heritage', '2026-04-29 23:25:42'),
(8, 'Music & Concerts', 'Culture & Heritage', '2026-04-29 23:25:43'),
(9, 'Hotels', 'Experiences & Lifestyle', '2026-04-29 23:25:43'),
(10, 'Recreation', 'Experiences & Lifestyle', '2026-04-29 23:25:45'),
(11, 'Fashion & Lifestyle', 'Experiences & Lifestyle', '2026-04-29 23:25:45'),
(12, 'Luxury Travel', 'Experiences & Lifestyle', '2026-04-29 23:25:45'),
(13, 'Books', 'Reviews', '2026-04-29 23:25:45'),
(14, 'Reviews', 'Reviews', '2026-04-29 23:25:45'),
(15, 'Film', 'Reviews', '2026-04-29 23:25:46'),
(16, 'Stage', 'Reviews', '2026-04-29 23:25:46'),
(17, 'Wayside', 'Diaries & Columns', '2026-04-29 23:25:46'),
(18, 'Editors Notes', 'Diaries & Columns', '2026-04-29 23:25:46');

-- Table structure for table `events`
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(250) NOT NULL,
  `category` varchar(150) DEFAULT 'General',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `event_time` varchar(100) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(250) DEFAULT NULL,
  `cta_label` varchar(100) DEFAULT 'Learn More',
  `cta_link` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `posts`
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(250) NOT NULL,
  `slug` varchar(200) NOT NULL UNIQUE,
  `category` varchar(200) DEFAULT 'General',
  `author` varchar(150) DEFAULT 'Wakabout Team',
  `excerpt` varchar(250) DEFAULT NULL,
  `body` text NOT NULL,
  `cover_image` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL UNIQUE,
  `email` varchar(200) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `post_comments`
CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_post_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `post_likes`
CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_post_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `subscribers`
CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` varchar(255) NOT NULL UNIQUE,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

