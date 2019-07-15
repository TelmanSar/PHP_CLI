<?php
//check if any arguments passed during call of script
if ($argc > 1) {
    //if second argument is export, run export function
    if (strtolower($argv[1]) === "export") {
        export();
    } elseif (strtolower($argv[1]) === "import") {
        import();
    }
} else {
    die("You didn't pass any arguments. Please specify arguments.\n");
}
function export()
{
    global $argv;
    global $argc;
//check if database specified or not
    try {
        if (isset($argv[2]) === false) {
            throw new Exception("Database name is not specified");
        }
        $database_name = $argv[2];
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage() . "\n");
    }
    //connection to mysql server
    $link = mysqli_connect("localhost", "root", "1234", $database_name);
    //check connection
    if ($link === false) {
        die("ERROR: Could not connect. " . mysqli_connect_error() . "\n");
    }
    //check if such database exist or not
    if (mysqli_query($link, "USE $database_name") === false) {
        die("ERROR: Could not able to execute USE $database_name " . mysqli_error($link) . "\n");
    }
    //check the names of tables: specified or must choose all of them
    if ($argv[3] === '*' || isset($argv[3]) === false || ($argc === 4 && strpos($argv[3], '.') !== false)) {
        if (mysqli_query($link, "SHOW TABLES")) {
            $table_names_resource = mysqli_query($link, "SHOW TABLES");
            //create table names array
            while ($table_name = mysqli_fetch_array($table_names_resource, MYSQLI_BOTH)) {
                $table_names_array[] = $table_name[0];
            }
        } else {
            die("ERROR: Could not able to execute SHOW TABLES " . mysqli_error($link) . "\n");
        }
    } else {
        //split the names of tables into array from passed comma separated argument
        $table_names_array = explode(',', $argv[3]);
        for ($i = 0; $i < count($table_names_array); $i++) {
            //check tables' names existence in DB
            $sql_query = "SELECT count(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = \"$database_name\" AND TABLE_NAME = \"$table_names_array[$i]\"";
            if (mysqli_fetch_array(mysqli_query($link, $sql_query), MYSQLI_BOTH)[0] > 0) {
                continue;
            } else {
                die("ERROR: table $table_names_array[$i] does not exist in $database_name database \n");
            }
        }
    }
    //collect table structure info in one array
    for ($i = 0; $i < count($table_names_array); $i++) {
        $table_schema_resource = mysqli_query($link, "SHOW CREATE TABLE $table_names_array[$i]");
        $table_schema_string = mysqli_fetch_array($table_schema_resource, MYSQLI_BOTH)[1];
        $tables_structure_array[] = $table_schema_string;
    }
    //Check if fileName is specified or not
    if ((isset($argv[4])) || (isset($argv[3]) && $argv[3] !== "*" && strpos($argv[3], '.') !== false)) {
        $file_name = $argv[4] ?? $argv[3];
        //Check if filename has right file extension (ends with ".sql")
        if (preg_match('/[\w].sql$/', $file_name) !== 1) {
            die("ERROR: wrong file extension");
        }
    } else {
        $file_name = "dump-" . date('Y\-m\-d\-H:i:s') . ".sql";
    }
    $dump_file = fopen($file_name, "w");
    fwrite($dump_file, "CREATE DATABASE IF NOT EXISTS $database_name;");
    fwrite($dump_file, "\n");
    fwrite($dump_file, "USE $database_name;");
    fwrite($dump_file, "\n");
    for ($i = 0; $i < count($tables_structure_array); $i++) {
        fwrite($dump_file, "DROP TABLE IF EXISTS $table_names_array[$i];\n");
        fwrite($dump_file, "\n");
        fwrite($dump_file, $tables_structure_array[$i] . ";\n");
        fwrite($dump_file, "\n");
    }
    fclose($dump_file);
    mysqli_close($link);
}
function import()
{
    global $argv;
    try {
        if (isset($argv[2]) === false) {
            throw new Exception("Database name is not specified");
        }
        $database_name = $argv[2];
    } catch (Exception $e) {
        die("ERROR: " . $e->getMessage() . "\n");
    }
    $link = mysqli_connect("localhost", "root", "1234");
    if ($link === false) {
        die("ERROR: Could not connect. " . mysqli_connect_error() . "\n");
    }
    if (mysqli_query($link, "USE $database_name") === false) {
        die("ERROR: Could not able to execute USE $database_name " . mysqli_error($link) . "\n");
    }
    if (isset($argv[3])) {
        $file_name = $argv[3];
        //Check if filename has right file extension (ends with ".sql")
        if (preg_match('/[\w].sql$/', $file_name) !== 1) {
            die("ERROR: wrong file extension");
        }
    } else {
        die("ERROR: You didn't specify any file name for import");
    }
    $string_of_queries_to_be_executed = file_get_contents($file_name);
    mysqli_query($link, "DROP DATABASE IF EXISTS $database_name");
    mysqli_query($link, "CREATE DATABASE $database_name");
    mysqli_query($link, "USE $database_name");
    mysqli_multi_query($link, $string_of_queries_to_be_executed);
    mysqli_close($link);
}
