<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMoreFieldsToShipmentsTable extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('reference_number', 100)->nullable()->after('tracking_number');
            $table->string('pickup_type', 20)->default('pickup'); // pickup = collected from the customer | drop_off = customer drops it at the branch
            $table->string('receiver_phone2', 30)->nullable();
            $table->string('receiver_district')->nullable();
            $table->string('receiver_landmark')->nullable();
            $table->string('package_type', 30)->default('parcel');
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->boolean('is_fragile')->default(false);
            $table->boolean('allow_open')->default(false); // allow the receiver to open the shipment before accepting it
            $table->decimal('goods_value', 10, 2)->default(0); // goods price
            $table->decimal('total_amount', 10, 2)->default(0); // total = shipping + goods price
            $table->string('shipping_payer', 20)->default('sender'); // sender | receiver
        });

        // Migrate old shipments: the old COD amount is treated as the goods price
        DB::statement("UPDATE shipments SET goods_value = cod_amount WHERE payment_method = 'cod'");
        DB::statement('UPDATE shipments SET total_amount = shipping_cost + goods_value');
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'reference_number', 'pickup_type', 'receiver_phone2', 'receiver_district', 'receiver_landmark',
                'package_type', 'length', 'width', 'height', 'is_fragile', 'allow_open', 'goods_value',
                'total_amount', 'shipping_payer',
            ]);
        });
    }
}
