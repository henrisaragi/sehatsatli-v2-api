<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->rememberToken();
            $table->integer('status')->default(1);

            $table->string('username')->unique()->comment("Username");
            $table->string('name')->comment("Nama ");
            $table->string('email')->comment("Alamat email");;
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable()->comment("Nomor Telp");
            $table->json('phones')->nullable()->comment("Nomor Telp Tambahan");
            $table->string('gender', 1)->nullable()->comment("Janis Kelamin");
            $table->string('drh_specialist')->nullable()->comment("Specialist Dokter Hewan");

            $table->string('occupation')->nullable()->comment("Jabatan");
            $table->integer('user_level')->nullable();
            $table->integer('upt_id')->default(0)->comment("ID UPT");
            $table->string('upt_type')->nullable()->comment("Tipe UPT");

            $table->boolean('reset_password')->default(1);
            $table->boolean('trained')->default(0);
            $table->boolean('is_doctor')->default(0);
            $table->boolean('show_in_contact')->default(0);
            $table->boolean('training_mode')->default(0);
            $table->boolean('web_admin')->default(0);
            $table->boolean('all_notification')->default(0);

            $table->index('id');
            $table->index(['username', 'password', 'status']);
            $table->index(['id', 'status']);
            $table->index(['id', 'status', 'user_level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
