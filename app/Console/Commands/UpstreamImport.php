<?php

namespace CachetHQ\Cachet\Console\Commands;

use CachetHQ\Cachet\Models\Component;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Make sure to set APPLICATION_UPSTREAM_MAPPING env value
 * format: 1:2,4:12
 * Where 1 and 4 are ID's of the upstream component, and 2 and 12 are the id's of the local components
 */
class UpstreamImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upstream:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set component status from upstream';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // get mapping from env
        $raw_mapping = config('application.upstream.mapping');
        $mappings = [];
        foreach (explode(',',$raw_mapping) as $map) {
            $maps = explode(':',$map);
            $mappings[$maps[0]] = $maps[1];
        }

        // get upstream components
        $client = new Client(['base_uri' => config('application.upstream.url')]);
        $res = $client->request('GET', '/api/v1/components', ['allow_redirects' => false]);
        if($res->getStatusCode() != 200) {
            Log::error('upstream api connection failed');
            exit();
        }
        $json = $res->getBody();
        $upstream_components = json_decode($json);

        // process stuff
        foreach ($upstream_components->data as $upstream_component) {
            if(isset($mappings[$upstream_component->id])) {
                $component = Component::find($mappings[$upstream_component->id]);
                if($component) {
                    $component->status = $upstream_component->status;
                    $component->save();
                }
            }
        }

    }
}
