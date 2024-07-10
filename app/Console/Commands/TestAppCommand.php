<?php

namespace App\Console\Commands;

use App\Jobs\ImportVideoJob;
use App\Services\HlsConverterService;
use App\Services\ImportVideoService;
use App\Services\TranscodeVideoService;
use Exception;
use Illuminate\Console\Command;

class TestAppCommand extends Command
{
    protected $signature = 'test:app';

    protected $description = 'Test app command';

    public function handle(): int
    {
        try {
            $this->info("Starting test");
            $this->newLine();

            // TODO: test the full import process with one video

//            $file = '/content/Sasha Grey/Sasha Grey & Rachel Roxxx Neighbor Affair scene1573 24.July.2007 nafsasharachel_2k.wmv';
//            $srv = new ImportVideoService();
//            $srv->execute([
//                'hash' => md5($file),
//                'file' => $file,
//            ]);
//
//            ImportVideoJob::dispatch([
//                'hash' => md5($file),
//                'file' => $file,
//            ])
//            ->onQueue('media');
//
//            $mediaId = 1;
//            $srv = new TranscodeVideoService();
//            $srv->execute($mediaId);
//
//            $srv = app(HlsConverterService::class);
//            $srv->execute(2);

            $this->newLine();
            $this->info("Done");

            return 0;
        } catch (Exception $e) {
            $this->newLine();
            $this->warn("Error found");
            $this->error($e->getMessage());
            $this->newLine();

            return 1;
        }
    }
}
