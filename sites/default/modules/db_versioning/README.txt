This module is aimed to help the versioning of the database information stored,
next with the code, to be able to version the db info (git, svn, whatever)

What the module does, it's transform every single sql operation that affects the
db (INSERT, DELETE, UPDATE, etc, etc) to a sql script in a file. For that, uuid
is extensively used to enable the cross env capabilities. That way, one developer can 
install and do a lot of stuff in the site, and another create content, install
modules, etc, etc. One commited the changes, the scripts will be send next to the
code, and on the other side, the module will be able to detect new script files
and run the necessary operations to update the db site.

UUID is used in every single table in the site, that has a PK such that is surrogate, 
to assoc a UUID with that. That's the heart of all the moveability of
the db info. Tables with no PK won't be assoc with a UUID (obviously)
Complex surrogate keys (really, that could happen, mix an auto_inc with another stuff,
and you got it) will be adressed too.

This module needs you to patch Drupal core, to let you add custom behavior to implement
UUIDs to data that needs that.

Use this module just on dev env, because of the high performance reqs that a UUID
handling could means

To use the module, you have to apply the patches detailed in the folder patches
of the module. Read the README.txt file there to know how to do that


To use the module, you have to modify the settings.php file. At the end, include 
the next:

// Enable DB Versioning module capabilities
include "path/to/your/module/db_versioning/includes/db_vesioning_bootstrap.inc";

The hooks related to this are called hook_db_query_alter, and the form they have
to be described, is different from the normal way. Why use another way to load a 
hook, because this module catch every call to db_query, and there are time that
this is called in a very early stage (check bootstrap order).

So, to implement a hook_db_query_alter, create a file called whatever you like with
the extension .db_versioning. It's a php file (like a module). There you define the
hook

This module uses a parser to do the transformation from SQL to a PHP understandable
structure. By now, we use php-sql-parser (http://code.google.com/p/php-sql-parser/)
In the future, better parsers could be used, but the main goal in this, is to use
the Mysql parser itself (by now didn't try to make any attempt to implement this 
way, due to lack of time)

NOTE: Only tested in MySsql