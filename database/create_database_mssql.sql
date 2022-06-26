/*
Due to the restriction that the SQL driver sticks to the encoding
of the Windows and ignores the database and server collation, the 
following should be ensured:

- The encoding of the Windows and that of the SQL Server must be identical.
- The collation of the database should be identical to that of the server.
- The code page in the sqlcmd should be identical to that of the server.
*/

IF EXISTS (SELECT name FROM sys.databases WHERE name = N'framework_demo') DROP DATABASE [framework_demo]
GO

IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'framework_demo')
CREATE DATABASE [framework_demo] 
GO

EXEC dbo.sp_dbcmptlevel @dbname = N'framework_demo', @new_cmptlevel = 100
GO

ALTER DATABASE [framework_demo] SET ANSI_NULL_DEFAULT OFF 
GO

ALTER DATABASE [framework_demo] SET ANSI_NULLS OFF 
GO

ALTER DATABASE [framework_demo] SET ANSI_PADDING OFF 
GO

ALTER DATABASE [framework_demo] SET ANSI_WARNINGS OFF 
GO

ALTER DATABASE [framework_demo] SET ARITHABORT OFF 
GO

ALTER DATABASE [framework_demo] SET AUTO_CLOSE OFF 
GO

ALTER DATABASE [framework_demo] SET AUTO_CREATE_STATISTICS ON 
GO

ALTER DATABASE [framework_demo] SET AUTO_SHRINK OFF 
GO

ALTER DATABASE [framework_demo] SET AUTO_UPDATE_STATISTICS ON 
GO

ALTER DATABASE [framework_demo] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO

ALTER DATABASE [framework_demo] SET CURSOR_DEFAULT  GLOBAL 
GO

ALTER DATABASE [framework_demo] SET CONCAT_NULL_YIELDS_NULL OFF 
GO

ALTER DATABASE [framework_demo] SET NUMERIC_ROUNDABORT OFF 
GO

ALTER DATABASE [framework_demo] SET QUOTED_IDENTIFIER OFF 
GO

ALTER DATABASE [framework_demo] SET RECURSIVE_TRIGGERS OFF 
GO

ALTER DATABASE [framework_demo] SET  ENABLE_BROKER 
GO

ALTER DATABASE [framework_demo] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO

ALTER DATABASE [framework_demo] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO

ALTER DATABASE [framework_demo] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO

ALTER DATABASE [framework_demo] SET PARAMETERIZATION SIMPLE 
GO

ALTER DATABASE [framework_demo] SET  READ_WRITE 
GO

ALTER DATABASE [framework_demo] SET RECOVERY FULL 
GO

ALTER DATABASE [framework_demo] SET  MULTI_USER 
GO

ALTER DATABASE [framework_demo] SET PAGE_VERIFY CHECKSUM  
GO

USE [framework_demo]
GO

create table department (
  id integer identity,
  name varchar(250) not null,
  constraint department_pk primary key nonclustered (id)
)
go

create unique index department_name_unq on department (
name asc
)
go

create table large_data (
  id integer not null,
  blob_data image,
  text_data text 
) 
go

create table page_content (
  language_key varchar(3) default null,
  page_id integer default null,
  title varchar(250) default null,
  content varchar(250) default null
) 
go

create unique index page_content_unq on page_content (
language_key asc,
page_id asc
)
go

create table users (
  id integer identity,
  email varchar(255) default null,
  last_name varchar(500) not null,
  first_name varchar(500) default null,
  birth_date datetime default null,
  salary float default null,
  department_id integer not null,
  language varchar(20) default null,
  time_zone varchar(20) default null,
  constraint users_pk primary key nonclustered (id)
) 
go

create table user_forum_settings (
  user_id integer,
  signature varchar(250) default null,
  status varchar(250) default null,
  hide_pictures integer not null default 0,
  hide_signatures integer not null default 0
)
go

create table user_colors (
  user_id integer,
  color varchar(255) default null   
)
go

set identity_insert department on
go

insert into department (id, name) values
  (1,'management')
go
insert into department (id, name) values
  (2,'marketing')
go
insert into department (id, name) values
  (3,'development')
go
insert into department (id, name) values
  (4,'pr')
go

set identity_insert department off
go

insert into large_data (id, blob_data, text_data) values
  (1,null,null)
go

insert into page_content (language_key, page_id, title, content) values
  ('en',1,'page 1 title en','page 1 content en')
go
insert into page_content (language_key, page_id, title, content) values
  ('de',1,'page 1 title de','page 1 content de')
go
insert into page_content (language_key, page_id, title, content) values
  ('ru',1,'page 1 title ru','page 1 content ru')
go
insert into page_content (language_key, page_id, title, content) values
  ('en',2,'page 2 title en','page 2 content en')
go
insert into page_content (language_key, page_id, title, content) values
  ('de',2,'page 2 title de','page 2 content de')
go
insert into page_content (language_key, page_id, title, content) values
  ('ru',2,'page 2 title ru','page 2 content ru')
go

set identity_insert users on
go

insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (1,'jsmith@gmail.com','Smith','John','1970-02-02 00:00:00',1000.000,1)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (2,'sparker@gmail.com','Parker','Sarah','1976-02-04 00:00:00',2000.000,1)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (3,'apetrov@gmail.com','Petrov','Alexei','1980-12-03 00:00:00',1500.000,1)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (4,'lsmirnova@gmail.com','Smirnova','Lena','1981-03-12 00:00:00',1200.000,1)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (5,'rschneider@gmail.com','Schnieder','Rolf','1960-09-08 00:00:00',3000.000,2)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (6,'uweinrich@gmail.com','Weinrich','Ulla','1979-01-05 00:00:00',2000.000,2)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (7,'aivanov@gmail.com','Ivanov','Alexander','1983-08-09 00:00:00',2300.000,2)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (8,'rschmidt@gmail.com','Schmidt','Ralf','1974-09-08 00:00:00',3400.000,3)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (9,'esinneger@gmail.com','Sinnegger','Elmar','1968-09-04 00:00:00',1800.000,3)
go
insert into users (id, email, last_name, first_name, birth_date, salary, department_id) values
  (10,'ahummel@gmail.com','Hummel','Angelika','1970-04-05 00:00:00',2800.000,3)
go

set identity_insert users off
go

insert into user_forum_settings (user_id, signature, status, hide_pictures, hide_signatures) values
  (1,'i am here','guest',1,1)
go

insert into user_colors (user_id, color) values
  (1,'yellow')
go
insert into user_colors (user_id, color) values
  (1,'red')
go
insert into user_colors (user_id, color) values
  (1,'green')
go

create procedure collect_users @min_salary as float
as
begin
  delete from #temp

  insert into #temp (email, last_name, first_name)
  select email, last_name, first_name from users where salary >= @min_salary
end
go
