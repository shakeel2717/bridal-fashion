<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Removing items in rental edit left some rentals on "partially picked up"
     * even though every remaining item is in the same state.
     */
    public function up(): void
    {
        $statuses = ['picked_up' => 'picked_up', 'returned' => 'returned', 'pending' => 'booked'];

        foreach (DB::table('rentals')->where('status', 'partially_picked_up')->pluck('id') as $id) {
            $states = DB::table('rental_items')->where('rental_id', $id)->distinct()->pluck('pickup_status');

            if ($states->count() === 1 && isset($statuses[$states->first()])) {
                DB::table('rentals')->where('id', $id)->update(['status' => $statuses[$states->first()]]);
            }
        }
    }

    public function down(): void
    {
        // Data repair only — the old wrong statuses can't be restored
    }
};
