<?php
/**
 * @param $link
 * @param $file
 * @throws Exception
 */
function import($link, $file)
{
    $string_of_queries_to_be_executed = file_get_contents($file);
    $result = mysqli_multi_query($link, $string_of_queries_to_be_executed);
    if($result) {
        throw new Exception("Could not able to execute. \n".mysqli_error($link));
    }
    mysqli_close($link);
}
