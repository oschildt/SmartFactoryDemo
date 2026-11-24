-- Recreates framework_demo. All existing demo data will be removed.

drop database if exists framework_demo;

create database framework_demo character set utf8mb4 collate utf8mb4_unicode_ci;

set names utf8mb4;

use framework_demo;

create table `dbworker_locks` (
    `resource_key` char(64) character set ascii collate ascii_bin not null,
    `resource_name` text null,
    primary key (`resource_key`)
);

create table `department` (
  `id` integer not null auto_increment,
  `name` varchar(250) not null,
  primary key (`id`),
  unique key `name` (`name`)
);

create table `large_data` (
  `id` integer not null,
  `blob_data` longblob,
  `text_data` longtext
);

create table `page_content` (
  `language_key` varchar(3) not null,
  `page_id` integer not null,
  `title` varchar(250) default null,
  `content` varchar(250) default null,
  unique key `page_content_unq` (`language_key`, `page_id`)
);

create table `pages` (
  `id` integer not null auto_increment,
  `page_name` varchar(250) not null,
  `page_type` varchar(20) not null,
  `page_order` integer default null,
  `page_date` datetime default null,
  primary key (`id`),
  unique key `page_name_unq` (`page_name`)
);

create table `room_prices` (
  `room` varchar(250) not null,
  `dt` date not null,
  `price` DECIMAL(12,2) default null
);

create table `settings` (
  `id` integer not null default 1,
  `data` text,
  primary key (`id`)
);

create table `users` (
  `id` integer not null auto_increment,
  `email` varchar(255) default null,
  `last_name` varchar(500) not null,
  `first_name` varchar(500) default null,
  `birth_date` date default null,
  `salary` DECIMAL(12,2) default null,
  `department_id` integer not null,
  `language` varchar(20) default null,
  `time_zone` varchar(20) default null,
  `last_login` datetime default null,
  primary key (`id`)
);

create table `user_forum_settings` (
  `user_id` integer not null,
  `signature` varchar(250) default null,
  `status` varchar(250) default null,
  `hide_pictures` integer not null default 0,
  `hide_signatures` integer not null default 0,
  primary key (`user_id`)
);

create table `user_colors` (
  `user_id` integer not null,
  `color` varchar(255) default null,
  unique (`user_id`, `color`)
);

insert into `department` (`id`, `name`) values
  (1,'management'),
  (2,'marketing'),
  (3,'development'),
  (4,'pr');

insert into `large_data` (`id`, `blob_data`, `text_data`) values
  (1,null,null);

insert into `page_content` (`language_key`, `page_id`, `title`, `content`) values
  ('de',1,'page 1 title de22','page 1 content de22'),
  ('de',2,'page 2 title de','page 2 content de'),
  ('de',3,'at de11','ac de11'),
  ('de',4,'333','444'),
  ('de',5,'33','44'),
  ('de',6,null,null),
  ('en',1,'page 1 title en2211','page 1 content en2211'),
  ('en',2,'page 2 title en','page 2 content en'),
  ('en',3,'at en11','ac en11'),
  ('en',4,'111','222'),
  ('en',5,'11','22'),
  ('en',6,null,null),
  ('ru',1,'page 1 title ru22','page 1 content ru22'),
  ('ru',2,'page 2 title ru','page 2 content ru'),
  ('ru',3,'at ru11','ac ru11'),
  ('ru',4,'555','666'),
  ('ru',5,'55','66'),
  ('ru',6,null,null);

insert into `pages` (`id`, `page_name`, `page_type`, `page_order`, `page_date`) values
  (1,'home','page',11,'1975-06-08 11:20:00'),
  (3,'about','page',12,'1978-02-12 12:00:00'),
  (4,'oleg','page',4,'1980-02-02 12:45:00'),
  (5,'alex','page',12,null),
  (6,'lena','page',12,null);

insert into `room_prices` (`room`, `dt`, `price`) values
  ('single_room','2018-04-01',333.00),
  ('single_room','2018-04-02',12.00),
  ('single_room','2018-04-03',13.00),
  ('single_room','2018-04-04',14.00),
  ('single_room','2018-04-05',15.00),
  ('single_room','2018-04-06',16.00),
  ('single_room','2018-04-07',17.00),
  ('double_room','2018-04-07',27.00),
  ('suite','2018-04-07',37.00),
  ('suite_delux','2018-04-07',0.00),
  ('double_room','2018-04-02',22.00),
  ('suite','2018-04-02',32.00),
  ('suite_delux','2018-04-02',42.00),
  ('double_room','2018-04-01',21.00),
  ('suite','2018-04-01',31.00),
  ('suite_delux','2018-04-01',41.00),
  ('double_room','2018-04-03',23.00),
  ('double_room','2018-04-04',24.00),
  ('double_room','2018-04-05',25.00),
  ('double_room','2018-04-06',27.00),
  ('suite','2018-04-03',33.00),
  ('suite','2018-04-04',34.00),
  ('suite','2018-04-05',35.00),
  ('suite','2018-04-06',36.00),
  ('suite_delux','2018-04-03',43.00),
  ('suite_delux','2018-04-04',44.00),
  ('suite_delux','2018-04-05',45.00),
  ('suite_delux','2018-04-06',46.00);

insert into `settings` (`data`) values
  (null);

insert into `users` (`id`, `email`, `last_name`, `first_name`, `birth_date`, `salary`, `department_id`, `language`, `time_zone`, `last_login`) values
  (1,'jsmith@gmail.com','Smith','John','1970-02-02',1000.00,1,'az','europe','2026-02-02 12:45:12'),
  (2,'sparker@gmail.com','Parker','Sarah','1976-02-04',2000.44,1,NULL,NULL,'2026-02-02 12:45:12'),
  (3,'apetrov@gmail.com','Petrov','Alexei','1980-12-03',1500.00,1,NULL,NULL,'2026-02-02 12:45:12'),
  (4,'lsmirnova@gmail.com','Smirnova','Lena','1981-03-12',1200.55,1,NULL,NULL,'2026-02-02 12:45:12'),
  (5,'rschneider@gmail.com','Schnieder','Rolf','1960-09-08',3000.00,2,NULL,NULL,'2026-02-02 12:45:12'),
  (6,'uweinrich@gmail.com','Weinrich','Ulla','1979-01-05',2000.00,2,NULL,NULL,'2026-02-02 12:45:12'),
  (7,'aivanov@gmail.com','Ivanov','Alexander','1983-08-09',2300.70,2,NULL,NULL,'2026-02-02 12:45:12'),
  (8,'rschmidt@gmail.com','Schmidt','Ralf','1974-09-08',3400.00,3,NULL,NULL,'2026-02-02 12:45:12'),
  (9,'esinneger@gmail.com','Sinnegger','Elmar','1968-09-04',1800.00,3,NULL,NULL,'2026-02-02 12:45:12'),
  (10,'ahummel@gmail.com','Hummel','Angelika','1970-04-05',2800.00,3,NULL,NULL,'2026-02-02 12:45:12');

insert into `user_forum_settings` (`user_id`, `signature`, `status`, `hide_pictures`, `hide_signatures`) values
  (1,'i am here','guest',1,1);

insert into `user_colors` (`user_id`, `color`) values
  (1,'yellow'),
  (1,'red'),
  (1,'green');

delimiter //
   
create procedure collect_users(in p_min_salary DECIMAL(12,2))
    sql security invoker
begin
  delete from temp;
  
  insert into temp (email, last_name, first_name)
  select email, last_name, first_name from users where salary >= p_min_salary;
end;
  
//

delimiter ;
