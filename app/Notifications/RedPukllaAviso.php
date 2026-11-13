<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
class RedPukllaAviso extends Notification {
 public function __construct(public string $mensaje,public string $tipo,public int $referencia){}
 public function via($notifiable):array{return ['database'];}
 public function toArray($notifiable):array{return ['mensaje'=>$this->mensaje,'resumen'=>$this->mensaje,'tipo'=>$this->tipo,'referencia'=>$this->referencia,'url'=>route($this->tipo==='grupo'?'red.grupo':($this->tipo==='oportunidad'?'red.oportunidad':'red.publicacion'),['id'=>$this->referencia])];}
}
