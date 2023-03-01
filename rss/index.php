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
    WHERE c.enabled = 1 AND cg.visible = 1
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

// Set the RSS feed title and link
$rssTitle = "Enreach Service Status";
$rssLink = "https://status.enreach.tech/rss";

// Create the XML document
$xmlDoc = new DOMDocument('1.0', 'utf-8');
$xmlDoc->formatOutput = true;

// Create the root element (rss)
$rss = $xmlDoc->createElement('rss');
$rss->setAttribute('version', '2.0');
$xmlDoc->appendChild($rss);

// Create the channel element and add it to the rss element
$channel = $xmlDoc->createElement('channel');
$rss->appendChild($channel);

// Add the title and link to the channel element
$title = $xmlDoc->createElement('title', $rssTitle);
$channel->appendChild($title);

$link = $xmlDoc->createElement('link', $rssLink);
$channel->appendChild($link);

// Loop through the $groupedArray and add the items to the channel element
foreach ($groupedArray as $category => $services) {
    foreach ($services as $service) {
        $item = $xmlDoc->createElement('item');
        $channel->appendChild($item);

        $title = $xmlDoc->createElement('title', $service['service']);
        $item->appendChild($title);

        $description = $xmlDoc->createElement('description', $service['currentStatus']);
        $item->appendChild($description);

        $categoryElement = $xmlDoc->createElement('category', $category);
        $item->appendChild($categoryElement);
    }
}

// Output the XML document as a string
$rssString = $xmlDoc->saveXML();

// Output the RSS feed
header('Content-Type: application/rss+xml; charset=utf-8');
echo $rssString;
