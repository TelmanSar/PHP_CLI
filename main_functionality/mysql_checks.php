<?php
/**
 * @param $host
 * @param $user
 * @param $password
 * @param $db_name
 * @return mysqli server connection link
 * @throws Exception
 */
function get_mysql_server_link_for_export($host, $user, $password, $db_name)
{
    $link = mysqli_connect($host, $user, $password, $db_name);
    if ($link === false) {
        throw new Exception("ERROR: Could not connect. " . mysqli_connect_error() . "\n");
    }
    return $link;
}
/**
 * @param $link
 * @param $tables
 * @param $db_name
 * @return array
 * @throws Exception
 */
function get_tables_names($link, $tables, $db_name)
{
    $tables_in_database = get_tables_from_db($link, $db_name);
    if ($tables !== '*') {
        check_tables_existence_in_db($tables_in_database, $tables, $db_name);
        return $tables;
    }
    return $tables_in_database;
}
/**
 * @param $link
 * @param $db_name
 * @return array
 * @throws Exception
 */
function get_tables_from_db($link, $db_name)
{
    $result = mysqli_query($link, "SHOW TABLES");
    if ($result === false) {
        throw new Exception("ERROR: Could not execute SHOW TABLES query. " . mysqli_connect_error() . "\n");
    }
    while ($tb_name = mysqli_fetch_array($result, MYSQLI_NUM)) {
        $tables_in_database[] = $tb_name[0];
    }
    if (!isset($tables_in_database)) {
        throw new Exception(" Database $db_name is empty");
    }
    return $tables_in_database;
}
/**
 * @param $tables_in_database
 * @param $tables
 * @param $db_name
 * @throws Exception
 */
function check_tables_existence_in_db($tables_in_database, $tables, $db_name)
{
    foreach($tables as $value){
        if(!in_array($value, $tables_in_database)) {
            throw new Exception("There is no $value table in database $db_name");
        }
    }
}
/**
 * @param $link
 * @param $table
 * @return string
 */
function get_table_structure($link, $table)
{
        $table_structure_result = mysqli_query($link, "SHOW CREATE TABLE $table");
        $table_structure_string = mysqli_fetch_array($table_structure_result, MYSQLI_BOTH)[1];
        return $table_structure_string;
}
function get_table_data($link, $table)
{
    $fk_0 = "SET foreign_key_checks = 0; \n \n";
    $column_names = get_column_names($link, $table);
    if(!$column_names) {
        return false;
    }
    $insert_command = "INSERT INTO `$table`($column_names) VALUES \n";
    $result = mysqli_query($link, "SELECT * FROM $table");
    $data = '';
    while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
        $data .= '( ';
        foreach($row as $value) {
            $value ? $data .= "'". addslashes($value) . "'" : $data .= 'NULL';
            $data .= ', ';
        }
        $data = substr($data, 0, -2);
        $data .= "),\n";
    }
    $data = substr($data, 0, -2);
    $data .= ";\n";
    $fk_1 = "SET foreign_key_checks = 1;  \n \n";
    return $fk_0.$insert_command.$data.$fk_1;
}
function get_column_names($link, $table)
{
    $result = mysqli_query($link, "SHOW COLUMNS FROM $table");
    while ($name = mysqli_fetch_array($result, MYSQLI_BOTH)[0]) {
        $column_names[] = "`".$name."`";
    }
    return isset($column_names) ? implode(',',$column_names) : false;
}
/**
 * @param $host
 * @param $user
 * @param $password
 * @return false|mysqli
 * @throws Exception
 */
function get_mysql_server_link_for_import($host, $user, $password)
{
    $link = mysqli_connect($host, $user, $password);
    if ($link === false) {
        throw new Exception("ERROR: Could not connect. " . mysqli_connect_error() . "\n");
    }
    return $link;
}