<?php
namespace App\Services\RedPuklla;
class ContenidoRed {
 public const CATEGORIAS=['yachay'=>['Yachay','Compartir saberes','Conocimientos, recursos y experiencias para compartir.'],'yuyay'=>['Yuyay','Pensar juntos','Preguntas y reflexiones para comprender juntos.'],'ruray'=>['Ruray','Hacer juntos','Acciones, materiales y proyectos para cocrear.'],'munay'=>['Munay','Cuidarnos y acompañarnos','Reconocimiento y cuidado comunitario. El apoyo sensible se solicita en privado.'],'rimay'=>['Rimay','Conversar y encontrarnos','Diálogos y encuentros para fortalecer vínculos.'],'pukllay'=>['Pukllay','Explorar y crear','Invitaciones a experimentar, jugar e imaginar.']];
 public const INTENCIONES=['experiencia'=>'Compartir una experiencia','pregunta'=>'Plantear una pregunta','colaboracion'=>'Pedir colaboración','ofrecer'=>'Ofrecer ayuda','encuentro'=>'Proponer un encuentro'];
 public const ACCIONES=['experiencia'=>'Contar cómo lo utilicé','pregunta'=>'Aportar a la conversación','colaboracion'=>'Ofrecer mi apoyo','ofrecer'=>'Sumarme a trabajar','encuentro'=>'Quiero participar'];
 public static function estados(?string $intencion):array{return match($intencion){'experiencia'=>['ofrecido'=>'Quiero ponerlo en práctica','en_curso'=>'Lo estoy poniendo en práctica','realizado'=>'Comparto lo aprendido al utilizarlo','retirado'=>'Por ahora no continuaré'],'pregunta'=>['ofrecido'=>'Quiero aportar','en_curso'=>'Estoy preparando un aporte','realizado'=>'Ya compartí mi aporte','retirado'=>'Por ahora no podré aportar'],'encuentro'=>['ofrecido'=>'Quiero participar','en_curso'=>'Estoy ayudando a organizar','realizado'=>'Participé en el encuentro','retirado'=>'No podré participar'],default=>['ofrecido'=>'Me ofrezco a participar','en_curso'=>'Estoy colaborando','realizado'=>'Comparto lo realizado','retirado'=>'Por ahora no puedo continuar']};}
 public static function elegible(?string $condicion,array $roles,bool $gestion=false):bool{return $condicion==='Egresado'||$gestion||(bool)array_intersect($roles,['egresado','docente','tutor','admin','super-admin','adminB','secretaria','gestor-titulacion','direccion','bienestar']);}
 public static function enlace(?string $url):?array {
  if(!$url)return null;
  if(strlen($url)>500||!filter_var($url,FILTER_VALIDATE_URL))throw new \InvalidArgumentException('Utiliza un enlace web válido.');
  $p=parse_url($url);$host=strtolower($p['host']??'');$path=$p['path']??'';
  if(!in_array(strtolower($p['scheme']??''),['http','https'])||isset($p['user'])||isset($p['pass'])||isset($p['port'])||!str_contains($host,'.')||filter_var($host,FILTER_VALIDATE_IP))throw new \InvalidArgumentException('Solo enlaces web sin credenciales ni direcciones internas.');
  $out=['url'=>$url,'proveedor'=>'enlace','embed'=>null];
  if(in_array($host,['youtube.com','www.youtube.com','m.youtube.com','youtu.be'])){parse_str($p['query']??'',$query);$id=null;if($host==='youtu.be')$id=trim($path,'/');elseif(preg_match('~^/(?:shorts|embed)/([A-Za-z0-9_-]{11})/?$~',$path,$m))$id=$m[1];elseif($path==='/watch')$id=$query['v']??null;if(is_string($id)&&preg_match('/^[A-Za-z0-9_-]{11}$/D',$id)){$out['proveedor']='youtube';$out['embed']='https://www.youtube-nocookie.com/embed/'.$id;}}
  elseif(in_array($host,['instagram.com','www.instagram.com'])&&preg_match('~^/(p|reel)/([A-Za-z0-9_-]+)/?$~',$path,$m)){$out['proveedor']='instagram';$out['url']='https://www.instagram.com/'.$m[1].'/'.$m[2].'/';}
  return $out;
 }
}
