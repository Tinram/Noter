/*
	Create noter.sqlite3 database using the following commands:

		sudo sqlite3 noter.sqlite3
		.read noter_schema_data.sql
		.exit
*/


-- Table: notes
CREATE TABLE notes (

	id					INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	title				CHAR(20)					NOT NULL DEFAULT (''),
	body				VARCHAR(512)			NOT NULL DEFAULT (''),
	timestamp 	DATETIME					NOT NULL DEFAULT (DATETIME('now', 'localtime'))

);


INSERT INTO [notes] ([id], [title], [body], [timestamp]) VALUES (1, 'test', 'This is a test note.', '2016-08-12 11:59:50');
INSERT INTO [notes] ([id], [title], [body], [timestamp]) VALUES (2, 'code snippet #1', 'mcrypt_create_iv($iLength, MCRYPT_DEV_URANDOM);', '2016-08-12 12:00:48');
INSERT INTO [notes] ([id], [title], [body], [timestamp]) VALUES (3, 'Unicode test', '▢▣▤▥▦▧▨▩▪▫▬▭▮▯▰▱▲△▴▵▶▷▸▹►▻▼▽▾▿◀◁◂◃◄◅◆◇◈◉◊○◌◍◎●◐◑◒◓◔◕◖◗◘◙◚◛◜◝◞◟◠◡ ◢◣◤◥◦◧◨◩◪◫◬◭◮◯◰◱◲◳◴◵◶◷◸◹◺◻◼◽◾◿☀☁☂☃☄★☆☇☈☉☊☋☌☍☎☏☐☑☒☓☔☕☖☗☘ ☙☚☛☜☝☞☟☠☡☢☣☤☥☦☧☨☩☪☫☬☭☮☯☸☹☺☻☼☽☾☿♀♁♂♃♄♅♆♇♈♉♊♋♌♍♎♏♐♑♒♓♔♕♖♗ ♘♙♚♛♜♝♞♟♠♡♢♣♤♥♦♧♨♩♪♫♬♭♮♯♰♱♲♳♴♵♶♷♸♹♺♻♼♽♾♿⚀⚁⚂⚃⚄⚅⚆⚇⚈⚉⚐⚑⚒⚓⚔⚕⚖ ⚗⚘⚙⚚⚛⚜⚝⚠⚡⚢⚣⚤⚥⚦⚧⚨⚩⚪⚫⚬⚭⚮⚯⚰⚱⚲⚳⚴⚵⚶⚷⚸⚹⚺⚻⚼⛀⛁⛂⛃✁✂✃✄✆✇✈✉✌✍✎✏✐', '2016-08-12 21:48:28');


-- Index: idx_title
CREATE INDEX idx_title ON notes (
	title COLLATE NOCASE ASC
);


-- Index: idx_body
CREATE INDEX idx_body ON notes (
	body COLLATE NOCASE
);
