<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NominaLiquidada extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $nomina;
    public $empresa;
    public $pdf;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nomina, $empresa, $pdf)
    {
        $this->subject = "Tu pago de nómina ha sido confirmado en {$empresa->nombre}";
        $this->nomina = $nomina;
        $this->empresa = $empresa;
        $this->pdf = $pdf;
        $this->persona = $nomina->persona;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->from($this->empresa->email, $this->empresa->nombre)
            ->view('emails.nomina-liquidada')
            ->attachFromStorageDisk('public',$this->pdf);
    }
}
