<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('counsellor_profiles', 'uuid')) {
            Schema::table(
                'counsellor_profiles',
                function (Blueprint $table): void {
                    $table
                        ->uuid('uuid')
                        ->nullable()
                        ->after('id');
                }
            );
        }

        DB::table('counsellor_profiles')
            ->select([
                'id',
                'uuid',
            ])
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(
                100,
                function ($profiles): void {
                    foreach ($profiles as $profile) {
                        DB::table('counsellor_profiles')
                            ->where(
                                'id',
                                $profile->id
                            )
                            ->update([
                                'uuid' => (string) Str::uuid7(),
                            ]);
                    }
                }
            );

        /*
         * SQLite is used for the in-memory test database.
         *
         * During SQLite schema reconstruction Laravel can
         * already retain/create the unique UUID index.
         * Explicitly creating cp_uuid_uk again caused the
         * entire test suite to fail before assertion #1.
         *
         * Production/development MySQL still receives the
         * explicit named unique constraint on fresh installs.
         */
        if (
            DB::connection()->getDriverName()
            !== 'sqlite'
        ) {
            Schema::table(
                'counsellor_profiles',
                function (Blueprint $table): void {
                    $table->unique(
                        'uuid',
                        'cp_uuid_uk'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        if (
            ! Schema::hasColumn(
                'counsellor_profiles',
                'uuid'
            )
        ) {
            return;
        }

        if (
            DB::connection()->getDriverName()
            !== 'sqlite'
        ) {
            Schema::table(
                'counsellor_profiles',
                function (Blueprint $table): void {
                    $table->dropUnique(
                        'cp_uuid_uk'
                    );
                }
            );
        }

        Schema::table(
            'counsellor_profiles',
            function (Blueprint $table): void {
                $table->dropColumn('uuid');
            }
        );
    }
};
