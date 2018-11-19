CREATE TABLE b_thurlycloud_option
(
	ID INT(11) NOT NULL auto_increment,
	NAME VARCHAR(50) NOT NULL,
	SORT INT(11) NOT NULL,
	PARAM_KEY VARCHAR(50),
	PARAM_VALUE VARCHAR(200),
	PRIMARY KEY pk_b_thurlycloud_option(ID)
);
CREATE INDEX ix_b_thurlycloud_option_1 on b_thurlycloud_option(NAME);
