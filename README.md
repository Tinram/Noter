
# Noter

#### Share notes across devices on a local network.


[1]: https://tinram.github.io/images/noter.png
![noter][1]


## Purpose

+ Share notes easily across devices and operating systems on a local network.

+ Avoid awkward device connections, network shares, or Internet transfer.

Noter's focus is simplicity &ndash; browser display and search, with a log-in to add, edit, and delete notes.

The log-in offers basic protection from unauthorised tampering via password obfuscation and session timeout.

Noter was created for an isolated network where plain text transfer and viewing is acceptable, and without determined adversaries. *Noter is NOT suitable for storing sensitive data.*

A single SQLite database file facilitates easy backup, transfer, and source control of data.

A Raspberry Pi, with Apache and PHP installed, could make an ideal low-powered always-on host for Noter.


## Requirements

A PHP server with the following modules enabled:

### PHP 7.0+

+ sqlite3

### PHP 5.4+

+ php5-sqlite
+ openssl


## Setup

Clone the repository (or extract the ZIP archive) into the server's web directory
e.g.

```bash
    cd /var/www/html

    sudo git clone https://github.com/Tinram/Noter.git

    sudo chown -R <username>:www-data Noter/
```

(Debian-based; use `apache` instead of `www-data` for Red Hat-based distros)

```bash
    sudo chown www-data Noter/log/badlog.txt

    sudo chmod 600 Noter/log/badlog.txt
```

SQLite file operation (requires actioning on the directory itself):

```bash
    sudo chown -R www-data Noter/db/
```

Check the configuration file constants: *config/config.php*

Only the timezone, session constants, and `CONFIG_NUM_NOTES_DISPLAYED` are of immediate interest, unless you wish to revise the SQLite database schema.

Change the users and passwords in the editing log-in gateway (*classes/login.class.php*). The default usernames (`USER1` and `USER2`) are *martin* and *alison*, and both passwords are *P@55w0rd*. More users can be easily added (at the top of the file and in `validateForm()`).

Passwords are stored as SHA-256 hashes. `$USER1_PASS` etc can be replaced with a hash generated from a website service or by running one of the following commands in a terminal and copying the output hash:

*Bash*

```bash
    echo -n 'PASSWORD' | sha256sum
```

*PHP*

```bash
    php -r "echo hash('sha256', 'PASSWORD');"
```

*Python*

```python
    python -c "import hashlib;print(hashlib.sha256('PASSWORD'.encode()).hexdigest())"

    python3 -c "import hashlib;print(hashlib.sha256('PASSWORD'.encode()).hexdigest())"
```

### Manually Create the SQLite Database

Noter includes an initial SQLite database: *db/noter.sqlite3*

However, where GitHub importing does not permit binary files (e.g. [PHPClasses](https://www.phpclasses.org) and the *noter.sqlite3* file is consequently missing, the file will need to be created manually &ndash;

In a terminal:

```bash
    cd db/
    sudo sqlite3 noter.sqlite3
    .read noter_schema_data.sql
    .exit
    sudo chown www-data:www-data noter.sqlite3
```


## Viewing and Searching

*http://localhost/Noter*

*http://IP.add.re.ss/Noter*

or better, configure an Apache vhost and access with:

*Noter*


## Adding and Editing

*http://localhost/Noter/edit*

or if Apache rewrite is disabled:

*http://localhost/Noter/edit.php*

Upon logging-in through this page, notes can be added, updated, or deleted.

`<pre>`...`</pre>` and `<code>`...`</code>` tags can be used to highlight code snippets.

`<pre>`...`</pre>` preserves whitespace (both spaces and copy/pasted tabs).

`<code>`...`</code>` does not preserve whitespace.

Most types of link references will be automatically converted into HTML links upon note addition (but not on note updating).


## Other Files

The SQLite database schema is available at *db/noter\_schema.sql*

The SQLite database schema and initial data is available at *db/noter\_schema\_data.sql*

Unsuccessful log-in attempts are recorded in *log/badlog.txt*


## Credits

+ Angel Marin and Paul Johnston &ndash; JavaScript SHA-256 hash function.

+ Andrew Ellis &ndash; link regex.


## Miscellaneous

Noter was originally created as a second language flashcard viewer (to which it can be easily reverted).

Noter was never coded for elegance, merely to do a simple job in minimal code. It's like my car: looks ancient, runs quite well.


## License

Noter is released under the [GPL v.3](https://www.gnu.org/licenses/gpl-3.0.html).
