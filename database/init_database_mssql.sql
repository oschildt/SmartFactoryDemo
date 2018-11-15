EXEC dbo.sp_dbcmptlevel @dbname = N'framework_demo', @new_cmptlevel = 100
GO

/*
Due to the restriction that the SQL driver sticks to the encoding
of the Windows and ignores the database and server collation, the 
following should be ensured:

- The encoding of the Windows and that of the SQL Server must be identical.
- The collation of the database should be identical to that of the server.
- The code page in the sqlcmd should be identical to that of the server.
*/

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

