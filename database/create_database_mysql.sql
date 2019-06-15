drop database if exists framework_demo;

create database if not exists framework_demo;

alter database framework_demo CHARACTER SET utf8 collate utf8_general_ci;

use framework_demo;

CREATE TABLE `DEPARTMENT` (
  `ID` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `NAME` VARCHAR(250) COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `ID` (`ID`) USING BTREE,
  UNIQUE KEY `NAME` (`NAME`) USING BTREE
) ENGINE=InnoDB
AUTO_INCREMENT=5 AVG_ROW_LENGTH=4096 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `LARGE_DATA` (
  `ID` INTEGER(11) NOT NULL,
  `BLOB_DATA` LONGBLOB,
  `TEXT_DATA` LONGTEXT COLLATE utf8_general_ci
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `PAGE_CONTENT` (
  `LANGUAGE_KEY` VARCHAR(3) COLLATE utf8_general_ci NOT NULL,
  `PAGE_ID` INTEGER(11) NOT NULL,
  `TITLE` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  `CONTENT` VARCHAR(250) COLLATE utf8_general_ci DEFAULT NULL,
  UNIQUE KEY `PAGE_CONTENT_UNQ` (`LANGUAGE_KEY`, `PAGE_ID`) USING BTREE
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `PAGES` (
  `ID` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `PAGE_NAME` VARCHAR(250) COLLATE utf8_general_ci NOT NULL,
  `PAGE_TYPE` VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
  `PAGE_ORDER` INTEGER(11) DEFAULT NULL,
  `PAGE_DATE` DATETIME(6) DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `PAGE_NAME_UNQ` (`PAGE_NAME`) USING BTREE
) ENGINE=InnoDB
AUTO_INCREMENT=7 ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `ROOM_PRICES` (
  `ROOM` VARCHAR(250) COLLATE utf8_general_ci NOT NULL,
  `DT` DATE NOT NULL,
  `PRICE` FLOAT(9,3) DEFAULT NULL
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `SETTINGS` (
  `DATA` TEXT COLLATE utf8_general_ci
) ENGINE=InnoDB
ROW_FORMAT=DYNAMIC CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'
;

CREATE TABLE `USERS` (
  `ID` INTEGER(11) NOT NULL AUTO_INCREMENT,
  `EMAIL` VARCHAR(255) COLLATE latin1_swedish_ci DEFAULT NULL,
  `LAST_NAME` VARCHAR(500) COLLATE latin1_swedish_ci NOT NULL,
  `FIRST_NAME` VARCHAR(500) COLLATE latin1_swedish_ci DEFAULT NULL,
  `BIRTH_DATE` DATETIME DEFAULT NULL,
  `SALARY` DOUBLE(15,3) DEFAULT NULL,
  `DEPARTMENT_ID` INTEGER(11) NOT NULL,
  `SIGNATURE` VARCHAR(250) COLLATE latin1_swedish_ci DEFAULT NULL,
  `STATUS` VARCHAR(250) COLLATE latin1_swedish_ci DEFAULT NULL,
  `HIDE_PICTURES` INTEGER(11) NOT NULL DEFAULT 0,
  `HIDE_SIGNATURES` INTEGER(11) NOT NULL DEFAULT 0,
  `LANGUAGE` VARCHAR(20) COLLATE latin1_swedish_ci DEFAULT NULL,
  `TIME_ZONE` VARCHAR(20) COLLATE latin1_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `ID` (`ID`) USING BTREE
) ENGINE=InnoDB
AUTO_INCREMENT=11 AVG_ROW_LENGTH=1638 ROW_FORMAT=DYNAMIC CHARACTER SET 'latin1' COLLATE 'latin1_swedish_ci'
;

INSERT INTO `DEPARTMENT` (`ID`, `NAME`) VALUES
  (1,'Management'),
  (2,'Marketing'),
  (3,'Development'),
  (4,'PR');

INSERT INTO `LARGE_DATA` (`ID`, `BLOB_DATA`, `TEXT_DATA`) VALUES
  (1,NULL,NULL);

INSERT INTO `PAGE_CONTENT` (`LANGUAGE_KEY`, `PAGE_ID`, `TITLE`, `CONTENT`) VALUES
  ('de',1,'page 1 title de22','page 1 content de22'),
  ('de',2,'page 2 title de','page 2 content de'),
  ('de',3,'at de11','ac de11'),
  ('de',4,'333','444'),
  ('de',5,'33','44'),
  ('de',6,NULL,NULL),
  ('en',1,'page 1 title en2211','page 1 content en2211'),
  ('en',2,'page 2 title en','page 2 content en'),
  ('en',3,'at en11','ac en11'),
  ('en',4,'111','222'),
  ('en',5,'11','22'),
  ('en',6,NULL,NULL),
  ('ru',1,'page 1 title ru22','page 1 content ru22'),
  ('ru',2,'page 2 title ru','page 2 content ru'),
  ('ru',3,'at ru11','ac ru11'),
  ('ru',4,'555','666'),
  ('ru',5,'55','66'),
  ('ru',6,NULL,NULL);

INSERT INTO `PAGES` (`ID`, `PAGE_NAME`, `PAGE_TYPE`, `PAGE_ORDER`, `PAGE_DATE`) VALUES
  (1,'home','page',11,'1975-06-08 11:20:00.000000'),
  (3,'about','page',12,'1978-02-12 12:00:00.000000'),
  (4,'oleg','page',4,'1980-02-02 12:45:00.000000'),
  (5,'alex','page',12,NULL),
  (6,'lena','page',12,NULL);

INSERT INTO `ROOM_PRICES` (`ROOM`, `DT`, `PRICE`) VALUES
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

INSERT INTO `SETTINGS` (`DATA`) VALUES
  (NULL);

INSERT INTO `users` (`ID`, `EMAIL`, `LAST_NAME`, `FIRST_NAME`, `BIRTH_DATE`, `SALARY`, `DEPARTMENT_ID`, `SIGNATURE`, `STATUS`, `HIDE_PICTURES`, `HIDE_SIGNATURES`, `LANGUAGE`, `TIME_ZONE`) VALUES
  (1,'jsmith@gmail.com','Smith','John','1970-02-02 00:00:00',1000.000,1,'i am here','Guest',1,1,'az','europe'),
  (2,'sparker@gmail.com','Parker','Sarah','1976-02-04 00:00:00',2000.000,1,NULL,NULL,0,0,NULL,NULL),
  (3,'apetrov@gmail.com','Petrov','Alexei','1980-12-03 00:00:00',1500.000,1,NULL,NULL,0,0,NULL,NULL),
  (4,'lsmirnova@gmail.com','Smirnova','Lena','1981-03-12 00:00:00',1200.000,1,NULL,NULL,0,0,NULL,NULL),
  (5,'rschneider@gmail.com','Schnieder','Rolf','1960-09-08 00:00:00',3000.000,2,NULL,NULL,0,0,NULL,NULL),
  (6,'uweinrich@gmail.com','Weinrich','Ulla','1979-01-05 00:00:00',2000.000,2,NULL,NULL,0,0,NULL,NULL),
  (7,'aivanov@gmail.com','Ivanov','Alexander','1983-08-09 00:00:00',2300.000,2,NULL,NULL,0,0,NULL,NULL),
  (8,'rschmidt@gmail.com','Schmidt','Ralf','1974-09-08 00:00:00',3400.000,3,NULL,NULL,0,0,NULL,NULL),
  (9,'esinneger@gmail.com','Sinnegger','Elmar','1968-09-04 00:00:00',1800.000,3,NULL,NULL,0,0,NULL,NULL),
  (10,'ahummel@gmail.com','Hummel','Angelika','1970-04-05 00:00:00',2800.000,3,NULL,NULL,0,0,NULL,NULL);

DELIMITER //
   
CREATE PROCEDURE GET_USERS(P_MIN_SALARY FLOAT)
    DETERMINISTIC
    SQL SECURITY INVOKER
    COMMENT ''
BEGIN
  CREATE TEMPORARY TABLE IF NOT EXISTS TEMP(
    EMAIL VARCHAR(255) DEFAULT NULL,
    LAST_NAME VARCHAR(500) NOT NULL,
    FIRST_NAME VARCHAR(500) DEFAULT NULL
  );
  
  DELETE FROM TEMP;

  INSERT INTO TEMP (EMAIL, LAST_NAME, FIRST_NAME)
  SELECT EMAIL, LAST_NAME, FIRST_NAME FROM USERS WHERE SALARY >= P_MIN_SALARY;
  
  SELECT * FROM TEMP;
  
  DROP TABLE TEMP;
END;
  
//

DELIMITER ;
