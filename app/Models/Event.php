<?php

namespace App\Models;

use App\Notifications\SendClientEventCreatedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Thomasjohnkane\Snooze\ScheduledNotification;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'area_id',
        'place_id'
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }


    public function notifyClients()
    {
        $eventArea = Area::find($this->area_id);

        $users = User::all();

        $matchedUsers = collect(User::with('areas')->get())->filter(function ($user) use ($eventArea) {
            $userAreas = $user->areas()->get();
            if ($userAreas->count()) {
                // Returns index of match
                return $userAreas->search(function ($userArea, $key) use ($eventArea) {
                    $doAreasOverlap = $eventArea->intersects("area", $userArea->area)->get();

                    return $doAreasOverlap->count() > 1;
                }) !== false;
            }
            return false;
        });

        $matchedUsers->each(function ($user) {
            $target = (new AnonymousNotifiable)
                ->route('mail', $user->email);

            ScheduledNotification::create(
                $target, // Target
                new SendClientEventCreatedNotification($this->place()->get()), // Notification
                Carbon::now()->addSeconds(5) // Send At
            );
        });

        return $matchedUsers;

    }
}
