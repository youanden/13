<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\Event;
use App\Models\Place;
use App\Models\Team;
use App\Models\User;
use App\Notifications\SendClientEventCreatedNotification;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;
use Grimzy\LaravelMysqlSpatial\Types\LineString;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;

//use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Thomasjohnkane\Snooze\ScheduledNotification;

class ClientNotificationsTest extends TestCase
{
//    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     * @throws \Thomasjohnkane\Snooze\Exception\SchedulingFailedException
     */
    /** @test */
    public function test_notify_clients_with_intersecting_notification_areas()
    {

        Notification::fake();

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
            "name" => "St. Ann's Community Church",
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
            'name' => 'Food Drive Next Week!',
            'starts_at' => Carbon::now()->addWeek()->setTime(9, 0),
            'ends_at' => Carbon::now()->addWeek()->setTime(21, 0),
            'area_id' => $teamArea->id,
            'place_id' => $place->id
        ]);
        $agencyTeam->events()->save($agencyEvent);

        $agencyEvent->notifyClients();

        $this->travel(10)->seconds();

        $this->artisan('snooze:send');


        Notification::assertSentTo(
            new AnonymousNotifiable, SendClientEventCreatedNotification::class
        );

        $this->assertTrue(true);
    }
}
