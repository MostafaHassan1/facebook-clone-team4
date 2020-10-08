<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RestPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $first_name,$code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($first_name,$code)
    {
        $this->first_name=$first_name;
        $this->code=$code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('RestPassword');
    }
}