<?php

namespace CachetHQ\Cachet\Http\Controllers;

use CachetHQ\Cachet\Models\Component;
use CachetHQ\Cachet\Models\ComponentGroup;
use CachetHQ\Cachet\Models\Incident;
use CachetHQ\Cachet\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class FeedController extends Controller
{
    public function feedJsonStatus(Request $request): JsonResponse
    {
        $groups = Cache::remember('feed_groups_for_incidents_json', 1, function () {
            return ComponentGroup::where('visible', true)->with('components')->get();
        });

        $response = [];
        foreach ($groups as $group) {
            if(!$group->components)
                continue;
            foreach ($group->components as $component) {
                if(!$component->enabled)
                    continue;

                $response[$group->name][] = [
                    'service' => $component->name,
                    'currentStatus' => __('cachet.components.status.'.$component->status),
                ];
            }
        }

        return response()->json($response);
    }

    public function feedIcalMaintenance(Request $request)
    {
        $schedules = Cache::remember('feed_ical', 1, function () {
            return Schedule::all();
        });

        $response = "BEGIN:VCALENDAR\r\n";
        $response .= "VERSION:2.0\r\n";
        $response .= "PRODID:-//Enreach//Maintenance Calendar//EN\r\n";

        if ($schedules->count() > 0) {
            foreach ($schedules as $schedule) {

                $response .= "BEGIN:VEVENT\r\n";
                $response .= "UID:".uniqid()."\r\n";
                $response .= "DTSTAMP:" . $schedule->scheduled_at->format('Ymd\This\Z') . "\r\n";
                $response .= "DTSTART:{$schedule->scheduled_at->format('Ymd\This\Z')}\r\n";
                $response .= "DTEND:{$schedule->scheduled_at->addHours(6)->format('Ymd\This\Z')}\r\n";
                $response .= "SUMMARY:{$schedule->name}\r\n";
                $response .= "DESCRIPTION:{$schedule->message}\r\n";
                $response .= "END:VEVENT\r\n";
            }
        }

        $response .= "END:VCALENDAR\r\n";

        return response()->make($response)->withHeaders([
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=calendar.ics'
        ]);
    }

    public function feedRssStatus(Request $request)
    {
        $components = Cache::remember('feed_rss_components', 1, function () {
            return Component::where('enabled', true)->with('group')->get();
        });

        $rssTitle = "Enreach Service Status";
        $rssLink = url($request->getRequestUri());
        $rssDescription = "A feed for service status updates.";

        // Create the XML document
        $xmlDoc = new \DOMDocument('1.0', 'utf-8');
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

        // Create lastBuildDate element with the current date and time in a 4-digit year format
        $lastBuildDate = $xmlDoc->createElement('lastBuildDate', Carbon::now()->format('D, d M Y H:i:s O'));

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
        foreach ($components as $service) {

            if(!$service->group->visible)
                continue;

            $item = $xmlDoc->createElement('item');
            $channel->appendChild($item);

            $title = $xmlDoc->createElement('title', $service->name);
            $item->appendChild($title);

            // $description = $xmlDoc->createElement('description', $service['currentStatus']);
            $description = $xmlDoc->createElement('description', htmlspecialchars(
                __('cachet.components.status.'.$service->status), ENT_QUOTES, 'UTF-8'));

            $item->appendChild($description);

            // $categoryElement = $xmlDoc->createElement('category', $category);
            $categoryElement = $xmlDoc->createElement('category', htmlspecialchars($service->group->name, ENT_QUOTES, 'UTF-8'));

            $item->appendChild($categoryElement);

            // Create a GUID for each item, using the service['id'] as a unique identifier (or any other unique value from your data)
            $guid = $xmlDoc->createElement('guid', htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].'/donotuse/' . Uuid::uuid4(), ENT_QUOTES, 'UTF-8'));
            $guid->setAttribute('isPermaLink', 'false');
            $item->appendChild($guid);
        }

        // Output the XML document as a string
        $rssString = $xmlDoc->saveXML();

        return response()->make($rssString)->withHeaders([
            'Content-Type' => 'application/rss+xml; charset=utf-8'
        ]);
    }

    public function feedRssIncidents(Request $request)
    {
        Cache::forget('feed_rss_incidents');
        $incidents = Cache::remember('feed_rss_incidents', 1, function () {
            return Incident::where('visible', true)->get();
        });

        $rssTitle = "Enreach Service Status - incidents";
        $rssLink = url($request->getRequestUri());
        $rssDescription = "A feed for service status updates.";

        // Create the XML document
        $xmlDoc = new \DOMDocument('1.0', 'utf-8');
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

        // Create lastBuildDate element with the current date and time in a 4-digit year format
        $lastBuildDate = $xmlDoc->createElement('lastBuildDate', Carbon::now()->format('D, d M Y H:i:s O'));

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
        foreach ($incidents as $incident) {

            $item = $xmlDoc->createElement('item');
            $channel->appendChild($item);

            $item->appendChild(
                $xmlDoc->createElement('title', $incident->name)
            );

            $item->appendChild(
                $xmlDoc->createElement('link', url('incidents/'.$incident->id))
            );

            $item->appendChild(
                $xmlDoc->createElement('description', htmlspecialchars(
                    __('cachet.incidents.status.'.$incident->message), ENT_QUOTES, 'UTF-8')
                )
            );

            $item->appendChild(
                $xmlDoc->createElement('guid', url('incidents/'.$incident->id))
            );

            $item->appendChild(
                $xmlDoc->createElement('pubDate', $incident->occurred_at->toRfc7231String())
            );

            $item->appendChild(
                $xmlDoc->createElement('status:incident', __('cachet.incidents.status.'.$incident->status))
            );
        }

        // Output the XML document as a string
        $rssString = $xmlDoc->saveXML();

        return response()->make($rssString)->withHeaders([
            'Content-Type' => 'application/rss+xml; charset=utf-8'
        ]);
    }
}
