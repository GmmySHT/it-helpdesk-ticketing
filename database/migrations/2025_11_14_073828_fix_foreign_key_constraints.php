<?php
// database/migrations/2025_11_14_fix_foreign_key_constraints.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // safety: only add foreign keys if columns exist and keys not present
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (! $this->foreignKeyExists('tickets', 'tickets_user_id_foreign') && Schema::hasColumn('tickets', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
                if (! $this->foreignKeyExists('tickets', 'tickets_category_id_foreign') && Schema::hasColumn('tickets', 'category_id')) {
                    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                }
                if (! $this->foreignKeyExists('tickets', 'tickets_assigned_to_foreign') && Schema::hasColumn('tickets', 'assigned_to')) {
                    $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        if (Schema::hasTable('ticket_responses')) {
            Schema::table('ticket_responses', function (Blueprint $table) {
                if (! $this->foreignKeyExists('ticket_responses', 'ticket_responses_ticket_id_foreign') && Schema::hasColumn('ticket_responses', 'ticket_id')) {
                    $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
                }
                if (! $this->foreignKeyExists('ticket_responses', 'ticket_responses_user_id_foreign') && Schema::hasColumn('ticket_responses', 'user_id')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('ticket_responses', function (Blueprint $table) {
            if ($this->foreignKeyExists('ticket_responses', 'ticket_responses_ticket_id_foreign')) {
                $table->dropForeign(['ticket_id']);
            }
            if ($this->foreignKeyExists('ticket_responses', 'ticket_responses_user_id_foreign')) {
                $table->dropForeign(['user_id']);
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if ($this->foreignKeyExists('tickets', 'tickets_user_id_foreign')) {
                $table->dropForeign(['user_id']);
            }
            if ($this->foreignKeyExists('tickets', 'tickets_category_id_foreign')) {
                $table->dropForeign(['category_id']);
            }
            if ($this->foreignKeyExists('tickets', 'tickets_assigned_to_foreign')) {
                $table->dropForeign(['assigned_to']);
            }
        });
    }

    // helper: check FK existence (works for MySQL)
    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $db = config('database.connections.' . config('database.default') . '.database');
        $result = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?", [$db, $table, $fkName]);
        return count($result) > 0;
    }
};

?>