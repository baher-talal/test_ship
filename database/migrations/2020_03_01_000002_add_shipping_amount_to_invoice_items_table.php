<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddShippingAmountToInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // The shipping fee of the shipment on this line. It is shown as a summary row
            // before the total due, not as a line item.
            $table->decimal('shipping_amount', 12, 2)->default(0)->after('amount');
        });

        // Merge the separate "shipping" + "goods" lines of the same shipment (earlier format) into one line
        $goods = DB::table('invoice_items')->where('kind', 'goods')->whereNotNull('shipment_id')->get();
        foreach ($goods as $item) {
            $shipping = DB::table('invoice_items')
                ->where('invoice_id', $item->invoice_id)
                ->where('shipment_id', $item->shipment_id)
                ->where('kind', 'shipping')
                ->first();

            if ($shipping) {
                DB::table('invoice_items')->where('id', $item->id)->update(['shipping_amount' => $shipping->amount, 'kind' => 'shipment']);
                DB::table('invoice_items')->where('id', $shipping->id)->delete();
            }
        }
    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('shipping_amount');
        });
    }
}
