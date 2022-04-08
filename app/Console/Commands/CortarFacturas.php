<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

use App\Contacto;
use App\Empresa;
use App\Contrato;
use App\CRM;
use App\Mikrotik;
include_once(app_path() .'/../public/routeros_api.class.php');
use RouterosAPI;

class CortarFacturas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturas:cortar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corte de Facturación';

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
        $i=0;
        $fecha = date('Y-m-d');

        $contactos = Contacto::join('factura as f','f.cliente','=','contactos.id')->
            join('contracts as cs','cs.client_id','=','contactos.id')->
            select('contactos.id', 'contactos.nombre', 'contactos.nit', 'f.id as factura', 'f.estatus', 'f.suspension', 'cs.state')->
            where('f.estatus',1)->
            whereIn('f.tipo', [1,2])->
            where('f.vencimiento', $fecha)->
            where('contactos.status',1)->
            where('cs.state','enabled')->
            take(25)->
            get();

        //dd($contactos);

        $empresa = Empresa::find(1);
        foreach ($contactos as $contacto) {
            $contrato = Contrato::where('client_id', $contacto->id)->first();

            $crm = CRM::where('cliente', $contacto->id)->whereIn('estado', [0, 3])->delete();
            $crm = new CRM();
            $crm->cliente = $contacto->id;
            $crm->factura = $contacto->factura;
            $crm->servidor = $contrato->server_configuration_id;
            $crm->grupo_corte = $contrato->grupo_corte;
            $crm->save();

            $mikrotik = Mikrotik::where('id', $contrato->server_configuration_id)->first();

            $API = new RouterosAPI();
            $API->port = $mikrotik->puerto_api;

            if ($contrato) {
                if ($API->connect($mikrotik->ip,$mikrotik->usuario,$mikrotik->clave)) {
                    $API->write('/ip/firewall/address-list/print', TRUE);
                    $ARRAYS = $API->read();
                    if($contrato->state == 'enabled'){
                        $API->comm("/ip/firewall/address-list/add", array(
                            "address" => $contrato->ip,
                            "comment" => $contrato->servicio,
                            "list" => 'morosos'
                            )
                        );

                        #ELIMINAMOS DE IP_AUTORIZADAS#
                        $API->write('/ip/firewall/address-list/print', false);
                        $API->write('?address='.$contrato->ip, false);
                        $API->write("?list=ips_autorizadas",false);
                        $API->write('=.proplist=.id');
                        $ARRAYS = $API->read();

                        if(count($ARRAYS)>0){
                            $API->write('/ip/firewall/address-list/remove', false);
                            $API->write('=.id='.$ARRAYS[0]['.id']);
                            $READ = $API->read();
                        }
                        #ELIMINAMOS DE IP_AUTORIZADAS#

                        $contrato->state = 'disabled';
                        $i++;
                    }
                    $API->disconnect();
                    $contrato->save();
                }
            }
        }
        if (file_exists("CorteFacturas.txt")){
            $file = fopen("CorteFacturas.txt", "a");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Corte: ".date('Y-m-d').''. PHP_EOL);
            fputs($file, "Contratos Deshabilitados: ".$i.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }else{
            $file = fopen("CorteFacturas.txt", "w");
            fputs($file, "-----------------".PHP_EOL);
            fputs($file, "Fecha de Corte: ".date('Y-m-d').''. PHP_EOL);
            fputs($file, "Contratos Deshabilitados: ".$i.''. PHP_EOL);
            fputs($file, "-----------------".PHP_EOL);
            fclose($file);
        }
    }
}
