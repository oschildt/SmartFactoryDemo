drop database if exists framework_demo;

create database if not exists framework_demo;

alter database framework_demo character set utf8 collate utf8_general_ci;

use framework_demo;

create table `department` (
  `id` integer(11) not null auto_increment,
  `name` varchar(250) collate utf8_general_ci not null,
  primary key (`id`) using btree,
  unique key `id` (`id`) using btree,
  unique key `name` (`name`) using btree
) engine=innodb
auto_increment=5 avg_row_length=4096 row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `large_data` (
  `id` integer(11) not null,
  `blob_data` longblob,
  `text_data` longtext collate utf8_general_ci
) engine=innodb
row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `page_content` (
  `language_key` varchar(3) collate utf8_general_ci not null,
  `page_id` integer(11) not null,
  `title` varchar(250) collate utf8_general_ci default null,
  `content` varchar(250) collate utf8_general_ci default null,
  unique key `page_content_unq` (`language_key`, `page_id`) using btree
) engine=innodb
row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `pages` (
  `id` integer(11) not null auto_increment,
  `page_name` varchar(250) collate utf8_general_ci not null,
  `page_type` varchar(20) collate utf8_general_ci not null,
  `page_order` integer(11) default null,
  `page_date` datetime(6) default null,
  primary key (`id`) using btree,
  unique key `page_name_unq` (`page_name`) using btree
) engine=innodb
auto_increment=7 row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `room_prices` (
  `room` varchar(250) collate utf8_general_ci not null,
  `dt` date not null,
  `price` float(9,3) default null
) engine=innodb
row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `settings` (
  `data` text collate utf8_general_ci
) engine=innodb
row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `users` (
  `id` integer(11) not null auto_increment,
  `email` varchar(255) collate latin1_swedish_ci default null,
  `last_name` varchar(500) collate latin1_swedish_ci not null,
  `first_name` varchar(500) collate latin1_swedish_ci default null,
  `birth_date` datetime default null,
  `salary` double(15,3) default null,
  `department_id` integer(11) not null,
  `language` varchar(20) collate latin1_swedish_ci default null,
  `time_zone` varchar(20) collate latin1_swedish_ci default null,
  primary key (`id`) using btree,
  unique key `id` (`id`) using btree
) engine=innodb
auto_increment=11 avg_row_length=1638 row_format=dynamic character set 'latin1' collate 'latin1_swedish_ci'
;

create table `user_forum_settings` (
  `user_id` integer(11) not null,
  `signature` varchar(250) collate latin1_swedish_ci default null,
  `status` varchar(250) collate latin1_swedish_ci default null,
  `hide_pictures` integer(11) not null default 0,
  `hide_signatures` integer(11) not null default 0
) engine=innodb
row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

create table `user_colors` (
  `user_id` integer(11) not null,
  `color` varchar(255) collate latin1_swedish_ci default null   
) engine=innodb
row_format=dynamic character set 'utf8' collate 'utf8_general_ci'
;

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
  (1,'home','page',11,'1975-06-08 11:20:00.000000'),
  (3,'about','page',12,'1978-02-12 12:00:00.000000'),
  (4,'oleg','page',4,'1980-02-02 12:45:00.000000'),
  (5,'alex','page',12,null),
  (6,'lena','page',12,null);

insert into `room_prices` (`room`, `dt`, `price`) values
  ('single_room','2018-04-01',333.000),
  ('single_room','2018-04-02',12.000),
  ('single_room','2018-04-03',13.000),
  ('single_room','2018-04-04',14.000),
  ('single_room','2018-04-05',15.000),
  ('single_room','2018-04-06',16.000),
  ('single_room','2018-04-07',17.000),
  ('double_room','2018-04-07',27.000),
  ('suite','2018-04-07',37.000),
  ('suite_delux','2018-04-07',0.000),
  ('double_room','2018-04-02',22.000),
  ('suite','2018-04-02',32.000),
  ('suite_delux','2018-04-02',42.000),
  ('double_room','2018-04-01',21.000),
  ('suite','2018-04-01',31.000),
  ('suite_delux','2018-04-01',41.000),
  ('double_room','2018-04-03',23.000),
  ('double_room','2018-04-04',24.000),
  ('double_room','2018-04-05',25.000),
  ('double_room','2018-04-06',27.000),
  ('suite','2018-04-03',33.000),
  ('suite','2018-04-04',34.000),
  ('suite','2018-04-05',35.000),
  ('suite','2018-04-06',36.000),
  ('suite_delux','2018-04-03',43.000),
  ('suite_delux','2018-04-04',44.000),
  ('suite_delux','2018-04-05',45.000),
  ('suite_delux','2018-04-06',46.000);

insert into `settings` (`data`) values
  (null);

insert into `users` (`id`, `email`, `last_name`, `first_name`, `birth_date`, `salary`, `department_id`, `language`, `time_zone`) values
  (1,'jsmith@gmail.com','Smith','John','1970-02-02 00:00:00',1000.000,1,'az','europe'),
  (2,'sparker@gmail.com','Parker','Sarah','1976-02-04 00:00:00',2000.000,1,NULL,NULL),
  (3,'apetrov@gmail.com','Petrov','Alexei','1980-12-03 00:00:00',1500.000,1,NULL,NULL),
  (4,'lsmirnova@gmail.com','Smirnova','Lena','1981-03-12 00:00:00',1200.000,1,NULL,NULL),
  (5,'rschneider@gmail.com','Schnieder','Rolf','1960-09-08 00:00:00',3000.000,2,NULL,NULL),
  (6,'uweinrich@gmail.com','Weinrich','Ulla','1979-01-05 00:00:00',2000.000,2,NULL,NULL),
  (7,'aivanov@gmail.com','Ivanov','Alexander','1983-08-09 00:00:00',2300.000,2,NULL,NULL),
  (8,'rschmidt@gmail.com','Schmidt','Ralf','1974-09-08 00:00:00',3400.000,3,NULL,NULL),
  (9,'esinneger@gmail.com','Sinnegger','Elmar','1968-09-04 00:00:00',1800.000,3,NULL,NULL),
  (10,'ahummel@gmail.com','Hummel','Angelika','1970-04-05 00:00:00',2800.000,3,NULL,NULL);

insert into `user_forum_settings` (`user_id`, `signature`, `status`, `hide_pictures`, `hide_signatures`) values
  (1,'i am here','guest',1,1);

insert into `user_colors` (`user_id`, `color`) values
  (1,'yellow'),
  (1,'red'),
  (1,'green');

delimiter //
   
create procedure collect_users(p_min_salary float)
    deterministic
    sql security invoker
    comment ''
begin
  delete from temp;
  
  insert into temp (email, last_name, first_name)
  select email, last_name, first_name from users where salary >= p_min_salary;
end;
  
//

delimiter ;
