<?php
//
// MySQL queries
//

//
// CREATE Table
//
define("QUERY_CREATE_TABLE_AtomElement", "
CREATE TABLE IF NOT EXISTS AtomElement (
	eid binary(16) primary key,
	contents text character set utf8,
	createDate datetime,
	lastModifiedDate timestamp
)
");

define("QUERY_CREATE_TABLE_RelationElement", "
CREATE TABLE IF NOT EXISTS RelationElement (
	eid binary(16) primary key,
	relid binary(16) not null,
	e0id binary(16) not null,
	e1id binary(16) not null,
	createDate datetime,
	lastModifiedDate timestamp
)
");

/*
define("QUERY_CREATE_TABLE_Question", "
CREATE TABLE IF NOT EXISTS Question (
	qid binary(16) primary key,
	description text character set utf8,
	createDate datetime,
	lastModifiedDate timestamp,
	questionHTML text character set utf8
)
");

define("QUERY_CREATE_TABLE_Answer", "
CREATE TABLE IF NOT EXISTS Answer (
	qid binary(16) not null,
	uid binary(16) not null,
	keyName char(64) character set utf8,
	value text character set utf8,
	unique(qid, uid, keyName)
)
");

define("QUERY_CREATE_TABLE_User", "
CREATE TABLE IF NOT EXISTS User (
	uid binary(16) not null,
	class char(16) character set utf8,
	number int,
	name text character set utf8,
	primary key(class, number)
)
");

define("QUERY_CREATE_TABLE_Class", "
CREATE TABLE IF NOT EXISTS Class (
	classname char(64) character set utf8 unique,
	cid binary(16) not null,
	primary key(cid)
)
");

define("QUERY_CREATE_TABLE_ClassMember", "
CREATE TABLE IF NOT EXISTS ClassMember (
	uid binary(16) not null,
	cid binary(16) not null,
	primary key(uid, cid)
)
");
*/
//
// SELECT
//

define("QUERY_SELECT_ALL_AtomElement", "
	select hex(eid), contents, createDate, lastModifiedDate from AtomElement
");
/*
define("QUERY_SELECT_ALL_Question", "
	select hex(qid), createDate, lastModifiedDate, questionHTML, description from Question order by UNIX_TIMESTAMP(lastModifiedDate) DESC
");
define("QUERY_SELECT_ALL_Class", "
	select classname, hex(cid) from Class
");
*/
/*
define("QUERY_SELECT_Question_LATEST", "
	SELECT hex(qid), description, createDate, lastModifiedDate, questionHTML
	FROM Question 
	ORDER BY createDate DESC LIMIT 1
");
*/
//

define("QUERY_SELECT_AtomElement_eid", "
	SELECT contents 
	from AtomElement 
	WHERE eid=unhex(replace(?, '-', ''))
");
define("QUERY_SELECT_AtomElement_eid_TYPES", "s");
/*
//
define("QUERY_SELECT_User_info_BY_uid", "
	SELECT class, number, name 
	from User 
	WHERE uid=unhex(replace(?, '-', ''))
");
define("QUERY_SELECT_User_info_BY_uid_TYPES", "s");
//
define("QUERY_SELECT_Question_data_BY_qid", "
	SELECT questionHTML, description 
	from Question 
	WHERE qid=unhex(replace(?, '-', ''))");
define("QUERY_SELECT_Question_data_BY_qid_TYPES", "s");
//
define("QUERY_SELECT_Answer_BY_qid", "
	SELECT hex(uid), keyName, value 
	from Answer 
	WHERE qid=unhex(replace(?, '-', '')) 
	ORDER BY uid
");
define("QUERY_SELECT_Answer_BY_qid_TYPES", "s");
//
define("QUERY_SELECT_Answer_keyName_BY_qid", "
	SELECT DISTINCT keyName 
	from Answer 
	WHERE qid=unhex(replace(?, '-', ''))
");
define("QUERY_SELECT_Answer_keyName_BY_qid_TYPES", "s");
//
define("QUERY_SELECT_Answer_BY_qid_AND_uid", "
	SELECT keyName 
	from Answer 
	WHERE qid=unhex(replace(?, '-', '')) AND uid=unhex(replace(?, '-', ''))
");
define("QUERY_SELECT_Answer_BY_qid_AND_uid_TYPES", "ss");
//
define("QUERY_SELECT_qid_NotAnsweredBy_uid", "
	SELECT HEX(qid), createDate, lastModifiedDate, questionHTML, description 
	FROM Question 
	WHERE qid NOT IN (
		SELECT DISTINCT qid 
		FROM Answer 
		WHERE uid=UNHEX(replace(?, '-', ''))
	) 
	ORDER BY createDate DESC
");
define("QUERY_SELECT_qid_NotAnsweredBy_uid_TYPES", "s");
//
define("QUERY_SELECT_Class_BY_classname", "
	SELECT hex(cid) 
	from Class 
	WHERE classname=?
");
define("QUERY_SELECT_Class_BY_classname_TYPES", "s");
//
define("QUERY_SELECT_User_info_BY_cid", "
	SELECT hex(uid), class, number, name 
	from User 
	WHERE uid IN (
		SELECT uid 
		FROM ClassMember 
		WHERE cid=UNHEX(replace(?, '-', ''))
	)
");
define("QUERY_SELECT_User_info_BY_cid_TYPES", "s");
*/
//
// INSERT
//
define("QUERY_ADD_AtomElement", "
insert into AtomElement (
	eid, contents, createDate
) values (
	unhex(replace(?, '-', '')), ?, now()
)
");
define("QUERY_ADD_AtomElement_TYPES", "ss");
/*
//
define("QUERY_ADD_Question", "
insert into Question (
	qid, createDate, description, questionHTML
) values (
	unhex(replace(uuid(), '-', '')), now(), ?, ?
)
");
define("QUERY_ADD_Question_TYPES", "ss");
//
define("QUERY_ADD_Answer", "
insert into Answer (
	qid, uid, keyName, value
) values (
	unhex(replace(?, '-', '')), unhex(replace(?, '-', '')), ?, ?
)
");
define("QUERY_ADD_Answer_TYPES", "ssss");
//
define("QUERY_ADD_Class", "
insert into Class (
	classname, cid
) values (
	?, unhex(replace(uuid(), '-', ''))
)
");
define("QUERY_ADD_Class_TYPES", "s");
//
define("QUERY_ADD_ClassMember", "
insert into ClassMember (
	uid, cid
) values (
	unhex(replace(?, '-', '')), unhex(replace(?, '-', ''))
)
");
define("QUERY_ADD_ClassMember_TYPES", "ss");

//
// UPDATE
//
define("QUERY_UPDATE_Question", "
UPDATE Question SET
	description=?, questionHTML=?
WHERE
	qid=unhex(replace(?, '-', ''))
");

define("QUERY_UPDATE_Question_TYPES", "sss");

//
// DELETE
//
define("QUERY_DELETE_User_BY_uid", "DELETE FROM User WHERE uid=unhex(replace(?, '-', ''))");
define("QUERY_DELETE_User_BY_uid_TYPES", "s");
//
define("QUERY_DELETE_Question_BY_qid", "DELETE FROM Question WHERE qid=unhex(replace(?, '-', ''))");
define("QUERY_DELETE_Question_BY_qid_TYPES", "s");
//
define("QUERY_DELETE_Class_BY_cid", "DELETE FROM Class WHERE cid=unhex(replace(?, '-', ''))");
define("QUERY_DELETE_Class_BY_cid_TYPES", "s");
//
define("QUERY_DELETE_ClassMember_BY_cid", "DELETE FROM Class WHERE cid=unhex(replace(?, '-', ''))");
define("QUERY_DELETE_ClassMember_BY_cid_TYPES", "s");
*/
?>