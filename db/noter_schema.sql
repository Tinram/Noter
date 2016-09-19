
CREATE TABLE notes (

	id					INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	title				CHAR(30)					NOT NULL DEFAULT (''),
	body				VARCHAR(512)			NOT NULL DEFAULT (''),
	creator			CHAR(16)					NOT NULL DEFAULT (''),
	create_ts		DATETIME					NOT NULL DEFAULT (DATETIME('now', 'localtime')),
	updater			CHAR(16)					NOT NULL DEFAULT (''),
	update_ts		DATETIME					DEFAULT NULL

);


CREATE INDEX idx_title ON notes ( 
	title COLLATE NOCASE ASC 
);


CREATE INDEX idx_body ON notes ( 
	body COLLATE NOCASE 
);
