<?php
/**
 * @param $argv
 * @return array
 * @throws Exception
 */
function get_arguments($argv)
{
    if (!isset($argv[0])) {
        throw new Exception("No any arguments passed");
    }
    array_shift($argv);
    $parsed_args = parse_arguments($argv);
    $validated_args = validate_arguments($parsed_args);
    return $validated_args;
}
/**
 * @param $arguments
 * @return array
 * @throws Exception
 */
function parse_arguments($arguments)
{
    foreach ($arguments as $value) {
        $key_value_pair = get_key_value_pair($value);
        $key_string = get_var_name($key_value_pair[0]);
        $value_string = $key_value_pair[1];
        $associative_array_of_arguments["$key_string"] = $value_string;
    }
    /** @var array $associative_array_of_arguments */
    return $associative_array_of_arguments;
}
/**
 * @param $passed_argument
 * @return array
 * @throws Exception
 */
function get_key_value_pair($passed_argument)
{
    if (preg_match('/^--[\w]+?=./', $passed_argument) !== 1) {
        throw new Exception("Please check the format for $passed_argument argument");
    }
    return explode('=', $passed_argument);
}
/**
 * @param $key_string
 * @return string
 * @throws Exception
 */
function get_var_name($key_string)
{
    $key_string = substr($key_string, 2);
    switch ($key_string) {
        case "host":
        case "user":
        case "password":
        case "command":
        case "database":
        case "tables":
        case "file":
            return $key_string;
        default:
            throw new Exception("Such a variable does not exist: $key_string");
    }
}
/**
 * @param $parsed_args
 * @return mixed
 * @throws Exception
 */
function validate_arguments($parsed_args)
{
    if (!isset($parsed_args["host"])) {
        $validated_args["host"] = "localhost";
    } else {
        $validated_args["host"] = $parsed_args["host"];
    }
    if (!isset($parsed_args["user"])) {
        $validated_args["user"] = "root";
    } else {
        $validated_args["user"] = $parsed_args["user"];
    }
    if (!isset($parsed_args["password"])) {
        $validated_args["password"] = "1234";
    } else {
        $validated_args["password"] = $parsed_args["password"];
    }
    if (!isset($parsed_args["command"])) {
        throw new Exception("You did not pass any command.");
    }
    $validated_args["command"] = check_and_get_command($parsed_args["command"]);
    if (!isset($parsed_args["database"])) {
        throw new Exception("You did not pass any database.");
    };
    $validated_args["database"] = check_and_get_database($parsed_args["database"]);
    if (($validated_args["command"] === "export_data" || $validated_args["command"] === "export_structure") && (!isset($parsed_args["tables"]) || $parsed_args["tables"] === '*') ) {
        $validated_args["tables"] = "*";
    } else {
        $validated_args["tables"] = check_and_get_table_names($parsed_args["tables"]);
    }
    if ( ($validated_args["command"] === "export_data" || $validated_args["command"] === "export_structure") && !isset($parsed_args["file"])) {
        $validated_args["file"] = generate_file_name();
    } elseif ($validated_args["command"] === "import" && !isset($parsed_args["file"])) {
        throw new Exception("Please specify file for Import");
    }
    else {
        $validated_args["file"] = check_and_get_file_name($parsed_args["file"]);
    }
    return $validated_args;
}
/**
 * @param $command
 * @return string
 * @throws Exception
 */
function check_and_get_command($command)
{
    switch ($command) {
        case "export_data":
        case "export_structure":
        case "import":
            return $command;
        default:
            throw new Exception("There is no such command: $command");
    }
}
/**
 * @param $database
 * @return string
 * @throws Exception
 */
function check_and_get_database($database)
{
    if (!preg_match('/^[\w$]{1,64}$/', $database) && preg_match('/^[0-9]{1,64}$/', $database)) {
        throw new Exception("Wrong database name format, the database name should contain only alphanumeric values, underscore and dollar signs");
    }
    return $database;
}
/**
 * @param $tables
 * @return array
 * @throws Exception
 */
function check_and_get_table_names($tables)
{
    try {
        $tables = explode(',', $tables);
    } catch (Exception $exception) {
        throw new Exception("The value of tables argument must be in a format of comma separated table names");
    }
    foreach ($tables as $tb_name)
        if (!preg_match('/^[\w$]{1,64}$/', $tb_name) && preg_match('/^[0-9]{1,64}$/', $tb_name)) {
            throw new Exception("Wrong table name format for: $tb_name, the table name should contain only alphanumeric values, underscore and dollar signs");
        }
    return $tables;
}
function generate_file_name()
{
    return "dump-" . date('Y\-m\-d\-H:i:s') . ".sql";
}
/**
 * @param $file_name
 * @return string
 * @throws Exception
 */
function check_and_get_file_name($file_name)
{
    if (pathinfo($file_name)['extension'] !== 'sql') {
        throw new Exception("wrong file format, the should have in .sql extension");
    }
    return $file_name;
}
