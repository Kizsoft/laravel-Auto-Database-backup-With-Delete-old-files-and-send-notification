<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackupDatabase extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //call it with php artisan backup:db to run the code
    protected $signature = 'backup:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        //calling it from the config we added before
        $this->host = config('app.db_host');
        $this->user = config('app.db_username');
        $this->pass = config('app.db_password');
        $this->dbname = config('app.db_database');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        //code that will handle the backup
        try {
            //path directory to store the file
            $storePath = storage_path() . '/app/backup';
            //filename , name of the file 
            $filename = 'backup' . Carbon::now()->format('Y-m-d') . '.sql';

            //check if the path directory exist and skip if not create it before procceeding
            if (!is_dir(dirname($storePath))) {
                //we create fuction to create the path
                static::createPathDiretory($storePath);
            }
            //handling the backup
            $command = "mysqldump --user=" . $this->user . " --password=" . $this->pass . " --host=" . $this->host . " " . $this->dbname . " > " . $storePath . $filename;
            $returnVar = null;
            $output = null;
            exec($command, $output, $returnVar);

            //adding function to delete old files after successfully backup of new files 
            $time = 345600; //time is in seconds eg 4 days = 345600 , delete file after 4 days
            //function to handle delete file
            static::deleteOldFiles($storePath, $time);
            //send notification after successful backup
            //send notification to email using your already mailling functions
        } catch (Exception $ex) {
            //backup fails 
            //send notification to email using your already mailling functions
        }
    }

    public static function createPathDiretory($path_dir, $abs = true) {

        if (!is_dir($path_dir)) {
            //create the path with permission to read and write
            mkdir($path_dir, 0777, true);
        }
        return ($abs ? $path_dir : $path_dir);
    }

    public static function deleteOldFiles($storePath, $time) {
        //loop into the directory and put all the files in foreach to check
        foreach (glob($storePath . "*") as $file) {
            //check the file that its time is more than 4 days depends on your own time
            if (time() - filectime($file) > $time) {
                unlink($file);
            }
        }
    }

}
