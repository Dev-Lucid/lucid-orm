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

In order to perform any kind of query, you must instantiate a model. Here's a basic table:
```sql
create table users (
	user_id integer primary key,
	first_name varchar(50),
	last_name varchar(50)
);
insert into users (first_name,last_name) values ('Alice','Anderson');
insert into users (first_name,last_name) values ('Bob','Bjornson');
```

Once you've generated a model for this table, one can query for a list of users like this:
```php
$users = $db->users()->select();
foreach($users as $user)
{
	echo($user['first_name'].' '.$user['last_name'].'<br />');
}
```
