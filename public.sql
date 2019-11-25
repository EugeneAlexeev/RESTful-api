/*
Navicat PGSQL Data Transfer

Source Server         : localhost_postgresql
Source Server Version : 90424
Source Host           : localhost:5432
Source Database       : postgres
Source Schema         : public

Target Server Type    : PGSQL
Target Server Version : 90424
File Encoding         : 65001

Date: 2019-11-25 09:31:55
*/


-- ----------------------------
-- Sequence structure for "public"."todos_todo_id_seq"
-- ----------------------------
DROP SEQUENCE "public"."todos_todo_id_seq";
CREATE SEQUENCE "public"."todos_todo_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 4
 CACHE 1;

-- ----------------------------
-- Sequence structure for "public"."users_user_id_seq"
-- ----------------------------
DROP SEQUENCE "public"."users_user_id_seq";
CREATE SEQUENCE "public"."users_user_id_seq"
 INCREMENT 1
 MINVALUE 1
 MAXVALUE 9223372036854775807
 START 9
 CACHE 1;

-- ----------------------------
-- Table structure for "public"."todos"
-- ----------------------------
DROP TABLE "public"."todos";
CREATE TABLE "public"."todos" (
"todo_id" int4 DEFAULT nextval('todos_todo_id_seq'::regclass) NOT NULL,
"title" varchar(255) NOT NULL,
"desc" text NOT NULL,
"status" varchar(32) NOT NULL,
"user_id" int4 NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Records of todos
-- ----------------------------
INSERT INTO "public"."todos" VALUES ('1', 'Task number 1!', 'abc', '1', '9');
INSERT INTO "public"."todos" VALUES ('2', 'Task number 12!', 'bcd', '1', '9');
INSERT INTO "public"."todos" VALUES ('4', 'Task number 12!', 'abc', '1', '9');

-- ----------------------------
-- Table structure for "public"."users"
-- ----------------------------
DROP TABLE "public"."users";
CREATE TABLE "public"."users" (
"user_id" int4 DEFAULT nextval('users_user_id_seq'::regclass) NOT NULL,
"login" varchar(16) NOT NULL,
"hash" varchar(32) NOT NULL,
"salt" varchar(5) NOT NULL
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO "public"."users" VALUES ('9', 'eugene', 'c7903952f20c851816cb5b6da731908a', 'q`i~a');

-- ----------------------------
-- Alter Sequences Owned By 
-- ----------------------------
ALTER SEQUENCE "public"."todos_todo_id_seq" OWNED BY "todos"."todo_id";
ALTER SEQUENCE "public"."users_user_id_seq" OWNED BY "users"."user_id";

-- ----------------------------
-- Indexes structure for table todos
-- ----------------------------
CREATE INDEX "todos_desc_idx" ON "public"."todos" USING btree ("desc");
CREATE INDEX "todos_title_idx" ON "public"."todos" USING btree ("title");

-- ----------------------------
-- Primary Key structure for table "public"."todos"
-- ----------------------------
ALTER TABLE "public"."todos" ADD PRIMARY KEY ("todo_id");

-- ----------------------------
-- Indexes structure for table users
-- ----------------------------
CREATE UNIQUE INDEX "users_login_idx" ON "public"."users" USING btree ("login");

-- ----------------------------
-- Primary Key structure for table "public"."users"
-- ----------------------------
ALTER TABLE "public"."users" ADD PRIMARY KEY ("user_id");

-- ----------------------------
-- Foreign Key structure for table "public"."todos"
-- ----------------------------
ALTER TABLE "public"."todos" ADD FOREIGN KEY ("user_id") REFERENCES "public"."users" ("user_id") ON DELETE NO ACTION ON UPDATE CASCADE;
