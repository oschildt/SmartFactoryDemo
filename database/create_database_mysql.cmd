@echo off

rem set the path to mysql.exe
 
set MYSQL_PATH="c:\web\mysql\bin\mysql.exe"

echo *****************************************************
echo * Creating MySQL database                           *
echo *****************************************************

echo -----------------------------------------------------
echo Creating database                                  
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password < create_database_mysql.sql 
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

