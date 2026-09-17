<?php

use App\Models\Terminal;
use App\Models\Vendor;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hibahVendors = Vendor::whereIn('nama_vendor', ['HIBAH', 'HIBAH SRISHINDU'])->get();
        $hibahVendorIds = $hibahVendors->pluck('id')->toArray();

        $terminalsQuery = Terminal::whereIn('vendor_id', $hibahVendorIds)
            ->orWhereIn('vendor_text', ['HIBAH', 'HIBAH SRISHINDU']);

        if ($terminalsQuery->exists()) {
            $koperasi = Vendor::firstOrCreate(['nama_vendor' => 'KOPERASI BANK SULTENG']);

            $terminalsQuery->update([
                'vendor_id' => $koperasi->id,
                'vendor_text' => 'KOPERASI BANK SULTENG',
                'is_hibah' => true,
            ]);
        }

        Vendor::whereIn('nama_vendor', ['HIBAH', 'HIBAH SRISHINDU'])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for obsolete hibah vendor
    }
};
