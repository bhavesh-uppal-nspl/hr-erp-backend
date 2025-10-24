<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIsDeletedToAllTables extends Command
{
    protected $signature = 'db:add-isdeleted-column';
    protected $description = 'Add isDeleted column before created_at to all tables in the database';

    public function handle()
    {
        $tables = DB::select("SELECT table_name 
                              FROM information_schema.tables 
                              WHERE table_schema = DATABASE() 
                                AND table_type = 'BASE TABLE'");

        foreach ($tables as $table) {
            $tableName = $table->table_name;

            // Skip if column already exists
            if (Schema::hasColumn($tableName, 'isDeleted')) {
                $this->info("Skipping {$tableName} — already has isDeleted.");
                continue;
            }

            // Add the column at the end
            try {
                DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `isDeleted` TINYINT(1) DEFAULT 0");
                $this->info("Added isDeleted to {$tableName}");
            } catch (\Exception $e) {
                $this->error("Failed on {$tableName}: " . $e->getMessage());
            }
        }

        $this->info('✅ All done.');
    }
}
