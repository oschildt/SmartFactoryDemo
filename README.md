## SmartFactory demo application

Demo application to demonstrate how to use the library [SmartFactory](https://github.com/oschildt/SmartFactory) and the [SmartFactory OAuth2 Server](https://github.com/oschildt/SmartFactoryOAuth2).

### To get familiar with the SmartFactory do the following

1. Git-clone the demo application and run 'composer update'.
2. Use the script *database/create_database_mysql.sql* (*create_database_mysql.sql*) to create a demo database necessary for some examples.
3. View and study the API documentation in the folder docs or here [API documentation](http://php-smart-factory.org/smartfactory/) and
[OAuth2 API documentation](http://php-smart-factory.org/oauth2/).
4. Study the core code of the library SmartFactory.

### To start writing own application using SmartFactory

1. Git-clone the demo application and run 'composer update'.

2. Study the directory structure of the demo application and the code.

3. Implement your classes and functions.

4. Bind you classes to the interfaces in the file *initialization_inc.php* to be able to use the IoC approach for creating objects offered by the library SmartFactory.

5. Implement you business logic in the root directory or any subdirectory. 

7. Implement the API request handlers for JSON or XML requests if necessary.

8. Add translation texts for your application over the *localization/edit.php* or directly into the XML file *localization/texts.json*. Use the script *localization/check.php* to check your localization texts for missing translations.

## Directory Structure 

```
config
logs
localization
src
tests
database
application
  api
  xmlapi
```

## Detailed description

### config
This directory contains the configuration files. This folder is outside of the access per http(s).

### logs
This directory is used for logging, debugging and tracing. This folder is outside of the access per http(s).

### localization
This directory contains the translation file *texts.json* and the editor *edit.php* for user friendly editing of the translation texts, and the file *check.php* for checking the localization texts for missing translations.

### src
This is the root directory for all code sources. 

### tests
This directory contains the test units.

### database
This directory contains the SQL scripts for creation of the database for the demo application.

### application
This is the root directory of the application.

### application/api
This directory contains the processor *index.php* of the JSON API requests.

### application/xmlapi
This directory contains the processor *index.php* of the XML API requests.





