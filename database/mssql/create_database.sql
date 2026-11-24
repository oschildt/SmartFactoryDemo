-- Run with sqlcmd or SQL Server Management Studio (GO batch separators).
-- Recreates framework_demo. All existing demo data will be removed.
use master;
go

if db_id(N'framework_demo') is not null
  drop database framework_demo;
go

create database framework_demo;
go

use framework_demo;
go

set ansi_nulls on;
set quoted_identifier on;
set ansi_padding on;
set ansi_warnings on;
set arithabort on;
go

create table dbo.dbworker_locks (
    resource_key char(64)
    collate latin1_general_100_bin2 not null,
    resource_name nvarchar(max) null,
    constraint dbworker_locks_pk primary key (resource_key)
);

create table dbo.department (
  id integer identity(1,1) not null,
  name nvarchar(250) not null,
  constraint department_pk primary key (id),
  constraint department_name_unq unique (name)
);

create table dbo.large_data (
  id integer not null,
  blob_data varbinary(max) null,
  text_data nvarchar(max) null
);

create table dbo.page_content (
  language_key nvarchar(3) not null,
  page_id integer not null,
  title nvarchar(250) null default null,
  content nvarchar(250) null default null,
  constraint page_content_unq unique (language_key, page_id)
);

create table dbo.pages (
  id integer identity(1,1) not null,
  page_name nvarchar(250) not null,
  page_type nvarchar(20) not null,
  page_order integer null default null,
  page_date datetime2(0) null default null,
  constraint pages_pk primary key (id),
  constraint page_name_unq unique (page_name)
);

create table dbo.room_prices (
  room nvarchar(250) not null,
  dt date not null,
  price DECIMAL(12,2) null default null
);

create table dbo.settings (
  id integer not null constraint settings_id_default default 1,
  data nvarchar(max) null,
  constraint settings_pk primary key (id)
);

create table dbo.users (
  id integer identity(1,1) not null,
  email nvarchar(255) null default null,
  last_name nvarchar(500) not null,
  first_name nvarchar(500) null default null,
  birth_date date null default null,
  salary DECIMAL(12,2) null default null,
  department_id integer not null,
  language nvarchar(20) null default null,
  time_zone nvarchar(20) null default null,
  last_login datetime2(0) default null,
  constraint users_pk primary key (id)
);

create table dbo.user_forum_settings (
  user_id integer not null,
  signature nvarchar(250) null default null,
  status nvarchar(250) null default null,
  hide_pictures integer not null default 0,
  hide_signatures integer not null default 0,
  constraint user_forum_settings_pk primary key (user_id)
);

create table dbo.user_colors (
  user_id integer not null,
  color nvarchar(255) null default null,
  constraint user_colors_unq unique (user_id, color)
);

go

set identity_insert dbo.department on;

insert into dbo.department (id, name) values
  (1,N'management'),
  (2,N'marketing'),
  (3,N'development'),
  (4,N'pr');

set identity_insert dbo.department off;

insert into dbo.large_data (id, blob_data, text_data) values
  (1,null,null);

insert into dbo.page_content (language_key, page_id, title, content) values
  (N'de',1,N'page 1 title de22',N'page 1 content de22'),
  (N'de',2,N'page 2 title de',N'page 2 content de'),
  (N'de',3,N'at de11',N'ac de11'),
  (N'de',4,N'333',N'444'),
  (N'de',5,N'33',N'44'),
  (N'de',6,null,null),
  (N'en',1,N'page 1 title en2211',N'page 1 content en2211'),
  (N'en',2,N'page 2 title en',N'page 2 content en'),
  (N'en',3,N'at en11',N'ac en11'),
  (N'en',4,N'111',N'222'),
  (N'en',5,N'11',N'22'),
  (N'en',6,null,null),
  (N'ru',1,N'page 1 title ru22',N'page 1 content ru22'),
  (N'ru',2,N'page 2 title ru',N'page 2 content ru'),
  (N'ru',3,N'at ru11',N'ac ru11'),
  (N'ru',4,N'555',N'666'),
  (N'ru',5,N'55',N'66'),
  (N'ru',6,null,null);

set identity_insert dbo.pages on;

insert into dbo.pages (id, page_name, page_type, page_order, page_date) values
  (1,N'home',N'page',11,convert(datetime2(0), '1975-06-08T11:20:00', 126)),
  (3,N'about',N'page',12,convert(datetime2(0), '1978-02-12T12:00:00', 126)),
  (4,N'oleg',N'page',4,convert(datetime2(0), '1980-02-02T12:45:00', 126)),
  (5,N'alex',N'page',12,null),
  (6,N'lena',N'page',12,null);

set identity_insert dbo.pages off;

insert into dbo.room_prices (room, dt, price) values
  (N'single_room',convert(date, '2018-04-01'),333.00),
  (N'single_room',convert(date, '2018-04-02'),12.00),
  (N'single_room',convert(date, '2018-04-03'),13.00),
  (N'single_room',convert(date, '2018-04-04'),14.00),
  (N'single_room',convert(date, '2018-04-05'),15.00),
  (N'single_room',convert(date, '2018-04-06'),16.00),
  (N'single_room',convert(date, '2018-04-07'),17.00),
  (N'double_room',convert(date, '2018-04-07'),27.00),
  (N'suite',convert(date, '2018-04-07'),37.00),
  (N'suite_delux',convert(date, '2018-04-07'),0.00),
  (N'double_room',convert(date, '2018-04-02'),22.00),
  (N'suite',convert(date, '2018-04-02'),32.00),
  (N'suite_delux',convert(date, '2018-04-02'),42.00),
  (N'double_room',convert(date, '2018-04-01'),21.00),
  (N'suite',convert(date, '2018-04-01'),31.00),
  (N'suite_delux',convert(date, '2018-04-01'),41.00),
  (N'double_room',convert(date, '2018-04-03'),23.00),
  (N'double_room',convert(date, '2018-04-04'),24.00),
  (N'double_room',convert(date, '2018-04-05'),25.00),
  (N'double_room',convert(date, '2018-04-06'),27.00),
  (N'suite',convert(date, '2018-04-03'),33.00),
  (N'suite',convert(date, '2018-04-04'),34.00),
  (N'suite',convert(date, '2018-04-05'),35.00),
  (N'suite',convert(date, '2018-04-06'),36.00),
  (N'suite_delux',convert(date, '2018-04-03'),43.00),
  (N'suite_delux',convert(date, '2018-04-04'),44.00),
  (N'suite_delux',convert(date, '2018-04-05'),45.00),
  (N'suite_delux',convert(date, '2018-04-06'),46.00);

insert into dbo.settings (data) values
  (null);

set identity_insert dbo.users on;

insert into dbo.users (id, email, last_name, first_name, birth_date, salary, department_id, language, time_zone, last_login) values
  (1,N'jsmith@gmail.com',N'Smith',N'John',convert(date, '1970-02-02', 126),1000.00,1,N'az',N'europe','2026-02-02T12:45:12'),
  (2,N'sparker@gmail.com',N'Parker',N'Sarah',convert(date, '1976-02-04', 126),2000.45,1,NULL,NULL,'2026-02-02T12:45:12'),
  (3,N'apetrov@gmail.com',N'Petrov',N'Alexei',convert(date, '1980-12-03', 126),1500.00,1,NULL,NULL,'2026-02-02T12:45:12'),
  (4,N'lsmirnova@gmail.com',N'Smirnova',N'Lena',convert(date, '1981-03-12', 126),1200.55,1,NULL,NULL,'2026-02-02T12:45:12'),
  (5,N'rschneider@gmail.com',N'Schnieder',N'Rolf',convert(date, '1960-09-08', 126),3000.00,2,NULL,NULL,'2026-02-02T12:45:12'),
  (6,N'uweinrich@gmail.com',N'Weinrich',N'Ulla',convert(date, '1979-01-05', 126),2000.00,2,NULL,NULL,'2026-02-02T12:45:12'),
  (7,N'aivanov@gmail.com',N'Ivanov',N'Alexander',convert(date, '1983-08-09', 126),2300.70,2,NULL,NULL,'2026-02-02T12:45:12'),
  (8,N'rschmidt@gmail.com',N'Schmidt',N'Ralf',convert(date, '1974-09-08', 126),3400.00,3,NULL,NULL,'2026-02-02T12:45:12'),
  (9,N'esinneger@gmail.com',N'Sinnegger',N'Elmar',convert(date, '1968-09-04', 126),1800.00,3,NULL,NULL,'2026-02-02T12:45:12'),
  (10,N'ahummel@gmail.com',N'Hummel',N'Angelika',convert(date, '1970-04-05', 126),2800.00,3,NULL,NULL,'2026-02-02T12:45:12');

set identity_insert dbo.users off;

insert into dbo.user_forum_settings (user_id, signature, status, hide_pictures, hide_signatures) values
  (1,N'i am here',N'guest',1,1);

insert into dbo.user_colors (user_id, color) values
  (1,N'yellow'),
  (1,N'red'),
  (1,N'green');
go

-- The caller creates the session-local temporary table #temp.
create procedure dbo.collect_users @min_salary decimal(12,2)
as
begin
  set nocount on;

  delete from #temp;

  insert into #temp (email, last_name, first_name)
  select email, last_name, first_name from dbo.users where salary >= @min_salary;
end;
go
