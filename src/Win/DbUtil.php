<?php namespace Win;

use DB, PDO;

class DbUtil {

    /**
     * Run pdo query and fetch all results.
     *
     * Runs query $sql and fetches all results as an array of stdClass objects.
     * Returns an empty array when no results found.
     *
     * @param   string  $sql
     * @return  array
     */
    public static function fetchAll($sql)
    {
        $pdo = DB::connection()->getPdo();
        $pdo = $pdo->query($sql);

        return $pdo->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Show databases.
     *
     * Returns an array of database names.
     * Returns an empty array when no results found.
     *
     * @return  array
     */
    public static function showDatabases()
    {
        $databases = static::fetchAll('SHOW DATABASES');

        return array_flatten($databases);
    }

    /**
     * Show tables in database.
     *
     * Returns an array of table names in the currently configured database.
     * Returns an empty array when no results found.
     *
     * @return  array
     */
    public static function showTables($database = null)
    {
        $sql = 'SHOW TABLES';

        if ($database)
        {
            $sql .= ' FROM ' . $database;
        }

        $tables = static::fetchAll($sql);

        return array_flatten($tables);
    }

    /**
     * Determine if a table exists.
     *
     * @return  bool
     */
    public static function hasTable($table)
    {
        $tables = static::showTables();

        return in_array($table, $tables);
    }

    /**
     * Get Table Schema
     *
     * Returns an array of stdClass objects containing column information about
     * the specified table. If column_name is set, the returned stdClass object
     * contains one column. An empty array is returned if no are results found.
     *
     * @param   string   $database_name
     * @param   string   $table_name
     * @param   string   $column_name
     * @return  array|stdClass
     */
    public static function schema($database_name, $table_name, $column_name = null)
    {
        // $comments = static::extractComments($table_name);

        $sql  = 'SHOW COLUMNS FROM `' . $database_name . '`.`' . $table_name . '`';
        $sql .= $column_name ? " LIKE '{$column_name}';" : ';';

        $results = static::fetchAll($sql);
return $results;
dd($results);
        $results = static::setColumnPad($results);

        foreach ($results as $key => $result)
        {
            // Change the key to the field name.
            $newkey = $results[$key]->field;
            $results[$newkey] = $results[$key];
            unset($results[$key]);

            $type    = explode('(', $result->type);
            $type[1] = isset($type[1]) ? explode(')', $type[1]) : array(null, null);

            $results[$newkey]->type    = $type[0];
            $results[$newkey]->length  = (($type[0] != 'enum') and ($type[0] != 'set')) ? $type[1][0] : null; //is_numeric($type[1][0]) ? $type[1][0] : null;
            $results[$newkey]->value   = $type[1][0];
            $results[$newkey]->values  = static::parseValues($type[1][0], $type[0]);
            $results[$newkey]->attr    = $type[1][1] === '' ? null : trim($type[1][1]);
            $results[$newkey]->comment = $comments[$result->field]['comment'];
            $results[$newkey]->data    = $comments[$result->field]['data'];
        }

        if ($column_name)
        {
            return $results[$column_name];
        }

        return $results;
    }

    /**
     * Parse mysql values.
     *
     * Parse mysql column values according to type. Types enum and set return an
     * array of values.
     *
     * @param   string   $values
     * @param   string   $type
     * @return  string|array
     */
    protected static function parseValues($values, $type)
    {
        if ($type == 'enum' or $type == 'set')
        {
            $values = str_replace("'", '', $values);
            return explode(',', $values);
        }
    }

    /**
     * Sets a string padded with spaces for each column to make the code line up.
     *
     * @param  array $columns
     * @return void
     */
    public static function setColumnPad($columns)
    {
        $max_length = 0;

        foreach ($columns as $key => $column)
        {
            $len = strlen($column->field);

            if ($len > $max_length)
            {
                $max_length = $len;
            }
        }

        // create pad based on difference of column name
        // length and maximum column name length.
        foreach ($columns as $key => $column)
        {
            $columns[$key]->pad = str_repeat(' ', $max_length - strlen($column->field));
        }

        return $columns;
    }

    /**
     * Extract comments
     *
     * Extracts comments from a mySQL create_table statement into an array.
     *
     * @param   string   $table_name
     * @return  array
     */
    protected static function extractComments($table_name)
    {
        // the create table statement lies in a
        // class var named 'create table' (with a space).
        // use this variable overcome the space problem.
        $class_var = 'create table';

        $regex1 = '|^  `(.+)` |';
        $regex2 = "#COMMENT '(.+)',$#U";

        $sql = 'SHOW CREATE TABLE `' . $table_name . '`';
        $results = static::fetchAll($sql);

        $lines = explode("\n", $results[0]->$class_var);

        $comments = array();

        foreach ($lines as $line)
        {
            preg_match_all($regex1, $line, $matches);

            // extract column name
            if ( ! empty($matches[1][0]))
            {
                $column_name = $matches[1][0];

                $comments[$column_name] = null;

                preg_match_all($regex2, $line, $matches);

                // extract comment
                if ( ! empty($matches[1][0]))
                {
                    $comment = $matches[1][0];
                    $split = explode('|', $comment);
                    $data = $split[0] == 'EVAL' ? eval('return ' . $split[1] . ';') : null;
                    $comments[$column_name] = array(
                        'comment' => $comment,
                        'data' => $data,
                    );
                }
            }
        }

        return $comments;
    }

    /**
     * Parse phpMyAdmin
     *
     * Parses a phpMyAdmin export file into an array of statements.
     *
     * @param   string   $sql
     * @return  array
     */
    public static function parsePhpmyadmin($sql)
    {
        $sql = explode(';' . "\n", $sql);

        foreach ($sql as $key1 => $statement)
        {
            $sql[$key1] = explode("\n", $statement);

            foreach ($sql[$key1] as $key2 => $line)
            {
                $substr = substr($line, 0, 2);
                if ($substr == '--')
                {
                    $sql[$key1][$key2] = '';
                }
            }

            $sql[$key1] = implode('', $sql[$key1]);
        }

        return array_filter($sql);
    }

    /**
     * Backup the default MySQL database.
     *
     * @return bool
     */
    public static function backupMysql()
    {
        Bundle::start('mysqldump');

        $filename = time() . ".sql";
        $filepath = "storage/work/";

        $conn = Config::get('database.connections.mysql');

        $dump = new MySQLDump();
        $dump->host     = $conn['host'];
        $dump->user     = $conn['username'];
        $dump->pass     = $conn['password'];
        $dump->db       = $conn['database'];
        $dump->filename = $filepath . $filename;

        return $dump->start();
    }

    /**
     * Get MySQL information.
     *
     * @return  array
     */
    public static function mysqlInfo()
    {
        $sql = "SELECT
                VERSION() as `version`,
                USER() as `user`,
                DATABASE() as `database`,
                CHARSET(USER()) as `charset`,
                COLLATION('abc') as `collation`";

        $info = self::fetch_all($sql);

        return (array) $info[0];
    }


}