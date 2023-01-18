<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificacionMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;

    public $datos;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datos){
        $this->datos   = $datos;
        $this->subject = $datos['titulo'];
        $this->archivo = $datos['archivo'];
        $this->name    = $datos['cliente'];
        $this->company = $datos['empresa'];
        $this->nit     = $datos['nit'];
        $this->date    = $datos['date'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(){
        $name = $this->name;
        $company = $this->company;
        $nit = $this->nit;
        $date = $this->date;

        return $this->view('emails.plantillas.'.$this->archivo, compact('name', 'company', 'nit', 'date'));
    }
}
