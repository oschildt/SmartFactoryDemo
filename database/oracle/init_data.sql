-- Same explicit IDs and initial data as mysql/create_database.sql.
-- Disable SQL*Plus substitution so text values can contain ampersands.
set define off

insert into department (id, name) values (1,'management');
insert into department (id, name) values (2,'marketing');
insert into department (id, name) values (3,'development');
insert into department (id, name) values (4,'pr');

insert into large_data (id, blob_data, text_data) values (1,null,null);

insert into page_content (language_key, page_id, title, content) values ('de',1,'page 1 title de22','page 1 content de22');
insert into page_content (language_key, page_id, title, content) values ('de',2,'page 2 title de','page 2 content de');
insert into page_content (language_key, page_id, title, content) values ('de',3,'at de11','ac de11');
insert into page_content (language_key, page_id, title, content) values ('de',4,'333','444');
insert into page_content (language_key, page_id, title, content) values ('de',5,'33','44');
insert into page_content (language_key, page_id, title, content) values ('de',6,null,null);
insert into page_content (language_key, page_id, title, content) values ('en',1,'page 1 title en2211','page 1 content en2211');
insert into page_content (language_key, page_id, title, content) values ('en',2,'page 2 title en','page 2 content en');
insert into page_content (language_key, page_id, title, content) values ('en',3,'at en11','ac en11');
insert into page_content (language_key, page_id, title, content) values ('en',4,'111','222');
insert into page_content (language_key, page_id, title, content) values ('en',5,'11','22');
insert into page_content (language_key, page_id, title, content) values ('en',6,null,null);
insert into page_content (language_key, page_id, title, content) values ('ru',1,'page 1 title ru22','page 1 content ru22');
insert into page_content (language_key, page_id, title, content) values ('ru',2,'page 2 title ru','page 2 content ru');
insert into page_content (language_key, page_id, title, content) values ('ru',3,'at ru11','ac ru11');
insert into page_content (language_key, page_id, title, content) values ('ru',4,'555','666');
insert into page_content (language_key, page_id, title, content) values ('ru',5,'55','66');
insert into page_content (language_key, page_id, title, content) values ('ru',6,null,null);

insert into pages (id, page_name, page_type, page_order, page_date) values (1,'home','page',11,to_timestamp('1975-06-08 11:20:00', 'YYYY-MM-DD HH24:MI:SS'));
insert into pages (id, page_name, page_type, page_order, page_date) values (3,'about','page',12,to_timestamp('1978-02-12 12:00:00', 'YYYY-MM-DD HH24:MI:SS'));
insert into pages (id, page_name, page_type, page_order, page_date) values (4,'oleg','page',4,to_timestamp('1980-02-02 12:45:00', 'YYYY-MM-DD HH24:MI:SS'));
insert into pages (id, page_name, page_type, page_order, page_date) values (5,'alex','page',12,null);
insert into pages (id, page_name, page_type, page_order, page_date) values (6,'lena','page',12,null);

insert into room_prices (room, dt, price) values ('single_room',date '2018-04-01',333.00);
insert into room_prices (room, dt, price) values ('single_room',date '2018-04-02',12.00);
insert into room_prices (room, dt, price) values ('single_room',date '2018-04-03',13.00);
insert into room_prices (room, dt, price) values ('single_room',date '2018-04-04',14.00);
insert into room_prices (room, dt, price) values ('single_room',date '2018-04-05',15.00);
insert into room_prices (room, dt, price) values ('single_room',date '2018-04-06',16.00);
insert into room_prices (room, dt, price) values ('single_room',date '2018-04-07',17.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-07',27.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-07',37.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-07',0.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-02',22.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-02',32.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-02',42.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-01',21.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-01',31.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-01',41.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-03',23.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-04',24.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-05',25.00);
insert into room_prices (room, dt, price) values ('double_room',date '2018-04-06',27.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-03',33.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-04',34.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-05',35.00);
insert into room_prices (room, dt, price) values ('suite',date '2018-04-06',36.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-03',43.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-04',44.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-05',45.00);
insert into room_prices (room, dt, price) values ('suite_delux',date '2018-04-06',46.00);

insert into settings (data) values (null);

insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (1,'jsmith@gmail.com','Smith','John',date '1970-02-02',1000.00,1,'az','europe',to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (2,'sparker@gmail.com','Parker','Sarah',date '1976-02-04',2000.44,1,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (3,'apetrov@gmail.com','Petrov','Alexei',date '1980-12-03',1500.00,1,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (4,'lsmirnova@gmail.com','Smirnova','Lena',date '1981-03-12',1200.55,1,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (5,'rschneider@gmail.com','Schnieder','Rolf',date '1960-09-08',3000.00,2,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (6,'uweinrich@gmail.com','Weinrich','Ulla',date '1979-01-05',2000.00,2,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (7,'aivanov@gmail.com','Ivanov','Alexander',date '1983-08-09',2300.70,2,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (8,'rschmidt@gmail.com','Schmidt','Ralf',date '1974-09-08',3400.00,3,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (9,'esinneger@gmail.com','Sinnegger','Elmar',date '1968-09-04',1800.00,3,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));
insert into users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values (10,'ahummel@gmail.com','Hummel','Angelika',date '1970-04-05',2800.00,3,NULL,NULL,to_timestamp('2026-02-02 12:45:12', 'YYYY-MM-DD HH24:MI:SS'));

insert into user_forum_settings (user_id, signature, status, hide_pictures, hide_signatures) values (1,'i am here','guest',1,1);

insert into user_colors (user_id, color) values (1,'yellow');
insert into user_colors (user_id, color) values (1,'red');
insert into user_colors (user_id, color) values (1,'green');

commit;
