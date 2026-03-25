<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tables may or may not exist depending on prior installs. Guard everything.
        if (Schema::hasTable('writers') && !Schema::hasTable('contractors')) {
            Schema::rename('writers', 'contractors');
        }

        if (Schema::hasTable('articles') && !Schema::hasTable('jobs')) {
            Schema::rename('articles', 'jobs');
        }

        // Common pivot / detail tables seen in this module
        $renames = [
            'writer_details' => 'contractor_details',
            'writer_infos' => 'contractor_infos',
            'writer_leaves' => 'contractor_leaves',
            'writer_rates' => 'contractor_rates',

            'article_details' => 'job_details',
            'article_files'   => 'job_files',
            'article_comments'=> 'job_comments',
            'article_activity_logs' => 'job_activity_logs',
            'article_types'   => 'job_types',
            'article_sops'    => 'job_sops',
            'article_invoices'=> 'job_invoices',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        // Example column renames on jobs table (if present)
        if (Schema::hasTable('jobs')) {
            Schema::table('jobs', function (Blueprint $table) {
                if (Schema::hasColumn('jobs', 'article_title') && !Schema::hasColumn('jobs', 'job_title')) {
                    $table->renameColumn('article_title', 'job_title');
                }
                if (Schema::hasColumn('jobs', 'article_status') && !Schema::hasColumn('jobs', 'job_status')) {
                    $table->renameColumn('article_status', 'job_status');
                }
                if (Schema::hasColumn('jobs', 'assigned_writer_id') && !Schema::hasColumn('jobs', 'assigned_contractor_id')) {
                    $table->renameColumn('assigned_writer_id', 'assigned_contractor_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Best-effort reverse
        if (Schema::hasTable('contractors') && !Schema::hasTable('writers')) {
            Schema::rename('contractors', 'writers');
        }
        if (Schema::hasTable('jobs') && !Schema::hasTable('articles')) {
            Schema::rename('jobs', 'articles');
        }

        $renames = [
            'contractor_details' => 'writer_details',
            'contractor_infos' => 'writer_infos',
            'contractor_leaves' => 'writer_leaves',
            'contractor_rates' => 'writer_rates',

            'job_details' => 'article_details',
            'job_files'   => 'article_files',
            'job_comments'=> 'article_comments',
            'job_activity_logs' => 'article_activity_logs',
            'job_types'   => 'article_types',
            'job_sops'    => 'article_sops',
            'job_invoices'=> 'article_invoices',
        ];

        foreach ($renames as $from => $to) {
            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }

        if (Schema::hasTable('articles')) {
            Schema::table('articles', function (Blueprint $table) {
                if (Schema::hasColumn('articles', 'job_title') && !Schema::hasColumn('articles', 'article_title')) {
                    $table->renameColumn('job_title', 'article_title');
                }
                if (Schema::hasColumn('articles', 'job_status') && !Schema::hasColumn('articles', 'article_status')) {
                    $table->renameColumn('job_status', 'article_status');
                }
                if (Schema::hasColumn('articles', 'assigned_contractor_id') && !Schema::hasColumn('articles', 'assigned_writer_id')) {
                    $table->renameColumn('assigned_contractor_id', 'assigned_writer_id');
                }
            });
        }
    }
};
