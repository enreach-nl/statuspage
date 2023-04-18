<?php

// get the database connection details
include('/etc/statuspage_rss.conf');

$auth_id = 'AvDZnrvgyJ64gPP2JYVnT3BlbkFJR5U9XYjhgdftyeIBJZ6jV';

$sql = $dbh->prepare("
    SELECT cg.name AS category, c.name AS service, c.status as currentStatus
    FROM components c
    LEFT JOIN component_groups cg ON c.group_id = cg.id
    WHERE c.enabled = 1 AND cg.visible = 1 AND c.deleted_at IS NULL
");
$sql->execute();
$input = $sql->fetchAll();

$output = [];

foreach ($input as $array) {
    $json_obj = new stdClass();
    foreach ($array as $key => $value) {
        if (is_string($key)) {
            $json_obj->$key = $value;
        }
    }
    $output[] = $json_obj;
}

echo json_encode($output, JSON_FORCE_OBJECT);
