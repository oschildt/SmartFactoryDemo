## SmartFactory demo application

Demo application to demonstrate how to use the library [SmartFactory](https://github.com/oschildt/SmartFactory).

### To get familiar with the SmartFactory do the following

- Git-clone the demo application.
- Use the script database/restore_database.cmd (restore_database_mssql.cmd) to create a demo database necessary for some examples.
- View and study the API documentation in the folder docs or here [API documentation](http://php-smart-factory.org/docs/).
- Study the core code of the library SmartFactory.

### To start writing own application using SmartFactory

1. Git-clone the demo application.

2. Study the directory structure of the demo application and the code.

3. Implement your classes and functions. Use the script tests/classtester.php to check your classes for correct syntax.

4. Bind you classes to the interfaces in the file factory_init_inc.php to be able to use the IoC approach for creating objects offered by the library SmartFactory.

5. Implement you business logic in the root directory or any subdirectory. 

7. Implement the API request handles for JSON or XML if necessary.

8. Add translation texts for your application over the localization/edit.php or directly into the XML file localization/texts.xml.  Use the script tests/langtester.php to check your translations for duplicates and missing translations.

## Directory Structure 

```
docs
src
  SmartFactory
    Interfaces
    DatabaseWorkers
```

## Detailed description

### docs
This directory contains the documentation about classes, interfaces and functions of the framework SmartFactory.

### src
This is the root directory for all classes and interfaces. The class loader is implemented based on PSR4 approach. You have no need to add additional class loader function for your classes.

### src/SmartFactory
This directory contains the core classes and interfaces of the framework SmartFactory.

### src/SmartFactory/Interfaces
This directory contains the core interfaces of the framework SmartFactory.

### src/SmartFactory/DatabaseWorkers
This directory contains the core classes of the framework SmartFactory for working with databases.
