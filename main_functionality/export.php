<?php
/**
 * @param $link
 * @param $command
 * @param $db_name
 * @param $tables
 * @param $file
 * @throws Exception
 */
function export($link, $command, $db_name, $tables, $file)
{
    $dump_file = fopen($file, "w");
    fwrite($dump_file, "DROP DATABASE IF  EXISTS $db_name;");
    fwrite($dump_file, "SET foreign_key_checks = 0;");
    fwrite($dump_file, "CREATE DATABASE $db_name;");
    fwrite($dump_file, "\n \n");
    fwrite($dump_file, "USE $db_name;");
    fwrite($dump_file, "\n \n");
    $tables_names = get_tables_names($link, $tables, $db_name);
    foreach ($tables_names as $table) {
        $table_structure = get_table_structure($link, $table);
        fwrite($dump_file, "$table_structure;");
        fwrite($dump_file, "\n \n");
        if ($command === "export_data") {
            $data = get_table_data($link, $table);
            if ($data) {
                fwrite($dump_file, "$data");
                fwrite($dump_file, "\n \n");
            }
        }
    }
    fwrite($dump_file, "SET foreign_key_checks = 1;");
    fclose($dump_file);
}