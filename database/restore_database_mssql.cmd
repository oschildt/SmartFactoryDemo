@echo off

rem set the path to sqlcmd.exe

set OSQL_PATH="c:\Program Files\Microsoft SQL Server\Client SDK\ODBC\130\Tools\Binn\"

rem set the instance name

set HOST="(local)"

rem Due to the restriction that the SQL driver sticks to the encoding
rem of the Windows and ignores the database and server collation, the 
rem following should be ensured:

rem - The encoding of the Windows and that of the SQL Server must be identical.
rem - The collation of the database should be identical to that of the server.
rem - The code page in the sqlcmd should be identical to that of the server.

set CODEPAGE="1251"

echo *****************************************************
echo * Creating MSSQL database                           *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating database                                  
echo -----------------------------------------------------

%OSQL_PATH%sqlcmd -S %HOST% -E -b -Q "IF EXISTS (SELECT name FROM sys.databases WHERE name = N'framework_demo') DROP DATABASE [framework_demo]"
if not %errorlevel%==0 goto err

%OSQL_PATH%sqlcmd -S %HOST% -E -b -i create_database_mssql.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 2: creating tables                                   
echo -----------------------------------------------------

%OSQL_PATH%sqlcmd -S %HOST% -E -b -d framework_demo -i framework_demo_mssql.sql
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



