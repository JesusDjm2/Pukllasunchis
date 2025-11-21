<?php

namespace App\Http\Controllers;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Web\WebDriver;

class BotManController extends Controller
{
    public function handle()
    {
        DriverManager::loadDriver(WebDriver::class);
        $botman = BotManFactory::create([]);

        $this->registerBotResponses($botman);

        $botman->listen();
    }
    protected function registerBotResponses(BotMan $botman)
    {
        $botman->hears('hola', function (BotMan $bot) {
            $bot->reply('👋 ¡Hola! Bienvenido a la EESP Pukllasunchis. ¿Sobre qué tema deseas información?');
        });

        $botman->hears('carreras|programas', function (BotMan $bot) {
            $bot->reply('🎓 Ofrecemos las carreras de Administración, Contabilidad y Educación.');
        });

        $botman->hears('horario|atención', function (BotMan $bot) {
            $bot->reply('🕓 Nuestro horario de atención es de lunes a viernes, de 8:00 a.m. a 6:00 p.m.');
        });

        $botman->hears('admisión|inscripción', function (BotMan $bot) {
            $bot->reply('📅 El proceso de admisión está abierto hasta el 15 de diciembre. Puedes registrarte en nuestro portal.');
        });

        $botman->hears('contacto|ubicación', function (BotMan $bot) {
            $bot->reply('📍 Estamos en Av. Los Olivos 123, Huancayo. Teléfono: (064) 123456.');
        });

        $botman->fallback(function (BotMan $bot) {
            $bot->reply('🤔 No tengo esa información. Puedes preguntar por "carreras", "horario", o "admisión".');
        });
    }
}
