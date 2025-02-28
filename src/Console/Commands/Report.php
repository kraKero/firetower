<?php

namespace Krakero\FireTower\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Krakero\FireTower\Checks\ApplicationInfoCheck;
use Krakero\FireTower\Facades\FireTower;

class Report extends Command
{
    public $signature = 'firetower:report';

    public $description = 'Reports data to your server';

    public function handle(): int
    {
        $this->info('Starting Report');

        $url = config('firetower.server_url') . '/api/report/' . config('firetower.account_key') . '/' . config('firetower.application_key');

        $requiredChecks = [
            ApplicationInfoCheck::check(),
        ];

        $data = collect(Firetower::getChecks())
            ->merge($requiredChecks)
            ->map(function ($check) {
                $check->report();

                return [
                    'name' => $check->name,
                    'description' => $check->description,
                    'class' => get_class($check),
                    'data' => property_exists($check, 'data') ? json_encode($check->data) : null,
                    'message' => $check->message,
                    'is_ok' => $check->ok,
                    'notify' => $check->notify_on_failure,
                ];
            })
            ->toArray();

        $this->line('Sending Data');

        $response = Http::post($url, $data);

        if ($response->successful()) {
            $this->comment('Report Complete');

            return self::SUCCESS;
        }

        $this->error('Report Failed');
        $this->line('');

        if ($response->json() && array_key_exists('message', $response->json())) {
            $this->error($response->json()['message']);
        } else {
            $this->error($response->body());
        }

        return self::FAILURE;
    }
}
