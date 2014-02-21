DROP DATABASE lukebarn_unify;
CREATE DATABASE lukebarn_unify;

USE lukebarn_unify;

CREATE TABLE university
(
university_id int NOT NULL AUTO_INCREMENT,
university_name varchar(256),

PRIMARY KEY (university_id)
)ENGINE=InnoDB;

CREATE TABLE course
(
course_id int NOT NULL AUTO_INCREMENT,
university_id int NOT NULL,
course_name varchar(256),

PRIMARY KEY (course_id),
FOREIGN KEY (university_id) REFERENCES university(university_id)
)ENGINE=InnoDB;

CREATE TABLE user_group
(
group_id int NOT NULL AUTO_INCREMENT,
group_name varchar(256),

PRIMARY KEY (group_id)
)ENGINE=InnoDB;

CREATE TABLE cohort
(
cohort_id int NOT NULL AUTO_INCREMENT,
course_id int NOT NULL,
group_id int NOT NULL,
cohort_start date NOT NULL,

PRIMARY KEY (cohort_id),
FOREIGN KEY (course_id) REFERENCES course(course_id),
FOREIGN KEY (group_id) REFERENCES user_group(group_id)
)ENGINE=InnoDB;

CREATE TABLE user
(
user_id int NOT NULL AUTO_INCREMENT,
cohort_id int NOT NULL,
user_name varchar(256) NOT NULL,
user_email varchar(254),
user_password varchar(32) NOT NULL,
user_picture varchar(32) DEFAULT 'default',

PRIMARY KEY (user_id),
FOREIGN KEY (cohort_id) REFERENCES cohort(cohort_id)
)ENGINE=InnoDB;

CREATE TABLE grouping
(
grouping_id int NOT NULL AUTO_INCREMENT,
group_id int NOT NULL,
user_id int NOT NULL,

PRIMARY KEY (grouping_id),
FOREIGN KEY (group_id) REFERENCES user_group(group_id) ON DELETE CASCADE,
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE post
(
post_id int NOT NULL AUTO_INCREMENT,
user_id int NOT NULL,
group_id int NOT NULL,
post_content TEXT NOT NULL,
post_rating_up int DEFAULT 0,
post_rating_dn int DEFAULT 0,
post_time datetime NOT NULL,

PRIMARY KEY (post_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (group_id) REFERENCES user_group(group_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE hidden_post
(
hide_id int NOT NULL AUTO_INCREMENT,
user_id int NOT NULL,
post_id int NOT NULL,

PRIMARY KEY (hide_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (post_id) REFERENCES post(post_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE connection
(
connection_id int NOT NULL AUTO_INCREMENT,
user_id1 int NOT NULL,
user_id2 int NOT NULL,

PRIMARY KEY (connection_id),
FOREIGN KEY (user_id1) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (user_id2) REFERENCES user(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE comment
(
comment_id int NOT NULL AUTO_INCREMENT,
user_id int NOT NULL,
post_id int NOT NULL,
comment_content TINYTEXT NOT NULL,

PRIMARY KEY (comment_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (post_id) REFERENCES post(post_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE confirmation
(
conf_id int NOT NULL AUTO_INCREMENT,
conf_rnd varchar(32) NOT NULL,
user_id int NOT NULL,
user_email varchar(254) NOT NULL,

PRIMARY KEY (conf_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE friend_request
(
req_id int NOT NULL AUTO_INCREMENT,
user_id1 int NOT NULL,
user_id2 int NOT NULL,
lat double NOT NULL,
lng double NOT NULL,

PRIMARY KEY (req_id),
FOREIGN KEY (user_id1) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (user_id2) REFERENCES user(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE reset_request
(
req_id int NOT NULL AUTO_INCREMENT,
user_id int NOT NULL, 
conf_rnd varchar(32) NOT NULL,

PRIMARY KEY (req_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
UNIQUE KEY (user_id)
)ENGINE=InnoDB;

CREATE TABLE message
(
message_id int NOT NULL AUTO_INCREMENT,
message_title varchar(32) NOT NULL,
message_description varchar(512) NOT NULL,

PRIMARY KEY (message_id)
)ENGINE=InnoDB;

CREATE TABLE chat_msg
(
msg_id int NOT NULL AUTO_INCREMENT,
user_id1 int NOT NULL, 
user_id2 int NOT NULL, 
msg_content varchar(512) NOT NULL,
msg_seen boolean NOT NULL DEFAULT 0,

PRIMARY KEY (msg_id),
FOREIGN KEY (user_id1) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (user_id2) REFERENCES user(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE post_vote
(
vote_id int NOT NULL AUTO_INCREMENT,
user_id int NOT NULL,
post_id int NOT NULL,

PRIMARY KEY (vote_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
FOREIGN KEY (post_id) REFERENCES post(post_id) ON DELETE CASCADE
)ENGINE=InnoDB;

CREATE TABLE notification
(
notif_id int NOT NULL AUTO_INCREMENT,
user_id int NOT NULL,
notif_title varchar(32) NOT NULL,
notif_message varchar(512) NOT NULL,
notif_link varchar(128) NOT NULL,
notif_seen boolean NOT NULL DEFAULT 0,
notif_emailed boolean NOT NULL DEFAULT 0,
notif_departure datetime NOT NULL,

PRIMARY KEY (notif_id),
FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
)ENGINE=InnoDB;

INSERT INTO university VALUES (NULL,"University of Bath");
INSERT INTO course VALUES (NULL,1,"Computer Science");
INSERT INTO cohort VALUES (-1,1,"0000-01-01");
INSERT INTO cohort VALUES (1,1,"2012-09-01");
INSERT INTO cohort VALUES (2,1,"2011-09-01");
INSERT INTO user VALUES (NULL,"1","Luke Barnard1","luke.barnard99@gmail.com","19cd3e66a6952b4a2fba0d11890ef873");
INSERT INTO user VALUES (NULL,"1","Luke Barnard2","ldb26@bath.ac.uk","19cd3e66a6952b4a2fba0d11890ef873");
INSERT INTO user VALUES (NULL,"2","Luke Barnard3","mooface@lukebarnard.co.uk","19cd3e66a6952b4a2fba0d11890ef873");
INSERT INTO connection VALUES (NULL,1,2);