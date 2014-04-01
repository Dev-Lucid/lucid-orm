#lucid-orm
=========

## Features
* Automatically generate models for you
* Instantiated models let you perform CRUD operations easily
* Figures out how to join tables for you

## Components
There are two main components in lucid-orm: the adaptor and the model. The adaptor contains a pdo object and provides a number of methods for abstracting the differences between different databases and utility methods for generating model files. The two main things you will use the adaptor for is instantiating models, and executing raw queries. 

The model has a number of different uses. It is used both to select data from your database, and to iterate over the resultset.

* [Querying](querying.md)
* [Database Design Recommendations](db_design.md)
* [Advanced Topics](advanced.md)