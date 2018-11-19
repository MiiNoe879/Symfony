CREATE TABLE b_imopenlines_session
(
	ID int(11) NOT NULL auto_increment,
	MODE varchar(255) NULL default 'input',
	STATUS tinyint(3) NULL default '0',
	SOURCE varchar(255) NULL,
	CONFIG_ID int(11) NULL,
	OPERATOR_ID int(11) NULL,
	USER_CODE varchar(255) NULL,
	PARENT_ID int(11) null DEFAULT 0,
	USER_ID int(11) NOT NULL,
	CHAT_ID int(11) NOT NULL,
	MESSAGE_COUNT int(11) NOT NULL,
	LIKE_COUNT int(11) NULL,
	START_ID int(11) NOT NULL,
	END_ID int(11) NOT NULL,
	CRM char(1) not null default 'N',
	CRM_CREATE char(1) not null default 'N',
	CRM_ENTITY_TYPE varchar(50) NULL,
	CRM_ENTITY_ID int(11) NULL,
	CRM_ACTIVITY_ID int(11) NULL,
	DATE_CREATE datetime,
	DATE_OPERATOR datetime,
	DATE_MODIFY datetime,
	DATE_OPERATOR_ANSWER datetime,
	DATE_OPERATOR_CLOSE datetime,
	DATE_FIRST_ANSWER datetime,
	DATE_LAST_MESSAGE datetime,
	DATE_CLOSE datetime,
	TIME_FIRST_ANSWER int(11) null,
	TIME_ANSWER int(11) null,
	TIME_CLOSE int(11) null,
	TIME_BOT int(11) null,
	TIME_DIALOG int(11) null,
	CATEGORY_ID int(11) null DEFAULT 0,
	WAIT_ACTION char(1) not null default 'N',
	WAIT_ANSWER char(1) not null default 'Y',
	WAIT_VOTE char(1) not null default 'N',
	SEND_FORM varchar(255) NULL default 'none',
	SEND_HISTORY char(1) not null default 'N',
	CLOSED char(1) not null default 'N',
	PAUSE char(1) not null default 'N',
	SPAM char(1) not null default 'N',
	WORKTIME char(1) not null default 'Y',
	QUEUE_HISTORY text null,
	VOTE int(11) NULL,
	VOTE_HEAD int(11) NULL,
	EXTRA_REGISTER int(11) NULL,
	EXTRA_TARIFF varchar(255) NULL,
	EXTRA_URL varchar(255) NULL,
	PRIMARY KEY (ID),
	KEY IX_IMOL_S_1 (USER_CODE, CLOSED),
	KEY IX_IMOL_S_2 (USER_ID, CLOSED),
	KEY IX_IMOL_S_3 (CHAT_ID, CLOSED),
	KEY IX_IMOL_S_4 (OPERATOR_ID, CLOSED, DATE_CREATE),
	KEY IX_IMOL_S_5 (OPERATOR_ID, CLOSED, DATE_MODIFY),
	KEY IX_IMOL_S_6 (EXTRA_URL, DATE_CREATE),
	KEY IX_IMOL_S_7 (EXTRA_TARIFF, DATE_CREATE),
	KEY IX_IMOL_S_8 (VOTE, VOTE_HEAD, DATE_CREATE),
	KEY IX_IMOL_S_9 (SPAM, DATE_CREATE),
	KEY IX_IMOL_S_10 (STATUS, DATE_CREATE)
);

CREATE TABLE b_imopenlines_session_index
(
	SESSION_ID int(11) not null,
	SEARCH_CONTENT mediumtext null,
	PRIMARY KEY (SESSION_ID)
);

CREATE TABLE b_imopenlines_user_relation
(
	USER_CODE varchar(255) NOT NULL,
	USER_ID int(11) NULL DEFAULT 0,
	CHAT_ID int(11) NULL DEFAULT 0,
	AGREES char(1) not null default 'N',
	PRIMARY KEY (USER_CODE)
);

CREATE TABLE b_imopenlines_session_check
(
	SESSION_ID int(11) NOT NULL,
	DATE_CLOSE datetime NULL,
	DATE_QUEUE datetime NULL,
	DATE_MAIL datetime NULL,
	PRIMARY KEY (SESSION_ID),
	KEY IX_IMOL_SCH_1 (DATE_CLOSE),
	KEY IX_IMOL_SCH_2 (DATE_QUEUE),
	KEY IX_IMOL_SCH_3 (DATE_MAIL)
);

CREATE TABLE b_imopenlines_livechat
(
	CONFIG_ID int(11) NOT NULL,
	URL_CODE varchar(255) NULL,
	URL_CODE_ID int(11) NULL,
	URL_CODE_PUBLIC varchar(255) NULL,
	URL_CODE_PUBLIC_ID int(11) NULL,
	TEMPLATE_ID varchar(255) DEFAULT NULL,
	BACKGROUND_IMAGE int(18) DEFAULT NULL,
	CSS_ACTIVE char(1) NOT NULL DEFAULT 'N',
 	CSS_PATH varchar(255) DEFAULT NULL,
 	CSS_TEXT longtext,
	COPYRIGHT_REMOVED char(1) NOT NULL DEFAULT 'N',
	CACHE_WIDGET_ID int(11) NULL,
	CACHE_BUTTON_ID int(11) NULL,
	PRIMARY KEY (CONFIG_ID)
);

CREATE TABLE b_imopenlines_config
(
	ID int(11) NOT NULL auto_increment,
	XML_ID varchar(255) NULL,
	ACTIVE char(1) not null default 'N',
	LINE_NAME varchar(255) NULL,
	CRM char(1) not null default 'Y',
	CRM_CREATE varchar(50) default 'none',
	CRM_FORWARD char(1) not null default 'Y',
	CRM_SOURCE varchar(50) default 'create',
	CRM_TRANSFER_CHANGE char(1) not null default 'Y',
	QUEUE_TIME int(11) DEFAULT 60,
	QUEUE_TYPE varchar(50) DEFAULT 'evenly',
	TIMEMAN char(1) not null default 'N',
	WELCOME_MESSAGE char(1) not null default 'Y',
	WELCOME_MESSAGE_TEXT text null,
	WELCOME_BOT_ENABLE char(1) null default 'N',
	WELCOME_BOT_JOIN varchar(50) DEFAULT 'first',
	WELCOME_BOT_ID int(11) DEFAULT 0,
	WELCOME_BOT_TIME int(11) DEFAULT 60,
	WELCOME_BOT_LEFT varchar(50) DEFAULT 'queue',
	AGREEMENT_MESSAGE char(1) not null default 'N',
	AGREEMENT_ID int(11) DEFAULT 0,
	NO_ANSWER_RULE varchar(50) DEFAULT 'form',
	NO_ANSWER_FORM_ID int(11),
	NO_ANSWER_BOT_ID int(11),
	NO_ANSWER_TEXT text null,
	WORKTIME_ENABLE char(1) null default 'N',
	WORKTIME_FROM varchar(5) null,
	WORKTIME_TO varchar(5) null,
	WORKTIME_TIMEZONE varchar(50) null,
	WORKTIME_HOLIDAYS varchar(2000) null,
	WORKTIME_DAYOFF varchar(20) null,
	WORKTIME_DAYOFF_RULE varchar(50) default 'form',
	WORKTIME_DAYOFF_FORM_ID int(11),
	WORKTIME_DAYOFF_BOT_ID int(11),
	WORKTIME_DAYOFF_TEXT text null,
	CATEGORY_ENABLE char(1) null default 'N',
	CATEGORY_ID int(11) null DEFAULT 0,
	CLOSE_RULE varchar(50) DEFAULT 'form',
	CLOSE_FORM_ID int(11),
	CLOSE_BOT_ID int(11),
	CLOSE_TEXT text null,
	AUTO_CLOSE_RULE varchar(50) DEFAULT 'none',
	AUTO_CLOSE_FORM_ID int(11),
	AUTO_CLOSE_BOT_ID int(11),
	AUTO_CLOSE_TIME int(11) DEFAULT 0,
	AUTO_CLOSE_TEXT text null,
	AUTO_EXPIRE_TIME int(11) DEFAULT 0,
	VOTE_MESSAGE char(1) not null default 'Y',
	VOTE_MESSAGE_1_TEXT text null,
	VOTE_MESSAGE_1_LIKE text null,
	VOTE_MESSAGE_1_DISLIKE text null,
	VOTE_MESSAGE_2_TEXT text null,
	VOTE_MESSAGE_2_LIKE text null,
	VOTE_MESSAGE_2_DISLIKE text null,
	DATE_CREATE datetime,
	DATE_MODIFY datetime,
	MODIFY_USER_ID int(11),
	TEMPORARY char(1) not null default 'Y',
	QUICK_ANSWERS_IBLOCK_ID int(11) null DEFAULT 0,
	SESSION_PRIORITY int(11) null DEFAULT 0,
	LANGUAGE_ID char(2) null,
	PRIMARY KEY PK_B_IMOPENLINES_CONFIG (ID)
);

CREATE TABLE b_imopenlines_config_statistic
(
	CONFIG_ID int(11) NOT NULL,
	SESSION int(11) NULL,
	MESSAGE int(11) NULL,
	CLOSED int(11) NULL,
	IN_WORK int(11) NULL,
	LEAD int(11) NULL,
	PRIMARY KEY PK_BB_IMOPENLINES_CONFIG_STATISTIC (CONFIG_ID)
);

CREATE TABLE b_imopenlines_config_category
(
	ID int(11) NOT NULL auto_increment,
	CONFIG_ID int(11) NOT NULL,
	CODE varchar(50) NULL,
	VALUE varchar(255) NULL,
	SORT int(11) NOT NULL,
	PRIMARY KEY PK_B_IMOPENLINES_CONFIG_CATEGORY (ID),
	KEY IX_IMOL_CC_1 (CODE),
	KEY IX_IMOL_CC_2 (CONFIG_ID, SORT DESC)
);

CREATE TABLE b_imopenlines_queue
(
	ID int(11) NOT NULL auto_increment,
	CONFIG_ID int(11) NOT NULL,
	USER_ID int(11) NOT NULL,
	LAST_ACTIVITY_DATE datetime,
	PRIMARY KEY PK_B_IMOPENLINES_QUEUE (ID),
	KEY IX_IMOL_Q_1 (CONFIG_ID),
	KEY IX_IMOL_Q_2 (CONFIG_ID, LAST_ACTIVITY_DATE),
	KEY IX_IMOL_Q_3 (USER_ID)
);

CREATE TABLE b_imopenlines_operator_transfer
(
	ID int(11) NOT NULL auto_increment,
	CONFIG_ID int(11) NOT NULL,
	SESSION_ID int(11) NOT NULL,
	USER_ID int(11) NOT NULL,
	TRANSFER_MODE varchar(50) default 'MANUAL',
	TRANSFER_TYPE varchar(50) default 'USER',
	TRANSFER_USER_ID int(11) NULL,
	TRANSFER_LINE_ID int(11) NULL,
	DATE_CREATE datetime,
	PRIMARY KEY PK_B_IMOPENLINES_OPERATOR_TRANSFER (ID),
	KEY IX_IMOL_OT_1 (CONFIG_ID),
	KEY IX_IMOL_OT_2 (SESSION_ID),
	KEY IX_IMOL_OT_3 (DATE_CREATE),
	KEY IX_IMOL_OT_4 (USER_ID, CONFIG_ID)
);

CREATE TABLE b_imopenlines_tracker
(
	ID int(11) NOT NULL auto_increment,
	SESSION_ID int(11) NULL,
	CHAT_ID int(11) NULL,
	MESSAGE_ID int(11) NULL,
	MESSAGE_ORIGIN_ID int(11) NULL,
	USER_ID int(11) NULL,
	ACTION varchar(50) NULL,
	CRM_ENTITY_TYPE varchar(50) NULL,
	CRM_ENTITY_ID int(11) NULL,
	FIELD_TYPE varchar(255) NULL,
	FIELD_VALUE varchar(255) NULL,
	DATE_CREATE datetime,
	PRIMARY KEY PK_B_IMOPENLINES_TRACKER (ID),
	KEY IX_IMOL_TR_1 (MESSAGE_ID)
);

CREATE TABLE b_imopenlines_role
(
	ID int(11) NOT NULL auto_increment,
	NAME varchar(255) NOT NULL,
	XML_ID varchar(255) NULL,
	PRIMARY KEY PK_B_IMOPENLINES_ROLE (ID),
	KEY IX_IMOL_PERM_XML_ID (XML_ID)
);

CREATE TABLE b_imopenlines_role_permission
(
	ID int(11) NOT NULL auto_increment,
	ROLE_ID int(11) NOT NULL,
	ENTITY varchar(50) NOT NULL,
	ACTION varchar(50) NOT NULL,
	PERMISSION char(1) NULL,
	PRIMARY KEY PK_B_IMOPENLINES_ROLE_PERMISSION (ID),
	KEY IX_IMOL_PERM_ROLE_ID (ROLE_ID)
);

CREATE TABLE b_imopenlines_role_access
(
	ID int(11) NOT NULL auto_increment,
	ROLE_ID int(11) NOT NULL,
	ACCESS_CODE varchar(100) NOT NULL,
	PRIMARY KEY PK_B_IMOPENLINES_ROLE_ACCESS (ID),
	KEY IX_IMOL_ACCESS_ROLE_ID (ROLE_ID)
);

CREATE TABLE b_imopenlines_rest_network_limit
(
	ID int(11) NOT NULL auto_increment,
	BOT_ID int(11) NOT NULL,
	USER_ID int(11) NOT NULL,
	DATE_CREATE datetime,
	PRIMARY KEY PK_B_IMOPENLINES_NETWORK_LIMIT (ID),
	KEY IX_IMOL_RNL_1 (BOT_ID, DATE_CREATE)
);