CREATE TABLE cms_post (
  id INT(11) NOT NULL AUTO_INCREMENT,
  title VARCHAR(256) NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
  KEY created_at(created_at)
)

INSERT INTO post (title, content) VALUES
('First post', 'This is a really interesting post'),
('Second post', 'This is a really fascinating post'),
('Third post', 'This is a really funny post'

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_active` smallint(6) DEFAULT '1',
  PRIMARY KEY (`id`)
)