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
     * @param   string   $database
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
     * the specified table. If $column is set, the returned stdClass object
     * contains one column. An empty array is returned if no are results found.
     *
     * @param   string   $database
     * @param   string   $table
     * @param   string   $column
     * @return  array|stdClass
     */
    public static function schema($database, $table, $column = null)
    {
        $sql  = 'SHOW COLUMNS FROM `' . $database . '`.`' . $table . '`';
        $sql .= $column ? " LIKE '{$column}';" : ';';

        $results = static::fetchAll($sql);
        $results = static::setColumnPad($results);

        foreach ($results as $key => $result)
        {
            $type    = explode('(', $result['Type']);
            $type[1] = isset($type[1]) ? explode(')', $type[1]) : array(null, null);

            $results[$key]['Type']    = $type[0];
            $results[$key]['Length']  = (($type[0] != 'enum') and ($type[0] != 'set')) ? $type[1][0] : null; //is_numeric($type[1][0]) ? $type[1][0] : null;
            $results[$key]['Value']   = $type[1][0];
            $results[$key]['Values']  = static::extractValues($type);
            $results[$key]['Attr']    = static::extractAttributes($type);
        }

        if ($column)
        {
            return $results[$column];
        }

        return $results;
    }

    /**
     * Extract mysql values.
     *
     * Extract mysql column values according to type. Types enum and set return an
     * array of values.
     *
     * @param   string   $type
     * @return  string|array
     */
    protected static function extractValues($type)
    {
        $values = $type[1][0];
        $type =  $type[0];

        if ($type == 'enum' or $type == 'set')
        {
            $values = str_replace("'", '', $values);
            return explode(',', $values);
        }
    }

    /**
     * Extract column attributes.
     *
     * @param   string   $type
     * @return  string|array
     */
    protected static function extractAttributes($type)
    {
        if ($type[1][1] === '' or $type[1][1] === null)
        {
            return null;
        }

        return trim($type[1][1]);
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
            $len = strlen($column['Field']);

            if ($len > $max_length)
            {
                $max_length = $len;
            }
        }

        // create pad based on difference of column name
        // length and maximum column name length.
        foreach ($columns as $key => $column)
        {
            $columns[$key]['Pad'] = str_repeat(' ', $max_length - strlen($column['Field']));
        }

        return $columns;
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