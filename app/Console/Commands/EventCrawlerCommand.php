<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EventCrawlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:event-crawler-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = 'https://gofusion-python.6lgx.com/crawl';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://gofusion-python.6lgx.com/crawl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'authorization: Bearer f95ae7bc-f273-4e0b-91d7-6a1e3f9057f1'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        if ($response !== false) {
            $this->info('✅ Event crawler command executed successfully.');
        } else {
            $this->error('❌ Failed to execute event crawler command.');
        }
    }
}
