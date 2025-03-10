<?php

namespace App\Http\Controllers;

use App\Models\PostulantesRegular;
use Illuminate\Http\Request;
use App\Exports\PostulantesRegularExport;
use Maatwebsite\Excel\Facades\Excel;

class PostulantesRegularController extends Controller
{
    public function form()
    {
        $departamentos = [
            ["id" => 1, "nombre" => "Apurímac"],
            ["id" => 2, "nombre" => "Ayacucho"],
            ["id" => 3, "nombre" => "Cusco"],
            ["id" => 4, "nombre" => "Madre de Dios"],
            ["id" => 5, "nombre" => "Puno"],
        ];
        $provincias = [
            1 => [
                ['id' => 1, 'nombre' => 'Abancay'],
                ['id' => 2, 'nombre' => 'Andahuaylas'],
                ['id' => 3, 'nombre' => 'Antabamba'],
                ['id' => 4, 'nombre' => 'Aymaraes'],
                ['id' => 5, 'nombre' => 'Chincheros'],
                ['id' => 6, 'nombre' => 'Cotabambas'],
                ['id' => 7, 'nombre' => 'Grau'],
            ],
            2 => [ // Ayacucho
                ['id' => 8, 'nombre' => 'Huamanga'],
                ['id' => 9, 'nombre' => 'Cangallo'],
                ['id' => 10, 'nombre' => 'Huanca Sancos'],
                ['id' => 11, 'nombre' => 'Huanta'],
                ['id' => 12, 'nombre' => 'La Mar'],
                ['id' => 13, 'nombre' => 'Lucanas'],
                ['id' => 14, 'nombre' => 'Parinacochas'],
                ['id' => 15, 'nombre' => 'Páucar del Sara Sara'],
                ['id' => 16, 'nombre' => 'Sucre'],
                ['id' => 17, 'nombre' => 'Víctor Fajardo'],
                ['id' => 18, 'nombre' => 'Vilcas Huamán'],
            ],
            3 => [ // Cusco
                ["id" => 19, "nombre" => "Cusco"],
                ["id" => 20, "nombre" => "Acomayo"],
                ["id" => 21, "nombre" => "Anta"],
                ["id" => 22, "nombre" => "Calca"],
                ["id" => 23, "nombre" => "Canas"],
                ["id" => 24, "nombre" => "Canchis"],
                ["id" => 25, "nombre" => "Chumbivilcas"],
                ["id" => 26, "nombre" => "Espinar"],
                ["id" => 27, "nombre" => "La Convención"],
                ["id" => 28, "nombre" => "Paruro"],
                ["id" => 29, "nombre" => "Paucartambo"],
                ["id" => 30, "nombre" => "Quispicanchi"],
                ["id" => 31, "nombre" => "Urubamba"],
            ],
            4 => [ // Madre de Dios
                ["id" => 32, "nombre" => "Tambopata"],
                ["id" => 33, "nombre" => "Manu"],
                ["id" => 34, "nombre" => "Tahuamanu"],
            ],
            5 => [ // Puno
                ["id" => 35, "nombre" => "Puno"],
                ["id" => 36, "nombre" => "Azángaro"],
                ["id" => 37, "nombre" => "Carabaya"],
                ["id" => 38, "nombre" => "Chucuito"],
                ["id" => 39, "nombre" => "El Collao"],
                ["id" => 40, "nombre" => "Huancané"],
                ["id" => 41, "nombre" => "Lampa"],
                ["id" => 42, "nombre" => "Melgar"],
                ["id" => 43, "nombre" => "San Antonio de Putina"],
                ["id" => 44, "nombre" => "San Román"],
                ["id" => 45, "nombre" => "Yunguyo"],
            ],
        ];
        $distritos = [
            1 => [ // Abancay (Apurímac)
                ['id' => 1, 'nombre' => 'Abancay'],
                ['id' => 2, 'nombre' => 'Ccapacmarca'],
                ['id' => 3, 'nombre' => 'Chacoche'],
                ['id' => 4, 'nombre' => 'Circa'],
                ['id' => 5, 'nombre' => 'Curahuasi'],
                ['id' => 6, 'nombre' => 'Huanipaca'],
                ['id' => 7, 'nombre' => 'Lambrama'],
                ['id' => 8, 'nombre' => 'Pichirhua'],
            ],
            2 => [ // Andahuaylas (Apurímac)
                ['id' => 9, 'nombre' => 'Andahuaylas'],
                ['id' => 10, 'nombre' => 'Chiara'],
                ['id' => 11, 'nombre' => 'Huaquirca'],
                ['id' => 12, 'nombre' => 'Kishuar'],
                ['id' => 13, 'nombre' => 'Pampachiri'],
                ['id' => 14, 'nombre' => 'San Jerónimo'],
                ['id' => 15, 'nombre' => 'San José de Pisca'],
                ['id' => 16, 'nombre' => 'San Pedro de Andahuaylas'],
                ['id' => 17, 'nombre' => 'Tantarica'],
            ],
            3 => [ // Antabamba (Apurímac)
                ['id' => 18, 'nombre' => 'Antabamba'],
                ['id' => 19, 'nombre' => 'El Oro'],
                ['id' => 20, 'nombre' => 'Huaquillas'],
                ['id' => 21, 'nombre' => 'Pampamarca'],
                ['id' => 22, 'nombre' => 'Sabaino'],
                ['id' => 23, 'nombre' => 'Santiago de Antabamba'],
                ['id' => 24, 'nombre' => 'Tambo'],
            ],
            4 => [ // Aymaraes (Apurímac)
                ['id' => 25, 'nombre' => 'Aymaraes'],
                ['id' => 26, 'nombre' => 'Chalhuanca'],
                ['id' => 27, 'nombre' => 'Colcabamba'],
                ['id' => 28, 'nombre' => 'Domingo Huamaní'],
                ['id' => 29, 'nombre' => 'Juan Espinoza Medrano'],
                ['id' => 30, 'nombre' => 'La Conveniencia'],
                ['id' => 31, 'nombre' => 'Pampa Grande'],
                ['id' => 32, 'nombre' => 'San Juan de Chacña'],
            ],
            5 => [ // Chincheros (Apurímac)
                ['id' => 33, 'nombre' => 'Chincheros'],
                ['id' => 34, 'nombre' => 'Cocharcas'],
                ['id' => 35, 'nombre' => 'Huaccana'],
                ['id' => 36, 'nombre' => 'Oropesa'],
                ['id' => 37, 'nombre' => 'Uripa'],
                ['id' => 38, 'nombre' => 'Virundo'],
            ],
            6 => [ // Cotabambas (Apurímac)
                ['id' => 39, 'nombre' => 'Cotabambas'],
                ['id' => 40, 'nombre' => 'Challhuahuacho'],
                ['id' => 41, 'nombre' => 'Haquira'],
                ['id' => 42, 'nombre' => 'Turpay'],
                ['id' => 43, 'nombre' => 'Coyllurqui'],
            ],
            7 => [ // Grau (Apurímac)
                ['id' => 44, 'nombre' => 'Grau'],
                ['id' => 45, 'nombre' => 'Chuquibambilla'],
                ['id' => 46, 'nombre' => 'Santa Rosa'],
                ['id' => 47, 'nombre' => 'Pampamarca'],
                ['id' => 48, 'nombre' => 'Pichirhua'],
            ],
            8 => [ // Huamanga (Ayacucho)
                ['id' => 49, 'nombre' => 'Huamanga'],
                ['id' => 50, 'nombre' => 'Cangallo'],
                ['id' => 51, 'nombre' => 'Huanca Sancos'],
                ['id' => 52, 'nombre' => 'Huanta'],
                ['id' => 53, 'nombre' => 'La Mar'],
                ['id' => 54, 'nombre' => 'Lucanas'],
                ['id' => 55, 'nombre' => 'Parinacochas'],
                ['id' => 56, 'nombre' => 'Páucar del Sara Sara'],
                ['id' => 57, 'nombre' => 'Sucre'],
                ['id' => 58, 'nombre' => 'Víctor Fajardo'],
                ['id' => 59, 'nombre' => 'Vilcas Huamán'],
            ],
            9 => [ // Cangallo (Ayacucho)
                ['id' => 60, 'nombre' => 'Cangallo'],
                ['id' => 61, 'nombre' => 'Chuschi'],
                ['id' => 62, 'nombre' => 'Los Morochucos'],
                ['id' => 63, 'nombre' => 'Tambo'],
                ['id' => 64, 'nombre' => 'Soledad'],
            ],
            10 => [ // Huanca Sancos (Ayacucho)
                ['id' => 65, 'nombre' => 'Huanca Sancos'],
                ['id' => 66, 'nombre' => 'Sancos'],
                ['id' => 67, 'nombre' => 'Sarhua'],
                ['id' => 68, 'nombre' => 'Tambo'],
            ],
            11 => [ // Huanta (Ayacucho)
                ['id' => 69, 'nombre' => 'Huanta'],
                ['id' => 70, 'nombre' => 'Ayahuanco'],
                ['id' => 71, 'nombre' => 'Chaca'],
                ['id' => 72, 'nombre' => 'Iguain'],
                ['id' => 73, 'nombre' => 'La Mar'],
                ['id' => 74, 'nombre' => 'San José de Ushua'],
            ],
            12 => [ // La Mar (Ayacucho)
                ['id' => 75, 'nombre' => 'San José de los Molinos'],
                ['id' => 76, 'nombre' => 'Anco'],
                ['id' => 77, 'nombre' => 'Chilca'],
                ['id' => 78, 'nombre' => 'Cangallo'],
                ['id' => 79, 'nombre' => 'Pacaycasa'],
            ],
            13 => [ // Lucanas (Ayacucho)
                ['id' => 80, 'nombre' => 'Puquio'],
                ['id' => 81, 'nombre' => 'Chaviña'],
                ['id' => 82, 'nombre' => 'Sancos'],
                ['id' => 83, 'nombre' => 'Socos'],
                ['id' => 84, 'nombre' => 'Carmen Alto'],
                ['id' => 85, 'nombre' => 'Siria'],
            ],
            14 => [ // Parinacochas (Ayacucho)
                ['id' => 86, 'nombre' => 'Coracora'],
                ['id' => 87, 'nombre' => 'Ayacucho'],
                ['id' => 88, 'nombre' => 'Chumpi'],
                ['id' => 89, 'nombre' => 'San Juan de Churubamba'],
                ['id' => 90, 'nombre' => 'San José de los Molinos'],
                ['id' => 91, 'nombre' => 'Santillana'],
            ],
            15 => [ // Páucar del Sara Sara (Ayacucho)
                ['id' => 92, 'nombre' => 'Sara Sara'],
                ['id' => 93, 'nombre' => 'Lucanas'],
                ['id' => 94, 'nombre' => 'Pucara'],
                ['id' => 95, 'nombre' => 'Cabana'],
                ['id' => 96, 'nombre' => 'Acos'],
                ['id' => 97, 'nombre' => 'Santillana'],
            ],
            16 => [ // Sucre (Ayacucho)
                ['id' => 98, 'nombre' => 'Sucre'],
                ['id' => 99, 'nombre' => 'Rumi Cruz'],
                ['id' => 100, 'nombre' => 'San Juan'],
                ['id' => 101, 'nombre' => 'San Salvador'],
                ['id' => 102, 'nombre' => 'Santa Rosa'],
                ['id' => 103, 'nombre' => 'Huaquilla'],
            ],
            17 => [ // Víctor Fajardo (Ayacucho)
                ['id' => 104, 'nombre' => 'Victor Fajardo'],
                ['id' => 105, 'nombre' => 'Ayahuanco'],
                ['id' => 106, 'nombre' => 'Huañahui'],
                ['id' => 107, 'nombre' => 'San José de Ushua'],
                ['id' => 108, 'nombre' => 'San Juan de la Esperanza'],
            ],
            18 => [ // Vilcas Huamán (Ayacucho)
                ['id' => 109, 'nombre' => 'Vilcas Huamán'],
                ['id' => 110, 'nombre' => 'Vítor'],
                ['id' => 111, 'nombre' => 'Huanca'],
                ['id' => 112, 'nombre' => 'Aco'],
                ['id' => 113, 'nombre' => 'Carhuanca'],
            ],
            19 => [ // Cusco (Cusco)
                ['id' => 114, 'nombre' => 'Cusco'],
                ['id' => 115, 'nombre' => 'San Sebastián'],
                ['id' => 116, 'nombre' => 'San Jerónimo'],
                ['id' => 117, 'nombre' => 'Santiago'],
                ['id' => 118, 'nombre' => 'Ccorca'],
                ['id' => 119, 'nombre' => 'Saylla'],
                ['id' => 120, 'nombre' => 'Poroy'],
                ['id' => 121, 'nombre' => 'Wanchaq'],
            ],
            20 => [ // Acomayo (Cusco)
                ['id' => 122, 'nombre' => 'Acomayo'],
                ['id' => 123, 'nombre' => 'Acopia'],
                ['id' => 124, 'nombre' => 'Mosoc Llacta'],
                ['id' => 125, 'nombre' => 'Pomacanchi'],
                ['id' => 126, 'nombre' => 'Rondocan'],
                ['id' => 127, 'nombre' => 'Acos'],
                ['id' => 128, 'nombre' => 'Sangarará'],
            ],

            21 => [ // Anta (Cusco)
                ['id' => 129, 'nombre' => 'Anta'],
                ['id' => 130, 'nombre' => 'Huarocondo'],
                ['id' => 131, 'nombre' => 'Mollepata'],
                ['id' => 132, 'nombre' => 'Pucyura'],
                ['id' => 133, 'nombre' => 'Zurite'],
                ['id' => 134, 'nombre' => 'Ancahuasi'],
                ['id' => 135, 'nombre' => 'Cachimayo'],
                ['id' => 136, 'nombre' => 'Chinchaypujio'],
                ['id' => 137, 'nombre' => 'Huarocondo'],
                ['id' => 138, 'nombre' => 'Limatambo'],
            ],
            22 => [ // Calca (Cusco)
                ['id' => 139, 'nombre' => 'Calca'],
                ['id' => 140, 'nombre' => 'Coya'],
                ['id' => 141, 'nombre' => 'Lamay'],
                ['id' => 142, 'nombre' => 'Lares'],
                ['id' => 143, 'nombre' => 'Pisac'],
                ['id' => 144, 'nombre' => 'San Salvador'],
                ['id' => 145, 'nombre' => 'Taray'],
                ['id' => 146, 'nombre' => 'Yanatile'],
            ],
            23 => [ // Canas (Cusco)
                ['id' => 147, 'nombre' => 'Yanaoca'],
                ['id' => 148, 'nombre' => 'Checca'],
                ['id' => 149, 'nombre' => 'Kunturkanka'],
                ['id' => 150, 'nombre' => 'Langui'],
                ['id' => 151, 'nombre' => 'Layo'],
                ['id' => 152, 'nombre' => 'Pampamarca'],
                ['id' => 153, 'nombre' => 'Quehue'],
                ['id' => 154, 'nombre' => 'Túpac Amaru'],
            ],
            24 => [ // Canchis (Cusco)
                ['id' => 155, 'nombre' => 'Sicuani'],
                ['id' => 156, 'nombre' => 'Checacupe'],
                ['id' => 157, 'nombre' => 'Marangani'],
                ['id' => 158, 'nombre' => 'Pitumarca'],
                ['id' => 159, 'nombre' => 'San Pablo'],
                ['id' => 160, 'nombre' => 'San Pedro'],
                ['id' => 161, 'nombre' => 'Combapata'],
                ['id' => 162, 'nombre' => 'Tinta'],
            ],
            25 => [ // Chumbivilcas (Cusco)
                ['id' => 163, 'nombre' => 'Santo Tomás'],
                ['id' => 164, 'nombre' => 'Capaya'],
                ['id' => 165, 'nombre' => 'Colquemarca'],
                ['id' => 166, 'nombre' => 'Chamaca'],
                ['id' => 167, 'nombre' => 'Llusco'],
                ['id' => 168, 'nombre' => 'Velille'],
                ['id' => 169, 'nombre' => 'Capacmarca'],
                ['id' => 170, 'nombre' => 'Livitaca'],
                ['id' => 171, 'nombre' => 'Quiñota'],
            ],
            26 => [ // Espinar (Cusco)
                ['id' => 172, 'nombre' => 'Condoroma'],
                ['id' => 173, 'nombre' => 'Coporaque'],
                ['id' => 174, 'nombre' => 'Ocoruro'],
                ['id' => 175, 'nombre' => 'Pallpata'],
                ['id' => 176, 'nombre' => 'Pichigua'],
                ['id' => 177, 'nombre' => 'Suyckutambo'],
                ['id' => 178, 'nombre' => 'Alto Pichigua'],
                ['id' => 179, 'nombre' => 'Yauri'],
            ],
            27 => [ // La Convención (Cusco)
                ['id' => 180, 'nombre' => 'Santa Teresa'],
                ['id' => 181, 'nombre' => 'Echarate'],
                ['id' => 182, 'nombre' => 'Quellouno'],
                ['id' => 183, 'nombre' => 'Vilcabamba'],
                ['id' => 184, 'nombre' => 'Huayopata'],
                ['id' => 185, 'nombre' => 'Inkawasi'],
                ['id' => 186, 'nombre' => 'Maranura'],
                ['id' => 187, 'nombre' => 'Megantoni'],
                ['id' => 188, 'nombre' => 'Ocobamba'],
                ['id' => 189, 'nombre' => 'Pachari'],
                ['id' => 190, 'nombre' => 'Quimiri'],
                ['id' => 191, 'nombre' => 'Santa Ana'],
                ['id' => 192, 'nombre' => 'Villa Virgen'],
                ['id' => 193, 'nombre' => 'Villa Kintiarina'],
            ],
            28 => [ // Paruro (Cusco)
                ['id' => 194, 'nombre' => 'Paruro'],
                ['id' => 195, 'nombre' => 'Accha'],
                ['id' => 196, 'nombre' => 'Ccapi'],
                ['id' => 197, 'nombre' => 'Colcha'],
                ['id' => 198, 'nombre' => 'Huanoquite'],
                ['id' => 199, 'nombre' => 'Omacha'],
                ['id' => 200, 'nombre' => 'Paccaritambo'],
                ['id' => 201, 'nombre' => 'Pillpinto'],
                ['id' => 202, 'nombre' => 'Yaurisque'],
            ],
            29 => [ // Paucartambo (Cusco)
                ['id' => 203, 'nombre' => 'Paucartambo'],
                ['id' => 204, 'nombre' => 'Caicay'],
                ['id' => 205, 'nombre' => 'Challabamba'],
                ['id' => 206, 'nombre' => 'Kosñipata'],
                ['id' => 207, 'nombre' => 'Colquepata'],
                ['id' => 208, 'nombre' => 'Huancarani'],
            ],
            30 => [ // Quispicanchi (Cusco)
                ['id' => 209, 'nombre' => 'Urcos'],
                ['id' => 210, 'nombre' => 'Ccarhuayo'],
                ['id' => 211, 'nombre' => 'Quiquijana'],
                ['id' => 212, 'nombre' => 'Acomayo'],
                ['id' => 213, 'nombre' => 'Acopia'],
                ['id' => 214, 'nombre' => 'Andahuaylillas'],
                ['id' => 215, 'nombre' => 'Cusipata'],
                ['id' => 216, 'nombre' => 'Oropesa'],
                ['id' => 217, 'nombre' => 'Marangani'],
                ['id' => 218, 'nombre' => 'San Pablo'],
                ['id' => 219, 'nombre' => 'Yanque'],
            ],
            31 => [ // Urubamba (Cusco)
                ['id' => 220, 'nombre' => 'Urubamba'],
                ['id' => 221, 'nombre' => 'Ollantaytambo'],
                ['id' => 222, 'nombre' => 'Yucay'],
                ['id' => 223, 'nombre' => 'Chinchero'],
                ['id' => 224, 'nombre' => 'Huayllabamba'],
                ['id' => 225, 'nombre' => 'Machupicchu'],
                ['id' => 226, 'nombre' => 'Maras'],
            ],
            32 => [ // Tambopata (Madre de Dios)
                ['id' => 227, 'nombre' => 'Tambopata'],
                ['id' => 228, 'nombre' => 'Inambari'],
                ['id' => 229, 'nombre' => 'Las Piedras'],
                ['id' => 230, 'nombre' => 'Puerto Maldonado'],
                ['id' => 231, 'nombre' => 'Río de Los Amigos'],
            ],
            33 => [ // Manu (Madre de Dios)
                ['id' => 232, 'nombre' => 'Manu'],
                ['id' => 233, 'nombre' => 'Fitzcarrald'],
                ['id' => 234, 'nombre' => 'Madre de Dios'],
                ['id' => 235, 'nombre' => 'Huepetue'],
            ],
            34 => [ // Tahuamanu (Madre de Dios)
                ['id' => 236, 'nombre' => 'Iñapari'],
                ['id' => 237, 'nombre' => 'Tahuamanu'],
                ['id' => 238, 'nombre' => 'Iberia'],
            ],
            35 => [ // Puno
                ['id' => 239, 'nombre' => 'Puno'],
                ['id' => 240, 'nombre' => 'Acora'],
                ['id' => 241, 'nombre' => 'Atuncolla'],
                ['id' => 242, 'nombre' => 'Capachica'],
                ['id' => 243, 'nombre' => 'Paucarcolla'],
                ['id' => 244, 'nombre' => 'Pichacani'],
                ['id' => 245, 'nombre' => 'Plateria'],
                ['id' => 246, 'nombre' => 'San Antonio'],
                ['id' => 247, 'nombre' => 'San Juan del Oro'],
                ['id' => 248, 'nombre' => 'San Pedro'],
                ['id' => 249, 'nombre' => 'Santiago de Pupuja'],
                ['id' => 250, 'nombre' => 'Tiquillaca'],
                ['id' => 251, 'nombre' => 'Vilque'],
            ],
            36 => [ // Azángaro
                ['id' => 252, 'nombre' => 'Azángaro'],
                ['id' => 253, 'nombre' => 'Chupa'],
                ['id' => 254, 'nombre' => 'Muñani'],
                ['id' => 255, 'nombre' => 'San Juan de Salinas'],
                ['id' => 256, 'nombre' => 'San José'],
                ['id' => 257, 'nombre' => 'Achaya'],
                ['id' => 258, 'nombre' => 'Arapa'],
                ['id' => 259, 'nombre' => 'Asillo'],
                ['id' => 260, 'nombre' => 'Caminaca'],
                ['id' => 261, 'nombre' => 'Potoni'],
                ['id' => 262, 'nombre' => 'Putina'],
                ['id' => 263, 'nombre' => 'Samán'],
                ['id' => 264, 'nombre' => 'San Antón'],
                ['id' => 265, 'nombre' => 'Santiago de Pupuja'],
                ['id' => 266, 'nombre' => 'Tirapata'],
                ['id' => 267, 'nombre' => 'José Domingo Choquehuanca'],
                ['id' => 268, 'nombre' => 'Pedro Vilcapaza'],
                ['id' => 269, 'nombre' => 'Huatasani'],
            ],
            37 => [ // Carabaya
                ['id' => 270, 'nombre' => 'Carabaya'],
                ['id' => 271, 'nombre' => 'Macusani'],
                ['id' => 272, 'nombre' => 'Ayapata'],
                ['id' => 273, 'nombre' => 'Coasa'],
                ['id' => 274, 'nombre' => 'Corani'],
                ['id' => 275, 'nombre' => 'San Gaban'],
            ],
            38 => [ // Chucuito
                ['id' => 276, 'nombre' => 'Chucuito'],
                ['id' => 277, 'nombre' => 'Desaguadero'],
                ['id' => 278, 'nombre' => 'Juli'],
                ['id' => 279, 'nombre' => 'Juliaca'],
                ['id' => 280, 'nombre' => 'Puno'],
                ['id' => 281, 'nombre' => 'Pomata'],
            ],
            39 => [ // El Collao
                ['id' => 282, 'nombre' => 'Ilave'],
                ['id' => 283, 'nombre' => 'Coporaque'],
                ['id' => 284, 'nombre' => 'Tiquillaca'],
            ],
            40 => [ // Huancané
                ['id' => 285, 'nombre' => 'Huancané'],
                ['id' => 286, 'nombre' => 'Vilque Chico'],
                ['id' => 287, 'nombre' => 'Santa Rosa'],
                ['id' => 288, 'nombre' => 'Chicureo'],
            ],

            41 => [ // Lampa
                ['id' => 289, 'nombre' => 'Lampa'],
                ['id' => 290, 'nombre' => 'Juliaca'],
                ['id' => 291, 'nombre' => 'Taraco'],
            ],
            42 => [ // Melgar
                ['id' => 292, 'nombre' => 'Melgar'],
                ['id' => 293, 'nombre' => 'Antauta'],
                ['id' => 294, 'nombre' => 'Santa Rosa'],
            ],
            43 => [ // San Antonio de Putina
                ['id' => 295, 'nombre' => 'Putina'],
                ['id' => 296, 'nombre' => 'Cuyocuyo'],
                ['id' => 297, 'nombre' => 'Phara'],
                ['id' => 298, 'nombre' => 'San Antón'],
            ],
            44 => [ // San Román
                ['id' => 299, 'nombre' => 'Juliaca'],
                ['id' => 300, 'nombre' => 'San Román'],
                ['id' => 301, 'nombre' => 'Puno'],
                ['id' => 302, 'nombre' => 'San Pedro'],
                ['id' => 303, 'nombre' => 'San Pablo'],
            ],
            45 => [ // Yunguyo
                ['id' => 304, 'nombre' => 'Yunguyo'],
                ['id' => 305, 'nombre' => 'Copacabana'],
                ['id' => 306, 'nombre' => 'Pomata'],
                ['id' => 307, 'nombre' => 'Desaguadero'],
            ],
        ];
        return view('alumnos.postulantes.regulares.formulario', compact('departamentos', 'provincias', 'distritos'));
    }
    public function index()
    {
        $postulantes = PostulantesRegular::all();
        return view('alumnos.postulantes.regulares.index', compact('postulantes'));
    }
    // Mostrar formulario de creación
    public function create()
    {
        return view('alumnos.postulantes.regulares.create');
    }

    public function toggleObservacion($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);

        // Cambiar el estado
        $postulante->observaciones = !$postulante->observaciones;
        $postulante->save();

        return back();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:postulantes_regulars,email', // Validación para el email
            'programa' => 'required|string', // El programa debe ser una cadena no vacía
            'estudio_beca' => 'required|boolean', // Estudio beca debe ser booleano
            'apellidos' => 'required|string|max:255', // Apellidos deben ser una cadena y no exceder 255 caracteres
            'nombres' => 'required|string|max:255', // Nombres deben ser una cadena y no exceder 255 caracteres
            'dni' => 'required|digits:8|unique:postulantes_regulars,dni', // DNI de 8 dígitos y único
            'genero' => 'required|boolean', // El género debe ser booleano
            'direccion' => 'nullable|string|max:255', // Dirección opcional
            'numero' => 'required|numeric|digits_between:7,9', // El número de teléfono debe ser numérico y entre 7 y 9 dígitos
            'fecha_nacimiento' => 'required|date', // La fecha de nacimiento debe ser válida
            'lugar_nacimiento' => 'nullable|string|max:255', // Lugar de nacimiento opcional
            'distrito_nacimiento' => 'nullable|string|max:255', // Distrito de nacimiento opcional
            'provincia_nacimiento' => 'nullable|string|max:255', // Provincia de nacimiento opcional
            'departamento_nacimiento' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',

            'colegio' => 'required|string|max:255', // Nombre del colegio
            'codigo_colegio' => 'required|string|max:50', // Código del colegio
            'gestion_colegio' => 'required|string|in:publico,privado', // Gestión del colegio debe ser público o privado
            'direccion_colegio' => 'nullable|string|max:255', // Dirección del colegio opcional
            'distrito_colegio' => 'nullable|string|max:255', // Distrito del colegio opcional
            'provincia_colegio' => 'nullable|string|max:255', // Provincia del colegio opcional
            'departamento_colegio' => 'nullable|string|max:255', // Departamento del colegio opcional
            'ano_termino_colegio' => 'required|digits:4|integer|min:1900|max:' . date('Y'), // Año de término debe ser de 4 dígitos y válido
            'promedio_colegio' => 'required|numeric|between:0,20', // Promedio debe ser un número entre 0 y 20
            'lengua_1' => 'required|string|max:255', // Primera lengua
            'lengua_2' => 'nullable|string|max:255', // Segunda lengua opcional
            'estado_civil' => 'required|string|in:soltero,casado,divorciado,viudo', // Estado civil debe ser una opción válida
            'num_hijos' => 'required|integer|min:0', // Número de hijos, mínimo 0
            'trabajas' => 'required|boolean', // Trabajas debe ser booleano
            'donde_trabajas' => 'nullable|string|max:255', // Dónde trabajas es opcional
            'cargo_trabajas' => 'nullable|string|max:255', // Cargo en el trabajo es opcional
            'describe_eespp' => 'required|string',

            'dni_pdf' => 'required|file|mimes:pdf|max:4048', // Máx. 2MB
            'partida_nacimiento_pdf' => 'nullable|file|mimes:pdf|max:4048', // Máx. 2MB
            'certificado_secundaria_pdf' => 'required|file|mimes:pdf|max:4048', // Máx. 2MB
            'foto' => 'required|file|mimes:jpg,png|max:4048', // Solo JPG o PNG, máx. 2MB
            'declaracion_jurada_salud_pdf' => 'nullable|file|mimes:pdf|max:3048', // Máx. 2MB
            'declaracion_jurada_documentos_pdf' => 'nullable|file|mimes:pdf|max:3048', // Máx. 2MB
            'declaracion_jurada_conectividad_pdf' => 'nullable|file|mimes:pdf|max:3048', // Máx. 2MB
            'voucher_pago' => 'required|file|mimes:pdf,jpg,png|max:4120',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $uploadPath = public_path('postulantes/regulares/');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $documentPaths = [];
        $fileFields = [
            'dni_pdf',
            'partida_nacimiento_pdf',
            'certificado_secundaria_pdf',
            'foto',
            'declaracion_jurada_salud_pdf',
            'declaracion_jurada_documentos_pdf',
            'declaracion_jurada_conectividad_pdf',
            'voucher_pago',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $file->getClientOriginalName();
                $file->move($uploadPath, $filename);
                $documentPaths[$field] = 'postulantes/regulares/' . $filename;
            }
        }

        $data = array_merge($validated, $documentPaths);

        PostulantesRegular::create($data);

        return redirect()->route('index')->with('success', 'Inscripcion creada con éxito, pronto recibirás un correo de respuesta hacia el correo registrado.');
    }
    public function show($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $departamentos = [
            1 => "Apurímac",
            2 => "Ayacucho",
            3 => "Cusco",
            4 => "Madre de Dios",
            5 => "Puno",
        ];
        $provincias = [
            1 => "Abancay",
            2 => "Andahuaylas",
            3 => "Antabamba",
            4 => "Aymaraes",
            5 => "Chincheros",
            6 => "Cotabambas",
            7 => "Grau",
            8 => "Huamanga",
            9 => "Cangallo",
            10 => "Huanca Sancos",
            11 => "Huanta",
            12 => "La Mar",
            13 => "Lucanas",
            14 => "Parinacochas",
            15 => "Páucar del Sara Sara",
            16 => "Sucre",
            17 => "Víctor Fajardo",
            18 => "Vilcas Huamán",
            19 => "Cusco",
            20 => "Acomayo",
            21 => "Anta",
            22 => "Calca",
            23 => "Canas",
            24 => "Canchis",
            25 => "Chumbivilcas",
            26 => "Espinar",
            27 => "La Convención",
            28 => "Paruro",
            29 => "Paucartambo",
            30 => "Quispicanchi",
            31 => "Urubamba",
            32 => "Tambopata",
            33 => "Manu",
            34 => "Tahuamanu",
            35 => "Puno",
            36 => "Azángaro",
            37 => "Carabaya",
            38 => "Chucuito",
            39 => "El Collao",
            40 => "Huancané",
            41 => "Lampa",
            42 => "Melgar",
            43 => "San Antonio de Putina",
            44 => "San Román",
            45 => "Yunguyo",
        ];
        $distritos = [
            1 => "Abancay",
            2 => "Ccapacmarca",
            3 => "Chacoche",
            4 => "Circa",
            5 => "Curahuasi",
            6 => "Huanipaca",
            7 => "Lambrama",
            8 => "Pichirhua",
            9 => "Andahuaylas",
            10 => "Chiara",
            11 => "Huaquirca",
            12 => "Kishuar",
            13 => "Pampachiri",
            14 => "San Jerónimo",
            15 => "San José de Pisca",
            16 => "San Pedro de Andahuaylas",
            17 => "Tantarica",
            18 => "Antabamba",
            19 => "El Oro",
            20 => "Huaquillas",
            21 => "Pampamarca",
            22 => "Sabaino",
            23 => "Santiago de Antabamba",
            24 => "Tambo",
            25 => "Aymaraes",
            26 => "Chalhuanca",
            27 => "Colcabamba",
            28 => "Domingo Huamaní",
            29 => "Juan Espinoza Medrano",
            30 => "La Conveniencia",
            31 => "Pampa Grande",
            32 => "San Juan de Chacña",
            33 => "Chincheros",
            34 => "Cocharcas",
            35 => "Huaccana",
            36 => "Oropesa",
            37 => "Uripa",
            38 => "Virundo",
            39 => "Cotabambas",
            40 => "Challhuahuacho",
            41 => "Haquira",
            42 => "Turpay",
            43 => "Coyllurqui",
            44 => "Grau",
            45 => "Chuquibambilla",
            46 => "Santa Rosa",
            47 => "Pampamarca",
            48 => "Pichirhua",
            49 => "Huamanga",
            50 => "Cangallo",
            51 => "Huanca Sancos",
            52 => "Huanta",
            53 => "La Mar",
            54 => "Lucanas",
            55 => "Parinacochas",
            56 => "Páucar del Sara Sara",
            57 => "Sucre",
            58 => "Víctor Fajardo",
            59 => "Vilcas Huamán",
            60 => "Cangallo",
            61 => "Chuschi",
            62 => "Los Morochucos",
            63 => "Tambo",
            64 => "Soledad",
            65 => "Huanca Sancos",
            66 => "Sancos",
            67 => "Sarhua",
            68 => "Tambo",
            69 => "Huanta",
            70 => "Ayahuanco",
            71 => "Chaca",
            72 => "Iguain",
            73 => "La Mar",
            74 => "San José de Ushua",
            75 => "San José de los Molinos",
            76 => "Anco",
            77 => "Chilca",
            78 => "Cangallo",
            79 => "Pacaycasa",
            80 => "Puquio",
            81 => "Chaviña",
            82 => "Sancos",
            83 => "Socos",
            84 => "Carmen Alto",
            85 => "Siria",
            86 => "Coracora",
            87 => "Ayacucho",
            88 => "Chumpi",
            89 => "San Juan de Churubamba",
            90 => "San José de los Molinos",
            91 => "Santillana",
            92 => "Sara Sara",
            93 => "Lucanas",
            94 => "Pucara",
            95 => "Cabana",
            96 => "Acos",
            97 => "Santillana",
            98 => "Sucre",
            99 => "Rumi Cruz",
            100 => "San Juan",
            101 => "San Salvador",
            102 => "Santa Rosa",
            103 => "Huaquilla",
            104 => "Victor Fajardo",
            105 => "Ayahuanco",
            106 => "Huañahui",
            107 => "San José de Ushua",
            108 => "San Juan de la Esperanza",
            109 => "Vilcas Huamán",
            110 => "Vítor",
            111 => "Huanca",
            112 => "Aco",
            113 => "Carhuanca",
            114 => "Cusco",
            115 => "San Sebastián",
            116 => "San Jerónimo",
            117 => "Santiago",
            118 => "Ccorca",
            119 => "Saylla",
            120 => "Poroy",
            121 => "Wanchaq",
            122 => "Acomayo",
            123 => "Acopia",
            124 => "Mosoc Llacta",
            125 => "Pomacanchi",
            126 => "Rondocan",
            127 => "Acos",
            128 => "Sangarará",

            129 => "Anta",
            130 => "Huarocondo",
            131 => "Mollepata",
            132 => "Pucyura",
            133 => "Zurite",
            134 => "Ancahuasi",
            135 => "Cachimayo",
            136 => "Chinchaypujio",
            137 => "Huarocondo",
            138 => "Limatambo",
            139 => "Calca",
            140 => "Coya",
            141 => "Lamay",
            142 => "Lares",
            143 => "Pisac",
            144 => "San Salvador",
            145 => "Taray",
            146 => "Yanatile",
            147 => "Yanaoca",
            148 => "Checca",
            149 => "Kunturkanka",
            150 => "Langui",
            151 => "Layo",
            152 => "Pampamarca",
            153 => "Quehue",
            154 => "Túpac Amaru",
            155 => "Sicuani",
            156 => "Checacupe",
            157 => "Marangani",
            158 => "Pitumarca",
            159 => "San Pablo",
            160 => "San Pedro",
            161 => "Combapata",
            162 => "Tinta",
            163 => "Santo Tomás",
            164 => "Capaya",
            165 => "Colquemarca",
            166 => "Chamaca",
            167 => "Llusco",
            168 => "Velille",
            169 => "Capacmarca",
            170 => "Livitaca",
            171 => "Quiñota",
            172 => "Condoroma",
            173 => "Coporaque",
            174 => "Ocoruro",
            175 => "Pallpata",
            176 => "Pichigua",
            177 => "Suyckutambo",
            178 => "Alto Pichigua",
            179 => "Yauri",
            180 => "Santa Teresa",
            181 => "Echarate",
            182 => "Quellouno",
            183 => "Vilcabamba",
            184 => "Huayopata",
            185 => "Inkawasi",
            186 => "Maranura",
            187 => "Megantoni",
            188 => "Ocobamba",
            189 => "Pachari",
            190 => "Quimiri",
            191 => "Santa Ana",
            192 => "Villa Virgen",
            193 => "Villa Kintiarina",
            194 => "Paruro",
            195 => "Accha",
            196 => "Ccapi",
            197 => "Colcha",
            198 => "Huanoquite",
            199 => "Omacha",
            200 => "Paccaritambo",
            201 => "Pillpinto",
            202 => "Yaurisque",
            203 => "Paucartambo",
            204 => "Caicay",
            205 => "Challabamba",
            206 => "Kosñipata",
            207 => "Colquepata",
            208 => "Huancarani",
            209 => "Urcos",
            210 => "Ccarhuayo",
            211 => "Quiquijana",
            212 => "Acomayo",
            213 => "Acopia",
            214 => "Andahuaylillas",
            215 => "Cusipata",
            216 => "Oropesa",
            217 => "Marangani",
            218 => "San Pablo",
            219 => "Yanque",
            220 => "Urubamba",
            221 => "Ollantaytambo",
            222 => "Yucay",
            223 => "Chinchero",
            224 => "Huayllabamba",
            225 => "Machupicchu",
            226 => "Maras",
            227 => "Tambopata",
            228 => "Inambari",
            229 => "Las Piedras",
            230 => "Puerto Maldonado",
            231 => "Río de Los Amigos",
            232 => "Manu",
            233 => "Fitzcarrald",
            234 => "Madre de Dios",
            235 => "Huepetue",
            236 => "Iñapari",
            237 => "Tahuamanu",
            238 => "Iberia",
            239 => "Puno",
            240 => "Acora",
            241 => "Atuncolla",
            242 => "Capachica",
            243 => "Paucarcolla",
            244 => "Pichacani",
            245 => "Plateria",
            246 => "San Antonio",
            247 => "San Juan del Oro",
            248 => "San Pedro",
            249 => "Santiago de Pupuja",
            250 => "Tiquillaca",
            251 => "Vilque",
            252 => "Azángaro",
            253 => "Chupa",
            254 => "Muñani",
            255 => "San Juan de Salinas",
            256 => "San José",
            257 => "Achaya",
            258 => "Arapa",
            259 => "Asillo",
            260 => "Caminaca",
            261 => "Potoni",
            262 => "Putina",
            263 => "Samán",
            264 => "San Antón",
            265 => "Santiago de Pupuja",
            266 => "Tirapata",
            267 => "José Domingo Choquehuanca",
            268 => "Pedro Vilcapaza",
            269 => "Huatasani",
            270 => "Carabaya",
            271 => "Macusani",
            272 => "Ayapata",
            273 => "Coasa",
            274 => "Corani",
            275 => "San Gaban",
            276 => "Chucuito",
            277 => "Desaguadero",
            278 => "Juli",
            279 => "Juliaca",
            280 => "Puno",
            281 => "Pomata",
            282 => "Ilave",
            283 => "Coporaque",
            284 => "Tiquillaca",
            285 => "Huancané",
            286 => "Vilque Chico",
            287 => "Santa Rosa",
            288 => "Chicureo",

            289 => "Lampa",
            290 => "Juliaca",
            291 => "Taraco",
            292 => "Melgar",
            293 => "Antauta",
            294 => "Santa Rosa",
            295 => "Putina",
            296 => "Cuyocuyo",
            297 => "Phara",
            298 => "San Antón",
            299 => "Juliaca",
            300 => "San Román",
            301 => "Puno",
            302 => "San Pedro",
            303 => "San Pablo",
            304 => "Yunguyo",
            305 => "Copacabana",
            306 => "Pomata",
            307 => "Desaguadero",
        ];
        $nombre_departamento = $departamentos[$postulante->departamento_nacimiento] ?? 'No registrado';
        $nombre_provincia = $provincias[$postulante->provincia_nacimiento] ?? 'No registrado';
        $nombre_distrito = $distritos[$postulante->distrito_nacimiento] ?? 'No registrado';
        return view('alumnos.postulantes.regulares.show', compact('postulante', 'nombre_departamento', 'nombre_provincia', 'nombre_distrito'));
    }
    public function edit($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        return view('alumnos.postulantes.regulares.edit', compact('postulante'));
    }
    public function update(Request $request, $id)
    {
        $postulante = PostulantesRegular::findOrFail($id); // Buscar al postulante con el ID dado.

        $validated = $request->validate([
            'email' => 'required|email|unique:postulantes_regulars,email,' . $postulante->id, // Aseguramos que el email sea único, excluyendo el postulante actual
            'programa' => 'required|string',
            'estudio_beca' => 'required|boolean',
            'apellidos' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'dni' => 'required|digits:8|unique:postulantes_regulars,dni,' . $postulante->id, // Aseguramos que el DNI sea único, excluyendo el postulante actual
            'genero' => 'required|boolean',
            'direccion' => 'required|string|max:255',
            'numero' => 'required|numeric|digits_between:7,9',
            'fecha_nacimiento' => 'required|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'distrito_nacimiento' => 'nullable|string|max:255',
            'provincia_nacimiento' => 'nullable|string|max:255',
            'departamento_nacimiento' => 'nullable|string|max:255',
            'colegio' => 'required|string|max:255',
            'codigo_colegio' => 'required|string|max:50',
            'gestion_colegio' => 'required|string|in:publico,privado',
            'direccion_colegio' => 'nullable|string|max:255',
            'distrito_colegio' => 'nullable|string|max:255',
            'provincia_colegio' => 'nullable|string|max:255',
            'departamento_colegio' => 'nullable|string|max:255',
            'ano_termino_colegio' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
            'promedio_colegio' => 'nullable|numeric|between:0,20',
            'lengua_1' => 'required|string|max:255',
            'lengua_2' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|in:soltero,casado,divorciado,viudo',
            'num_hijos' => 'nullable|integer|min:0',
            'trabajas' => 'nullable|boolean',
            'donde_trabajas' => 'nullable|string|max:255',
            'cargo_trabajas' => 'nullable|string|max:255',
            'describe_eespp' => 'required|string|max:500',

            'dni_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'partida_nacimiento_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'certificado_secundaria_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'foto' => 'nullable|file|mimes:jpg,png|max:3048',
            'declaracion_jurada_salud_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'declaracion_jurada_documentos_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'declaracion_jurada_conectividad_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'voucher_pago' => 'nullable|file|mimes:pdf,jpg,png|max:3048',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $uploadPath = public_path('postulantes/regulares/');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $documentPaths = [];
        $fileFields = [
            'dni_pdf',
            'partida_nacimiento_pdf',
            'certificado_secundaria_pdf',
            'foto',
            'declaracion_jurada_salud_pdf',
            'declaracion_jurada_documentos_pdf',
            'declaracion_jurada_conectividad_pdf',
            'voucher_pago',
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($uploadPath, $filename);
                $documentPaths[$field] = 'postulantes/regulares/' . $filename;

                // Eliminar el archivo anterior si existe
                if (!empty($postulante->$field) && file_exists(public_path($postulante->$field))) {
                    unlink(public_path($postulante->$field));
                }
            } else {
                // Mantener el archivo anterior si no se subió uno nuevo
                $documentPaths[$field] = $postulante->$field;
            }
        }

        /* foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $file->getClientOriginalName(); // Añadimos un prefijo con el timestamp para evitar conflictos de nombres
                $file->move($uploadPath, $filename);
                $documentPaths[$field] = 'postulantes/regulares/' . $filename;

                // Eliminar archivo anterior si existe (para evitar acumular archivos no necesarios)
                if ($postulante->$field) {
                    $previousFile = public_path($postulante->$field);
                    if (file_exists($previousFile)) {
                        unlink($previousFile);
                    }
                }
            }
        } */

        // Fusionamos los campos validados con las rutas de los documentos (si se subieron)
        $data = array_merge($validated, $documentPaths);

        // Actualizamos los datos del postulante
        $postulante->update($data);

        return redirect()->route('regulares.index')->with('success', 'Inscripción actualizada con éxito.');
    }

    public function destroy($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $postulante->delete();
        return redirect()->back()->with('success', 'Postulante eliminado correctamente.');
    }

    public function exportarCSV()
    {
        return Excel::download(new PostulantesRegularExport, 'postulantes.csv');
    }

}
