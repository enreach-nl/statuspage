<?php

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=calendar.ics');

// Database connection
include('/etc/statuspage_rss.conf');

$sql = $dbh->prepare("
SELECT name, message, scheduled_at FROM schedules
WHERE deleted_at IS NULL
");
$sql->execute();
$result = $sql->fetchAll();

function dateToCal($timestamp) {
    return date('Ymd\THis\Z', $timestamp);
}

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//Enreach//Maintenance Calendar//EN\r\n";

if ($sql->rowCount() > 0) {
    foreach ($result as $row) {
        $name = $row["name"];
        $message = $row["message"];
        $scheduled_at = $row["scheduled_at"];

        $dtstart = dateToCal(strtotime($scheduled_at));
        $dtend = dateToCal(strtotime($scheduled_at) + 21600); // Every event lasts for 6 hours.

        $uid = uniqid();

        echo "BEGIN:VEVENT\r\n";
        echo "UID:{$uid}\r\n";
        echo "DTSTAMP:" . dateToCal(time()) . "\r\n";
        echo "DTSTART:{$dtstart}\r\n";
        echo "DTEND:{$dtend}\r\n";
        echo "SUMMARY:{$name}\r\n";
        echo "DESCRIPTION:{$message}\r\n";
        echo "END:VEVENT\r\n";
    }
} else {
    echo "No events found";
}

echo "END:VCALENDAR\r\n";

?>

