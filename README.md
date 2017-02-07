##About

This is a deserializer written in PHP. It takes an expression (in string format) as an argument, converts that expression into a native PHP object (a boolean, a string, or an array), and stores that object in a variable. The output is a var_dump of that variable. See the "Examples" section for example expressions to try out.

##Setup

THIS SCRIPT WAS DEBUGGED AND RUN ON A UBUNTU MACHINE WITH PHP CLI INSTALLED:
$ apt-get install php7.0-cli

##Usage

$ php deserializer.php "<expression>"

##Examples

$ php deserializer.php "true"

$ php deserializer.php "'example string'"

$ php deserializer.php "['example', 'array']"

$ php datto_test_main.php "['true', [true, 'something'], 'blah blah', [['blah'], true], false]"

##NOTES

The meat of this code belongs in src/KeithParser.php.
