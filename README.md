#lucid-orm
=========

## Features
* Automatically generate models for you
* Instantiated models let you perform CRUD operations easily
* Figures out how to join tables for you

## Usage
Starting up lucid-orm requires 1 include, and one function call. It might look something like this:
```php
include(__DIR__.'/../lib/lucid-orm/lib/php/lucid_orm.php');
$db = lucid_orm::init(array(
	'type'=>'mysql',
	'hostname'=>'127.0.0.1',
	'username'=>'myuser',
	'password=>'mypassword',
	'model_path'=>__DIR__.'/models/',
));
```
The variable $db now contains an instance of one of the lucid_db_adaptors_* classes, currently either mysql, pgsql, or sqlite. These classes provide some helper functions to abstract the underlying database, and a __call magic method for instantiating models. This is most of what you'll likely be using the adaptor class for. Note that you can store the adaptor object into a variable named just about anything. $db will be used for the remainder for the examples.

In order to perform any kind of query, you must first instantiate a model. Here's a basic table:
```sql
create table users (
	user_id integer primary key,
	first_name varchar(50),
	last_name varchar(50)
);
insert into users (first_name,last_name) values ('Alice','Anderson');
insert into users (first_name,last_name) values ('Bob','Bjornson');
```
Once you've generated a model for this table, one can instantiate a model like this:
```php
$users = $db->users();
```
You didn't need to define a function called 'users' anywhere, simply using a table name as a function will use the __call function in the adaptor class, and that will properly include the model class and return an instance. You can do a lot with this object. For example, you can:
* Use methods like ->filter() and ->sort() to prepare a query (note: most methods like this are chainable, so feel free to do things like ->filter()->limit()->sort()->select()), and then call ->select(), ->one(), or ->first() to run the query. After that, you can loop through the results and do things with each row.
* Start setting fields in the model using array-like syntax. After you've set any values you want, just call ->save() and a new row would be inserted
* Use the ->delete() method to delete a specific row in the table.

So, let's look at a more detailed example on how to read data from the table. Building on the examples before:
```php
$db = lucid_orm::init(array(
	'type'=>'mysql',
	'hostname'=>'127.0.0.1',
	'username'=>'myuser',
	'password=>'mypassword',
	'model_path'=>__DIR__.'/models/',
));
$users = $db->users();
$users->select();
foreach($users as $user)
{
	echo($user['first_name'].' '.$user['last_name'].'<br />');
}
```
And you get a list of users! Maybe you know which user you want to load:
```php
$user = $db->users()->one($_REQUEST['user_id']);
echo('you loaded '.$user['first_name'].' '.$user['last_name'].'<br />');
```