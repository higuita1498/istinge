<?php

namespace App\Jobs;

use App\Mail\NominaPagada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PagarNomina implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $nominas;
    public $pdfs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($nominas, $pdfs)
    {
        $this->nominas = $nominas;
        $this->pdfs = $pdfs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        $personas = $this->nominas->map(function ($nomina) {
            return $nomina->persona;
        });

        $correoPersonas = $personas->map(function ($persona) {
            return $persona->correo;
        });

        $empresa = $this->nominas->map(function ($nomina) {
            return $nomina->empresa;
        })->first();

        $subject = "Tu pago de nómina ha sido confirmado en {$empresa->nombre}";

        foreach ($this->nominas as $i => $nomina) {

            Mail::to($correoPersonas[$i])
                ->queue(new NominaPagada($subject, $nomina, $empresa, $this->pdfs[$i], $personas[$i]));
        }
    }
}
