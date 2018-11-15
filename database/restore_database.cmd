@echo off

rem set the path to mysql.exe
 
set MYSQL_PATH="c:\web\mysql\bin\mysql.exe"

rem set the root password

set ROOT_PASSWORD="root"

echo *****************************************************
echo * Creating MySQL database                           *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating database                                  
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "drop database if exists framework_demo"
if not %errorlevel%==0 goto err

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% < create_database.sql 
if not %errorlevel%==0 goto err

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% framework_demo < init_database.sql 
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 2: restoring tables                                   
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% framework_demo < framework_demo.sql 
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Database successfully restored                     
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

