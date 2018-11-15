/*
Due to the restriction that the SQL driver sticks to the encoding
of the Windows and ignores the database and server collation, the 
following should be ensured:

- The encoding of the Windows and that of the SQL Server must be identical.
- The collation of the database should be identical to that of the server.
- The code page in the sqlcmd should be identical to that of the server.
*/

IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'framework_demo')
CREATE DATABASE [framework_demo] 
GO
