create table roles (
	role_id integer primary key,
	name varchar(50)
);

insert into roles (role_id,name) values (1,'admin');
insert into roles (role_id,name) values (2,'customer');

create table organizations(
	org_id integer primary key autoincrement,
	role_id integer,
	name varchar(255),
	creation_date timestamp default CURRENT_TIMESTAMP
);

insert into organizations (role_id,name) values (1,'Admin');
insert into organizations (role_id,name) values (2,'Customer 1');
insert into organizations (role_id,name) values (2,'Customer two');

create table users (
	user_id integer primary key autoincrement,
	org_id integer,
	email varchar(255),
	password varchar(255),
	first_name varchar(50),
	last_name varchar(50),
	creation_date timestamp DEFAULT CURRENT_TIMESTAMP
);

insert into users (org_id,email,password,first_name,last_name) values 
	(1,'admin@lucid-orm-testing.net','password','admin','admin');
insert into users (org_id,email,password,first_name,last_name) values 
	(2,'testing1@lucid-orm.org','mypassword1','testaccount','number1');
insert into users (org_id,email,password,first_name,last_name) values 	
	(3,'testing2@lucid-ftw.com','passwordmy2','accountfortesting','numerodos');