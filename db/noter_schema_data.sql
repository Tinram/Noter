/*
	Create noter.sqlite3 database using the following commands:

		sudo sqlite3 noter.sqlite3
		.read noter_schema_data.sql
		.exit
*/


-- Table: notes
CREATE TABLE notes (

	id					INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	title				CHAR(30)					NOT NULL DEFAULT (''),
	body				VARCHAR(512)			NOT NULL DEFAULT (''),
	creator			CHAR(16)					NOT NULL DEFAULT (''),
	create_ts		DATETIME					NOT NULL DEFAULT (DATETIME('now', 'localtime')),
	updater			CHAR(16)					NOT NULL DEFAULT (''),
	update_ts		DATETIME					DEFAULT NULL

);


INSERT INTO [notes] ([id], [title], [body], [creator], [create_ts]) VALUES (1, 'test', 'This is a test note.', 'system', DATETIME('now', 'localtime'));
INSERT INTO [notes] ([id], [title], [body], [creator], [create_ts]) VALUES (2, 'code snippet', '<pre>mcrypt_create_iv($iLength, MCRYPT_DEV_URANDOM);</pre>', 'system', DATETIME('now', 'localtime'));
INSERT INTO [notes] ([id], [title], [body], [creator], [create_ts]) VALUES (3, 'Unicode test', '▢▣▤▥▦▧▨▩▪▫▬▭▮▯▰▱▲△▴▵▶▷▸▹►▻▼▽▾▿◀◁◂◃◄◅◆◇◈◉◊○◌◍◎●◐◑◒◓◔◕◖◗◘◙◚◛◜◝◞◟◠◡ ◢◣◤◥◦◧◨◩◪◫◬◭◮◯◰◱◲◳◴◵◶◷◸◹◺◻◼◽◾◿☀☁☂☃☄★☆☇☈☉☊☋☌☍☎☏☐☑☒☓☔☕☖☗☘ ☙☚☛☜☝☞☟☠☡☢☣☤☥☦☧☨☩☪☫☬☭☮☯☸☹☺☻☼☽☾☿♀♁♂♃♄♅♆♇♈♉♊♋♌♍♎♏♐♑♒♓♔♕♖♗ ♘♙♚♛♜♝♞♟♠♡♢♣♤♥♦♧♨♩♪♫♬♭♮♯♰♱♲♳♴♵♶♷♸♹♺♻♼♽♾♿⚀⚁⚂⚃⚄⚅⚆⚇⚈⚉⚐⚑⚒⚓⚔⚕⚖ ⚗⚘⚙⚚⚛⚜⚝⚠⚡⚢⚣⚤⚥⚦⚧⚨⚩⚪⚫⚬⚭⚮⚯⚰⚱⚲⚳⚴⚵⚶⚷⚸⚹⚺⚻⚼⛀⛁⛂⛃✁✂✃✄✆✇✈✉✌✍✎✏✐', 'system', DATETIME('now', 'localtime'));


-- Index: idx_title
CREATE INDEX idx_title ON notes (
	title COLLATE NOCASE ASC
);


-- Index: idx_body
CREATE INDEX idx_body ON notes (
	body COLLATE NOCASE
);
