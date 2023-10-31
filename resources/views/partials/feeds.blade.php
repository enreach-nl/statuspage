<div class="feeds">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <p style="text-align: center;">
                    @if(config('application.feed.rss_incidents'))
                    <a href="{{ cachet_route('feed_rss_incidents') }}" class="feed">Incidents RSS</a>
                    @endif
                    @if(config('application.feed.rss_status'))
                    <a href="{{ cachet_route('feed_rss_status') }}" class="feed">Status RSS</a>
                    @endif
                    <a href="{{ cachet_route('feed_json_status') }}" class="feed">Status JSON</a>
                    <a href="{{ cachet_route('feed_ical_maintenance') }}" class="feed">Maintenance iCal</a>
                </p>
            </div>
        </div>
    </div>
</div>
<style>
    .feeds {
        padding: 0 0 20px 0;
    }

    a.feed {
        background:#AC96FF;
        color:#38006B;
        padding: 3px 8px;
        border-radius: 6px;
        margin: 0 3px;
        text-decoration: none;
        font-size: 0.9em;
    }
    a.feed:hover {
        box-shadow: 0 2px;
    }
</style>
