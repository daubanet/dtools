<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class downloadZipFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zip:download';
    private $ftp;

    private $societes  = [
        '01' => 'LABNAT',
        '02' => 'BBI',
        '03' => 'SPCONFORT'
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Télécharches les Fichiers ZIP  depuis le FP';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->ftp = new \FtpClient\FtpClient();
        try {

            $this->ftp->connect(env('COLISSIMO_FTP_HOST') , env('COLISSIMO_FTP_USE_SSL'), env('COLISSIMO_FTP_PORT'));
            $this->ftp->login(env('COLISSIMO_FTP_USER'), env('COLISSIMO_FTP_PASSWORD'));

        } catch (FtpClient\FtpException $e) {

            Log::error($e->getMessage());
        }
    }

    /**
     * Vérifie si le fichier corresponds a un ZIP INVOICE.
     *
     * @return boolean
     */
    public function is_invoice_zip($filename){
        $exploded = explode('.', $filename);
        $first_part = $exploded[0];
        $extention = $exploded[count($exploded) -1];
        if ($first_part == 'INVOIC' AND strtolower($extention) == 'zip') {
            return true;
        } else {
            return false;
        }
    }




    /**
     * Retourne la liste des fichiers ZIP
     *@param societe LABNAT|BBI|SPCONFORT
     *
     * @return array
     */
    public function getFilesZip($societe) {


        $fichiers_zip_collection = $this->ftp->scanDir( env('COLISSIMO_FTP_FOLDER_'.$societe));

        $data = [];  // tableau pour le retour de la liste des ZIP

        foreach($fichiers_zip_collection as $file){

            if ($this->is_invoice_zip($file['name'])){

                $data[] = $file['name'];
            }
        }
        $this->output->title("Téléchargement des ZIP Factures colissimos pour " .$societe . ' ('. count($data) .' fichiers) sur ' . env('COLISSIMO_FTP_HOST'));
        return $data;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // assure la présence de la structure de répertoires d'acceuil

        if(!Storage::exists('colissimos') ) {
            Storage::makeDirectory('colissimos');
        }

        foreach($this->societes as $societe){


            $filelist = $this->getFilesZip($societe);

            foreach($filelist as $filename){

                if(!Storage::exists( env('COLISSIMO_FOLDER_' . $societe )) ) {
                    Storage::makeDirectory( env('COLISSIMO_FOLDER_' . $societe ));
                }

                $origine = env('COLISSIMO_FTP_FOLDER_' . $societe ) . '/' . $filename;
                $destination = storage_path('app/' . env('COLISSIMO_FOLDER_' . $societe )) . '/' . $filename;

                if ($this->ftp->get($destination, $origine, FTP_BINARY)) {

                    $this->info("[FTP]: " . $origine . " => " . $destination);
                    Log::info("Téléchargement du fichier ZIP: " . $filename . " => " . $destination);

                } else {
                    $this->error("[FTP]: " . $origine . " => " . $destination);
                    Log::error("Téléchargement du fichier ZIP: " . $filename . " => " . $destination);
                }
            }
        }
    }
}
