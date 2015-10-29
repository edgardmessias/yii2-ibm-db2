DROP TABLE IF EXISTS composite_fk CASCADE;
DROP TABLE IF EXISTS order_item CASCADE;
DROP TABLE IF EXISTS item CASCADE;
DROP TABLE IF EXISTS order_item_with_null_fk CASCADE;
DROP TABLE IF EXISTS order CASCADE;
DROP TABLE IF EXISTS order_with_null_fk CASCADE;
DROP TABLE IF EXISTS category CASCADE;
DROP TABLE IF EXISTS customer CASCADE;
DROP TABLE IF EXISTS profile CASCADE;
DROP TABLE IF EXISTS type CASCADE;
DROP TABLE IF EXISTS null_values CASCADE;
DROP TABLE IF EXISTS constraints CASCADE;
DROP TABLE IF EXISTS bool_values CASCADE;
DROP TABLE IF EXISTS animal CASCADE;
DROP TABLE IF EXISTS default_pk CASCADE;
DROP TABLE IF EXISTS document CASCADE;
DROP VIEW IF EXISTS animal_view;

CREATE TABLE constraints
(
  id integer not null,
  field1 varchar(255)
);

CREATE TABLE profile (
  id serial not null primary key,
  description varchar(128) NOT NULL
);

CREATE TABLE customer (
  id serial not null primary key,
  email varchar(128) NOT NULL,
  name varchar(128),
  address text,
  status integer DEFAULT 0,
  bool_status boolean DEFAULT 'f',
  profile_id integer
);

CREATE TABLE category (
  id serial not null primary key,
  name varchar(128) NOT NULL
);

CREATE TABLE item (
  id serial not null primary key,
  name varchar(128) NOT NULL,
  category_id integer NOT NULL references category(id) on DELETE CASCADE
);

CREATE TABLE order (
  id serial not null primary key,
  customer_id integer NOT NULL references customer(id) on DELETE CASCADE,
  created_at integer NOT NULL,
  total decimal(10,0) NOT NULL
);

CREATE TABLE order_with_null_fk (
  id serial not null primary key,
  customer_id integer,
  created_at integer NOT NULL,
  total decimal(10,0) NOT NULL
);

CREATE TABLE order_item (
  order_id integer NOT NULL references order(id) on DELETE CASCADE,
  item_id integer NOT NULL references item(id) on DELETE CASCADE,
  quantity integer NOT NULL,
  subtotal decimal(10,0) NOT NULL,
  PRIMARY KEY (order_id,item_id)
);

CREATE TABLE order_item_with_null_fk (
  order_id integer,
  item_id integer,
  quantity integer NOT NULL,
  subtotal decimal(10,0) NOT NULL
);

CREATE TABLE composite_fk (
  id integer NOT NULL,
  order_id integer NOT NULL,
  item_id integer NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (order_id, item_id) REFERENCES order_item (order_id, item_id) ON DELETE CASCADE CONSTRAINT FK_composite_fk_order_item
);

CREATE TABLE null_values (
  id serial NOT NULL,
  var1 integer,
  var2 integer,
  var3 integer DEFAULT NULL,
  stringcol VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE type (
  int_col integer NOT NULL,
  int_col2 integer DEFAULT 1,
  smallint_col smallint DEFAULT 1,
  char_col char(100) NOT NULL,
  char_col2 varchar(100) DEFAULT 'something',
  char_col3 text,
  float_col double precision NOT NULL,
  float_col2 double precision DEFAULT 1.23,
  blob_col clob,
  numeric_col decimal(5,2) DEFAULT 33.22,
  time DATETIME YEAR TO SECOND DEFAULT DATETIME(2002-01-01 00:00:00) YEAR TO SECOND NOT NULL,
  bool_col boolean NOT NULL,
  bool_col2 boolean DEFAULT 't',
  bool_col3 boolean DEFAULT 'f',
  ts_default DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND NOT NULL,
  bit_col SMALLINT DEFAULT 130 NOT NULL
);

CREATE TABLE bool_values (
  id serial not null primary key,
  bool_col boolean,
  default_true boolean default 't' not null,
  default_false boolean  default 'f' not null
);

CREATE TABLE animal (
  id serial primary key,
  type varchar(255) not null
);

CREATE TABLE default_pk (
  id integer default 5 not null primary key,
  type varchar(255) not null
);

CREATE TABLE document (
  id serial primary key,
  title varchar(255) not null,
  content text not null,
  version integer default 0 not null
);

CREATE VIEW animal_view AS SELECT * FROM animal;

INSERT INTO animal (type) VALUES ('yiiunit\data\ar\Cat');
INSERT INTO animal (type) VALUES ('yiiunit\data\ar\Dog');


INSERT INTO profile (description) VALUES ('profile customer 1');
INSERT INTO profile (description) VALUES ('profile customer 3');

INSERT INTO customer (email, name, address, status, bool_status, profile_id) VALUES ('user1@example.com', 'user1', 'address1', 1, 't', 1);
INSERT INTO customer (email, name, address, status, bool_status) VALUES ('user2@example.com', 'user2', 'address2', 1, 't');
INSERT INTO customer (email, name, address, status, bool_status, profile_id) VALUES ('user3@example.com', 'user3', 'address3', 2, 'f', 2);

INSERT INTO category (name) VALUES ('Books');
INSERT INTO category (name) VALUES ('Movies');

INSERT INTO item (name, category_id) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO item (name, category_id) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO item (name, category_id) VALUES ('Ice Age', 2);
INSERT INTO item (name, category_id) VALUES ('Toy Story', 2);
INSERT INTO item (name, category_id) VALUES ('Cars', 2);

INSERT INTO order (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO order (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO order (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO order_with_null_fk (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO order_with_null_fk (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO order_with_null_fk (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO order_item (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO order_item_with_null_fk (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

INSERT INTO document (title, content, version) VALUES ('Yii 2.0 guide', 'This is Yii 2.0 guide', 0);

/**
 * (Postgres-)Database Schema for validator tests
 */

DROP TABLE validator_main CASCADE;
DROP TABLE validator_ref CASCADE;

CREATE TABLE validator_main (
  id integer not null primary key,
  field1 VARCHAR(255)
);

CREATE TABLE validator_ref (
  id integer not null primary key,
  a_field VARCHAR(255),
  ref     integer
);

INSERT INTO validator_main (id, field1) VALUES (1, 'just a string1');
INSERT INTO validator_main (id, field1) VALUES (2, 'just a string2');
INSERT INTO validator_main (id, field1) VALUES (3, 'just a string3');
INSERT INTO validator_main (id, field1) VALUES (4, 'just a string4');
INSERT INTO validator_ref (id, a_field, ref) VALUES (1, 'ref_to_2', 2);
INSERT INTO validator_ref (id, a_field, ref) VALUES (2, 'ref_to_2', 2);
INSERT INTO validator_ref (id, a_field, ref) VALUES (3, 'ref_to_3', 3);
INSERT INTO validator_ref (id, a_field, ref) VALUES (4, 'ref_to_4', 4);
INSERT INTO validator_ref (id, a_field, ref) VALUES (5, 'ref_to_4', 4);
INSERT INTO validator_ref (id, a_field, ref) VALUES (6, 'ref_to_5', 5);

/* bit test, see https://github.com/yiisoft/yii2/issues/9006 */

DROP TABLE bit_values CASCADE;

CREATE TABLE bit_values (
  id serial not null primary key,
  val smallint not null
);

INSERT INTO bit_values (id, val) VALUES (1, 0);
INSERT INTO bit_values (id, val) VALUES (2, 1);
