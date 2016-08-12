
CREATE TABLE notes (

	id					INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	title				CHAR(20)					NOT NULL DEFAULT (''),
	body				VARCHAR(512)			NOT NULL DEFAULT (''),
	timestamp 	DATETIME					NOT NULL DEFAULT (DATETIME('now', 'localtime'))

);


CREATE INDEX idx_title ON notes ( 
	title COLLATE NOCASE ASC 
);


CREATE INDEX idx_body ON notes ( 
	body COLLATE NOCASE 
);
