<?php

namespace App\Http\Controllers;

use App\Classes\WebUntis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

use chillerlan\QRCode\QRCode;
use App\Models\User;
use App\Models\DartQueue;
use App\Notifications\UserRegisteredNotification;
use App\Notifications\WebPushTestNotification;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(QRCode $qRCode, WebUntis $untis)
    {
        $data['qrcode'] = $qRCode->render(route('index'));
        $data['dartGames'] = Auth::user()->dartGameInvites()->get();
        $data['activeDartGames'] = Auth::user()->activeDartGames()->get();
        $data['activeDartGame'] = Auth::user()->activeDartGame();
        $data['dartQueue'] = DartQueue::with('parentUser')->get()->unique('parent_user_id')->sortBy('created_at');
        // dd($data['dartQueue']);

        $user = User::find(1);

        $timetableData = $untis->getOwnTimetableForWeek(Carbon::now()->addDays(1));
        foreach($timetableData['data']['result']['data'] as $result) {
            $data['timetable'][] = $result;
        }

        //Create a UDP socket
        // if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))) {
        //     $errorcode = socket_last_error();
        //     $errormsg = socket_strerror($errorcode);
        //     die("Couldn't create socket: [$errorcode] $errormsg \n");
        // }
        // echo "Socket created \n";

        // // Bind the source address
        // if( !socket_bind($sock, "0.0.0.0" , 22223) ) {
        //     $errorcode = socket_last_error();
        //     $errormsg = socket_strerror($errorcode);
        //     die("Could not bind socket : [$errorcode] $errormsg \n");
        // }
        // echo "Socket bind OK \n";

        // $r = socket_recvfrom($sock, $buf, 5120, 0, $remote_ip, $remote_port);
        // echo "$remote_ip : $remote_port -- \n\n";

        // for ($i = 0; $i < $r; $i++) {
        //     // Convert the byte to hexadecimal representation
        //     $hexValue = strtoupper(dechex(ord($buf[$i])));

        //     // Ensure each hexadecimal value has two characters
        //     if (strlen($hexValue) < 2) {
        //         $hexValue = '0' . $hexValue;
        //     }

        //     // Output the hexadecimal value
        //     echo $hexValue . ' ';
        // }

        // echo "\n\n\n\n\n\n\n\n<div style='margin-top:2rem;'></div>";

        // $byteArray = unpack('C*', $buf); // 'C*' dekodiert die Daten als 8-Bit-Integer

        // foreach ($byteArray as $byte) {
        //     echo "$byte \n";
        // }

        // echo "\n\n\n";

        // socket_close($sock);

        // $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);
        return view('home.index', $data);
    }

    public function ShowMovingAverage()
    {
        Notification::send(User::all(), new WebPushTestNotification);
        return view('home.moving-average');
    }

    public function ShowAirsoftCalculator()
    {
        $data['ammoWeights'] = [
            '.20', '.23', '.25','.28',
            '.30','.32','.36',
            '.40','.43','.45','.46','.48','.49',
            '.50'
        ];

        return view('home.airsoft-calculator', $data);
    }

    public function ShowIEC7064()
    {
        return view('home.iec7064');
    }

    // todo for later.
    // a microservice needs to be written to make BlubberLounges' spotfiy data available for this application.
    // public function callback(Request $request)
    // {
    //     $resp = Http::withBasicAuth('client_id', 'secret')->asForm()->post('https://accounts.spotify.com/api/token', [
    //         'grant_type' => 'client_credentials',
    //     ]);

    //     $bearer = $resp->json('access_token');

    //     $r = Http::withToken($bearer)->withQueryParameters([
    //         'market' => 'DE'
    //     ])->get('https://api.spotify.com/v1/me/player/currently-playing');

    //     $token = request()->get('access_token');
    // }
}
