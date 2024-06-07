<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearViolationPdf extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ClearViolationPdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear violation "PDF" before 1 month (public/uploads/pdf/).';

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
        $path = public_path("/uploads/pdf/");
        
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $file_parts = pathinfo($file);
                if ($file_parts['extension'] == "pdf") {
                    $filelastmodified = filemtime($path . $file);
                    //24 hours in a day * 3600 seconds per hour
                    if ((time() - $filelastmodified) > 24 * 3600) {
                        unlink($path . $file);
                    }
                }
            }
            closedir($handle);
        }
    }
}
