# parser_test

## About

I've created this is two broad sections. The index.php file which acts as a public interface to the application
itself, and an Application folder which contains the main class for the csv writer. I've also added some tests.
In the interests of sticking to the three hour time-limit, I haven't added a full suite of tests, but I wanted
to show that I have some knowledge of TDD and PHP unit testing.

The script is run through the index.php file, and takes a single argument - the directory to scan for log files
and will output a CSV with a date stamp to ensure that a historic record of runs is completed.

I wasn't sure, in the cotext of this test, what to do with the filtered tags, as I noted that you wanted to have
a record kept of them. I do break them down into an array for processing, so I did consider adding an extra field 
to the csv that held the original tag string, although that would have been out of specification.

I've based this broadly on the idea that this would be a one-off operation, so everything is built upon a single
class. Given more time, and had this been an operation that would have been worked into core functionality, I would
have made provision for that by breaking up the code into smaller, more self-contained classes, to deal with
the individual processes (directory scanning, csv writing, cli processing, etc) to create a more maintainable
and extensible codebase.