<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tracking_number', 30)->unique();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('courier_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();

            $table->string('origin_city');
            $table->string('receiver_name');
            $table->string('receiver_phone', 30);
            $table->string('receiver_city');
            $table->string('receiver_address');

            $table->string('service_type', 20)->default('standard'); // standard | express | same_day
            $table->decimal('weight', 8, 2)->default(1);
            $table->unsignedInteger('pieces')->default(1);
            $table->string('description')->nullable();

            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('payment_method', 20)->default('prepaid'); // prepaid | cod
            $table->decimal('cod_amount', 10, 2)->default(0);

            $table->string('status', 30)->default('pending')->index();
            $table->date('expected_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}
