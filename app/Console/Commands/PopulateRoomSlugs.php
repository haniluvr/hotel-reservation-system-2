<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;
use Illuminate\Support\Str;

class PopulateRoomSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:populate-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate slug field for all existing rooms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Populating slugs for existing rooms...');
        
        $rooms = Room::all();
        $updated = 0;
        
        foreach ($rooms as $room) {
            if (empty($room->slug)) {
                $room->slug = Str::slug($room->room_type);
                
                // Ensure uniqueness
                $originalSlug = $room->slug;
                $counter = 1;
                while (Room::where('slug', $room->slug)->where('id', '!=', $room->id)->exists()) {
                    $room->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                
                $room->save();
                $updated++;
                $this->line("Updated room #{$room->id}: {$room->room_type} -> {$room->slug}");
            }
        }
        
        $this->info("Successfully updated {$updated} room(s) with slugs.");
        
        return Command::SUCCESS;
    }
}
