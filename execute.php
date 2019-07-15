
<?php
try {
    require_once "main_functionality/arguments.php";
    $options = get_arguments($argv);
    [   'host' => $host,
        'user' => $user,
        'password' => $password,
        'command' => $command,
        'database' => $db_name,
        'tables' => $tables,
        'file' => $file] = $options;
    require_once "main_functionality/mysql_checks.php";
    if($command === "export_data" || $command === "export_structure") {
        require_once "main_functionality/export.php";
        $link = get_mysql_server_link_for_export($host, $user, $password,$db_name);
        export($link, $command, $db_name, $tables, $file);
        mysqli_close($link);
    } else {
        require_once "main_functionality/import.php";
        $link = get_mysql_server_link_for_import($host, $user, $password);
        import($link, $file);
        mysqli_close($link);
    }
} catch (Exception $exception) {
    die("ERROR: " . $exception->getMessage() . "\n");
}
