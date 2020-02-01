
CREATE TABLE notes
(
    id              INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title           TEXT                 NOT NULL DEFAULT (''),
    body            TEXT                 NOT NULL DEFAULT (''),
    creator         TEXT                 NOT NULL DEFAULT (''),
    create_ts       DATETIME             NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    updater         TEXT                 NOT NULL DEFAULT (''),
    update_ts       DATETIME             DEFAULT NULL

);


CREATE INDEX idx_title ON notes
(
    title COLLATE NOCASE ASC
);


CREATE INDEX idx_body ON notes
(
    body COLLATE NOCASE
);
