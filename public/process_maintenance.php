<?php

// This script sets databases older than 7 days to have a completed time, effectively removing them from the frontpage.

// get the database connection details
include ('/etc/statuspage_rss.conf');

$sql = $dbh->prepare("
    UPDATE schedules s SET s.completed_at = NOW()
    WHERE s.scheduled_at <= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND s.completed_at IS null
    ");
$sql->execute();

?>
