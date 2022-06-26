@echo off

rem set the path to postgre.exe
 
set PGSQL_PATH="c:\PostgreSQL\bin\psql.exe"

rem set the root password

echo *****************************************************
echo * Creating PostgreSQL database                      *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating the database                                  
echo -----------------------------------------------------

%PGSQL_PATH% -U postgres < create_database_postgres.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Database successfully created                     
echo -----------------------------------------------------
pause
@echo on
exit

:err
echo -----------------------------------------------------
echo Error detected. Please read the error message     
echo supplied from script, eliminate the problem and   
echo repeate the action!                               
echo -----------------------------------------------------
pause
@echo on

