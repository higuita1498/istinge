<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ArchivoEnviado extends Mailable
{
    public $rutaArchivo;

    public function __construct($rutaArchivo)
    {
        $this->rutaArchivo = $rutaArchivo;
    }

    public function build()
    {
        return $this->view('emails.archivo-enviado')
                    ->attach(storage_path('app/' . $this->rutaArchivo));
    }
}
