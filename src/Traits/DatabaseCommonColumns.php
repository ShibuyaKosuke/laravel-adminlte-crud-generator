<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait DatabaseCommonColumns
{
    /**
     * @param Blueprint $table
     */
    public function addCommonColumns(Blueprint $table)
    {
        $table->unsignedBigInteger('created_by')->nullable()->comment('作成者');
        $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
        $table->unsignedBigInteger('deleted_by')->nullable()->comment('削除者');

        $table->timestamp('created_at')->nullable()->comment('作成日時');
        $table->timestamp('updated_at')->nullable()->comment('更新日時');
        $table->softDeletes()->comment('削除日時');

        $table->foreign('created_by')->references('id')->on('users');
        $table->foreign('updated_by')->references('id')->on('users');
        $table->foreign('deleted_by')->references('id')->on('users');
    }

    /**
     * @param string $table
     * @param string $comment
     */
    public function comment(string $table, string $comment)
    {
        $sql = sprintf("ALTER TABLE `%s` COMMENT '%s'", $table, $comment);
        DB::statement($sql);
    }
}
