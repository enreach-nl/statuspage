<?php

// get the database connection details
include ('/etc/statuspage_rss.conf');

// prepare the query to get the neccesary information
$sql = $dbh->prepare("
    SELECT cg.name AS category, c.name AS service,
    case c.`status`
        when 1 then 'No issues'
        when 2 then 'Performance Issues'
        when 3 then 'Partial Outage'
        when 4 then 'Major Outage'
    ELSE 'Unknown'
    END AS currentStatus
    FROM components c
    LEFT JOIN component_groups cg ON c.group_id = cg.id
    WHERE c.enabled = 1 AND cg.visible = 1 AND c.deleted_at IS NULL
");
$sql->execute();
$results = $sql->fetchAll();

// start building the array based on the output of the query
$groupedArray = array();

foreach ($results as $item) {
    $category = $item['category'];
    $service = $item['service'];
    $currentStatus = $item['currentStatus'];

    if (!array_key_exists($category, $groupedArray)) {
        $groupedArray[$category] = array();
    }

    $groupedArray[$category][] = array(
        'service' => $service,
        'currentStatus' => $currentStatus
    );
}

$json = json_encode($groupedArray);

echo $json;
