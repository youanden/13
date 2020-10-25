<?php

use App\Models\AdhocSubscriber;
use App\Models\Area;
use App\Models\Event;
use App\Models\Place;
use App\Models\Team;
use App\Models\User;
use App\Notifications\SendClientEventCreatedNotification;
use Carbon\Carbon;
use Geocodio\Geocodio;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Thomasjohnkane\Snooze\ScheduledNotification;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/adhoc/register', function () {
    $input = request()->input();
    Validator::make($input, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255'],
        'phone' => ['required', 'string', 'max:255']
    ])->validate();

    return DB::transaction(function () use ($input) {
        // Geocoding.
        $geoCoder = new Geocodio();
        $geoCoder->setApiKey(env('GEOCODIO_API_KEY'));
        $address = $input['street_address'] . $input['city'] . $input['state'] . ', ' . $input['zip'];
        $coordinates = $geoCoder->geocode($address);

        $input['location'] = new Point(
            $coordinates->results[0]->location->lat,
            $coordinates->results[0]->location->lng
        );
        $input['active'] = true; // start off as subscribed
        if (AdhocSubscriber::create($input)) {
            return redirect('/adhoc/thankyou');
        }
        return response()->status(401);
    });
})->name('adhoc.register');

Route::get('/adhoc/thankyou', function () {
  return view('thankyou');
});

Route::put('/events/create', function () {
    $input = request()->input();
    $event = Event::create([
        "name" => $input['name'],
        "starts_at" => Carbon::parse($input['start_time']),
        "ends_at" => Carbon::parse($input['end_time']),
        "place_id" => $input['place_id']
    ]);

    $place = $event->place;

    $matchedUsers = collect(AdhocSubscriber::whereNotNull('location')->get())->filter(function ($user) use ($place) {
            // Returns index of match

        // WHY even transform into WKT if MySQL expects a "," - hm..
        $placeWKT = str_replace(" ", ", ", $place->location->toWKT());
        $userWKT = str_replace(" ", ", ", $user->location->toWKT());
        $distanceResult = DB::select(DB::raw("
            select ST_Distance_Sphere(
                {$placeWKT},
                {$userWKT}
            ) * .000621371192 as distance
        "));

        $distanceInMiles = $distanceResult[0]->distance;

        if ($distanceInMiles < $user->alert_distance) {
            return true;
        }

        return false;
    });


    $matchedUsers->each(function ($user) use ($place) {
        $target = (new AnonymousNotifiable)
            ->route('mail', $user->email);

        ScheduledNotification::create(
            $target, // Target
            new SendClientEventCreatedNotification($place), // Notification
            Carbon::now() // Send At
        );

        // For Hackathon Time-Constraint Reasons

        $response = Http::post('https://us-central1-aiot-fit-xlab.cloudfunctions.net/sendsms', [
            'message' => "Hi! " . $place->name . " has scheduled a food drive in your pickup area!",
            'receiver' => $user->phone,
            'token' => env('SUPERSECRET_TOKEN')
        ]);

    });

    return Inertia\Inertia::render('Dashboard', [
        'places' => Place::all()->toArray(),
        'message' => "Notifications have been sent to " . $matchedUsers->count() . " users."
    ]);;
})->name('events.create');

Route::get('/test-notifications', function () {
    $agencyUser = User::firstOrNew([
        "name" => "Agency 1",
        "email" => "agency1@example.com",
        "phone" => "123"
    ], [
        "role" => "admin",
        "password" => bcrypt(123456)
    ]);
    $agencyUser->save();
    $agencyTeam = Team::firstOrNew([
        "name" => "St. Ann's Team",
        "personal_team" => true
    ]);
    $agencyTeam->user_id = $agencyUser->id;
    $agencyTeam->save();

    $user = User::firstOrNew([
        "name" => "Client 1",
        "email" => "client1@example.com",
        "phone" => "234"
    ], [
        "role" => "client",
        "password" => bcrypt(123456)
    ]);

    $user->save();

    $userArea = Area::firstOrNew([
        "name" => "My Area",
        "user_id" => $user->id
    ], [
        "area" => new Polygon([new LineString([
            new Point(26.690717206045928,-80.05076408386229),
            new Point(26.714257047003006,-80.0547981262207),
            new Point(26.688876752901592,-80.05908966064453),
            new Point(26.690717206045928,-80.05076408386229)
        ])], 4326)
    ]);

    $user->areas()->save($userArea);

    $teamArea = Area::firstOrNew([
        "name" => "Team Distribution Area 1",
    ], [
        "area" => new Polygon([new LineString([
            new Point(26.711631100640133, -80.05730867385864),
            new Point(26.711631100640133, -80.04535675048828),
            new Point(26.722000360988616, -80.04535675048828),
            new Point(26.722000360988616, -80.05730867385864),
            new Point(26.711631100640133, -80.05730867385864)
        ])], 4326)
    ]);

    $place = Place::firstOrNew([
        "name" => "St. Ann's Community Church 2",
        "address" => "310 N Olive Avenue West Palm Beach FL 33401",
        "tel" => "561-832-3757 x304",
        "email" => "testvenue@example.com",
        "category" => "Food Pantry"
    ]);
    $place->location = new Point(26.716154590985, -80.050249099731, 4326);
    $place->team_id = $agencyTeam->id;
    $place->save();

    $agencyTeam->places()->save($place);
    $agencyTeam->areas()->save($teamArea);
    $agencyEvent = Event::firstOrNew([
        "name" => "Food Drive Next Week!",
        "starts_at" => Carbon::now()->addWeek()->setTime(9, 0),
        "ends_at" => Carbon::now()->addWeek()->setTime(21, 0),
        "area_id" => $teamArea->id,
        "place_id" => $place->id
    ]);
    $agencyTeam->events()->save($agencyEvent);

    $users = $agencyEvent->notifyClients();


    dd($users);


});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->is_client) {
            return redirect('/clients');
        }
        // TODO: Build event creation form
        return Inertia\Inertia::render('Dashboard', [
            'places' => Place::all()->toArray()
        ]);
    })->name('dashboard');

    Route::get('/clients', function () {
        if ( ! auth()->user()->is_client) {
            return redirect('/dashboard');
        }
        return Inertia\Inertia::render('Clients', [
            'places' => Place::all()->toArray()
        ]);
    })->name('clients');
});
