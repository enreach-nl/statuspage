<?php
error_reporting(0);

function generateV4UUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

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

// Set the RSS feed title and link
$rssTitle = "Enreach Service Status";
$rssLink = "https://" . $_SERVER['HTTP_HOST'] . "/rss/";
$rssDescription = "A feed for service status updates.";

// Create the XML document
$xmlDoc = new DOMDocument('1.0', 'utf-8');
$xmlDoc->formatOutput = true;

// Create the root element (rss)
$rss = $xmlDoc->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
$xmlDoc->appendChild($rss);

// Create the channel element and add it to the rss element
$channel = $xmlDoc->createElement('channel');
$rss->appendChild($channel);

// Add the title and link to the channel element
$title = $xmlDoc->createElement('title', $rssTitle);
$description = $xmlDoc->createElement('description', $rssDescription);

// Create a DateTime object for the current date and time
$currentDateTime = new DateTime();

// Create lastBuildDate element with the current date and time in a 4-digit year format
$lastBuildDate = $xmlDoc->createElement('lastBuildDate', $currentDateTime->format('D, d M Y H:i:s O'));

$channel->appendChild($title);
$channel->appendChild($description);
$channel->appendChild($lastBuildDate);

// Some sites reported the RSS was invalid because the atom link to self was missing. I've added it.
$atomLink = $xmlDoc->createElement('atom:link');
$atomLink->setAttribute('href', $rssLink);
$atomLink->setAttribute('rel', 'self');
$atomLink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomLink);

$link = $xmlDoc->createElement('link', $rssLink);
$channel->appendChild($link);

// Loop through the $groupedArray and add the items to the channel element
foreach ($groupedArray as $category => $services) {
    foreach ($services as $service) {
        $item = $xmlDoc->createElement('item');
        $channel->appendChild($item);

        $title = $xmlDoc->createElement('title', $service['service']);
        $item->appendChild($title);

        // $description = $xmlDoc->createElement('description', $service['currentStatus']);
        $description = $xmlDoc->createElement('description', htmlspecialchars($service['currentStatus'], ENT_QUOTES, 'UTF-8'));

        $item->appendChild($description);

        // $categoryElement = $xmlDoc->createElement('category', $category);
        $categoryElement = $xmlDoc->createElement('category', htmlspecialchars($category, ENT_QUOTES, 'UTF-8'));

        $item->appendChild($categoryElement);

        // Create a GUID for each item, using the service['id'] as a unique identifier (or any other unique value from your data)
        $guidValue = generateV4UUID();
        $guid = $xmlDoc->createElement('guid', htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].'/donotuse/' . $guidValue, ENT_QUOTES, 'UTF-8'));
        $guid->setAttribute('isPermaLink', 'false');
        $item->appendChild($guid);
    }
}

// Output the XML document as a string
$rssString = $xmlDoc->saveXML();

// Output the RSS feed
header('Content-Type: application/rss+xml; charset=utf-8');
echo $rssString;
