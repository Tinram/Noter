
# Noter

### Note sharing on an internal network


## Purpose

Share notes and code on a home / internal network without awkward PC connections or Internet transfer obstructing.

Quickly post and view notes on the same network e.g. save a note from a Linux PC and read on a Windows PC, without needing to invoke WinSCP or use network sharing such as Samba or NFS.

The focus is simplicity - browser display and search, with a log-in to add, edit, and delete notes.

The log-in offers basic protection from unauthorised tampering via password obfuscation and session timeout. Noter was created for an isolated network where plain text transfer and viewing is acceptable, and without determined adversaries. *Noter is **not** suitable for storing sensitive data.*

A single SQLite database file facilitates easy backup and / or source control of data.


## Requirements

A PHP 5.4+ server with the sqlite3 module enabled.


## Setup

Unzip or git clone the Noter files into the chosen directory inside a server's web directory.

For the following example on a Linux machine, the chosen directory is called *notes/*

(XAMPP and WAMP are suitable servers for Windows.)

`cd` to the web directory (usually */var/www/html/*).

(Prefix `sudo` to the following commands as necessary.)

`chown -R <username>:www-data notes/` # Debian-based, username as owner allows file editing

(`chown -R <username>:apache notes/`   # others)

`chown www-data notes/log/badlog.txt`

`chmod 600 notes/log/badlog.txt`

Very important for the SQLite file operation:

`chown -R www-data notes/db/`

Check the configuration file constants: *config/config.php*

Only the timezone, session constants, and `CONFIG_NUM_NOTES_DISPLAYED` are of immediate interest, unless you wish to revise the SQLite database schema.

Change the users and passwords in the editing log-in gateway (*classes/login.class.php*). The default usernames (`USER1` and `USER2`) are *martin* and *alison* and both passwords are *P@55w0rd*.  More users can be easily added (top of the file and in `validateForm()`).

Passwords are stored as SHA-256 hashes. `$USER1_PASS` etc can be replaced with a hash generated from a web service or by command-line scripting:

*PHP*

`php -r "echo hash('sha256', 'PASSWORD');"`

*Python 2 and 3*

`import hashlib;print(hashlib.sha256('PASSWORD'.encode()).hexdigest())`


## Viewing and Searching

*http://localhost/notes*

*http://<IP>/notes*

or better, configure an Apache vhost:

*notes*


## Adding and Editing

*http://localhost/notes/edit*

or if Apache rewrite is disabled:

*http://localhost/notes/edit.php*

Upon logging-in through this page, notes can be added, updated, or deleted.


## Other Files

The SQLite database schema is available at *db/schema.sql*

Unsuccessful log-in attempts are recorded in *log/badlog.txt*


### Credits

Angel Marin and Paul Johnston for the JavaScript SHA-256 hash implementation used.


### Miscellaneous

Noter was originally created as a second language flashcard viewer (to which it can be easily reverted).

Noter was never coded for elegance, merely to do a simple job in minimal code. It's like my car: looks a wreck, runs quite well.


### License

Noter is released under the [GPL v.3](https://www.gnu.org/licenses/gpl-3.0.html).
