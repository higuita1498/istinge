<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
use Auth;

class RadicadosMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;

    public $datos;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datos)
    {
        $this->datos = $datos;
        $this->subject = Auth::user()->empresa()->nombre.': Reporte de Radicado';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $datos = $this->datos;
        return $this->view('emails.radicado')->with('datos');
    }
}
