PRAGMA foreign_keys = on;

create table roles (
	role_id integer primary key,
	name varchar(50)
);
CREATE UNIQUE INDEX idx__roles__role_id ON roles (role_id);

insert into roles (role_id,name) values (1,'admin');
insert into roles (role_id,name) values (2,'customer');

create table organizations(
	org_id integer primary key autoincrement,
	role_id integer references roles(role_id),
	name varchar(255),
	creation_date timestamp default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX idx__organizations__org_id ON organizations (org_id);
CREATE INDEX idx__organizations__role_id ON organizations (role_id);

insert into organizations (role_id,name) values (1,'Admin');
insert into organizations (role_id,name) values (2,'Customer 1');
insert into organizations (role_id,name) values (2,'Customer two');

create table users (
	user_id integer primary key autoincrement,
	org_id integer references organizations(org_id),
	email varchar(255),
	password varchar(255),
	first_name varchar(50),
	last_name varchar(50),
	score numeric(10,2) default 0.0,
	is_deleted bool default false,
	creation_date timestamp DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX idx__users__user_id ON users (user_id);
CREATE INDEX idx__users__org_id ON users (org_id);

insert into users (org_id,email,password,first_name,last_name,creation_date) values 
	(1,'admin@lucid-orm-testing.net','password','admin','admin','2014-01-01 12:00:00');
insert into users (org_id,email,password,first_name,last_name,creation_date) values 
	(2,'testing1@lucid-orm.org','mypassword1','testaccount','number1','2014-02-01 12:00:00');
insert into users (org_id,email,password,first_name,last_name,creation_date) values 	
	(3,'testing2@lucid-ftw.com','passwordmy2','accountfortesting','numerodos','2014-03-01 12:00:00');