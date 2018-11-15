## SmartFactory demo application

Demo application to demonstrate how to use the library [SmartFactory](https://github.com/oschildt/SmartFactory).

### To get familiar with the SmartFactory do the following

- Git-clone the demo application and run 'composer update'.
- Use the script database/create_database_mysql.cmd (create_database_mysql.cmd) to create a demo database necessary for some examples.
- View and study the API documentation in the folder docs or here [API documentation](http://php-smart-factory.org/docs/).
- Study the core code of the library SmartFactory.

### To start writing own application using SmartFactory

1. Git-clone the demo application and run 'composer update'.

2. Study the directory structure of the demo application and the code.

3. Implement your classes and functions. Use the script tests/classtester.php to check your classes for correct syntax.

4. Bind you classes to the interfaces in the file factory_init_inc.php to be able to use the IoC approach for creating objects offered by the library SmartFactory.

5. Implement you business logic in the root directory or any subdirectory. 

7. Implement the API request handles for JSON or XML if necessary.

8. Add translation texts for your application over the localization/edit.php or directly into the XML file localization/texts.xml.  Use the script tests/langtester.php to check your translations for duplicates and missing translations.

## Directory Structure 

```
application
  api
  config
  css
  localization
  logs
  resources
  src
  tests
  xmlapi
database
```

## Detailed description

### application
This is the root directory of the application.

### application/api
This directory contains the processor index.php of the JSON API requests.

### application/config
This directory contains the configuration files.

### application/css
This directory contains the css files for the application.

### application/localization
This directory contains the translation file texts.xml and the editor edit.php for user friendly editing of the translation texts.

### application/logs
This directory is used for logging, debugging and tracing.

### application/resources
This directory contains the resource files for the application.

### application/src
This is the root directory for all code sources. 

### application/tests
This directory contains the test scripts for checking the your classes for correct syntax and for checking your translations for duplicates and missing translations.

### application/xmlapi
This directory contains the processor index.php of the XML API requests.

### database
This directory contains the SQL scripts for creation of the database for the demo application.





