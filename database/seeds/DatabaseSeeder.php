<?php

use App\Models\Branch;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $cairo = Branch::create(['name' => 'Cairo Branch', 'code' => 'CAI', 'city' => 'Cairo', 'address' => 'Nasr City - Makram Ebeid St.', 'phone' => '0222000001']);
        $alex = Branch::create(['name' => 'Alexandria Branch', 'code' => 'ALX', 'city' => 'Alexandria', 'address' => 'Smouha', 'phone' => '0333000001']);

        $password = Hash::make('password');

        $admin = User::create(['name' => 'General manager', 'email' => 'admin@shipping.test', 'password' => $password, 'work_type' => 'hq', 'phone' => '01000000000']);
        $manager = User::create(['name' => 'Cairo Branch Manager', 'email' => 'manager@shipping.test', 'password' => $password, 'branch_id' => $cairo->id]);
        $employee = User::create(['name' => 'Cairo Employee', 'email' => 'employee@shipping.test', 'password' => $password, 'branch_id' => $cairo->id]);
        $alexManager = User::create(['name' => 'Alexandria Branch Manager', 'email' => 'alex@shipping.test', 'password' => $password, 'branch_id' => $alex->id]);
        $courierUser = User::create(['name' => 'Ahmed (Courier)', 'email' => 'courier@shipping.test', 'password' => $password, 'branch_id' => $cairo->id, 'phone' => '01011111111']);

        // Assign roles and branches (the default roles are created in the migration)
        $assign = function (User $u, array $slugs) {
            foreach ($slugs as $slug) {
                DB::table('role_user')->insert(['role_id' => DB::table('roles')->where('slug', $slug)->value('id'), 'user_id' => $u->id]);
            }
            if ($u->branch_id) {
                DB::table('branch_user')->insert(['branch_id' => $u->branch_id, 'user_id' => $u->id]);
            }
        };
        $assign($admin, ['admin']);
        $assign($manager, ['branch_manager']);
        $assign($employee, ['employee']);
        $assign($alexManager, ['branch_manager']);
        $assign($courierUser, ['courier']);

        $courier1 = Courier::create(['branch_id' => $cairo->id, 'user_id' => $courierUser->id, 'name' => 'Ahmed (Courier)', 'phone' => '01011111111', 'vehicle_type' => 'motorcycle', 'vehicle_number' => 'Q A 1234', 'status' => 'available']);
        $courier2 = Courier::create(['branch_id' => $cairo->id, 'name' => 'Mahmoud Ali', 'phone' => '01022222222', 'vehicle_type' => 'van', 'vehicle_number' => 'B G 5678', 'status' => 'available']);

        $customers = [
            Customer::create(['branch_id' => $cairo->id, 'name' => 'Nile Trading Co.', 'company' => 'Nile Trading', 'phone' => '01033333333', 'email' => 'info@nile.test', 'city' => 'Cairo', 'address' => 'Downtown']),
            Customer::create(['branch_id' => $cairo->id, 'name' => 'Sara Mohamed', 'phone' => '01044444444', 'city' => 'Giza', 'address' => 'Dokki']),
            Customer::create(['branch_id' => $cairo->id, 'name' => 'Al Amal Store', 'company' => 'Al Amal Store', 'phone' => '01055555555', 'city' => 'Cairo', 'address' => 'Nasr City']),
        ];

        $cities = ['Alexandria', 'Aswan', 'Mansoura', 'Tanta', 'Luxor', 'Port Said'];
        $statuses = ['pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'delivered', 'returned'];
        $services = ['standard', 'express', 'same_day'];

        for ($i = 0; $i < 14; $i++) {
            $service = $services[$i % 3];
            $status = $statuses[$i % count($statuses)];
            $createdAt = Carbon::now()->subDays(rand(0, 12));
            $cod = $i % 2 === 0;

            $shipment = Shipment::create([
                'tracking_number' => Shipment::generateTrackingNumber(),
                'branch_id' => $cairo->id,
                'customer_id' => $customers[$i % 3]->id,
                'courier_id' => $status === 'pending' ? null : ($i % 2 ? $courier1->id : $courier2->id),
                'created_by' => $employee->id,
                'origin_city' => 'Cairo',
                'receiver_name' => 'Receiver #'.($i + 1),
                'receiver_phone' => '0106'.rand(1000000, 9999999),
                'receiver_city' => $cities[$i % count($cities)],
                'receiver_address' => 'Street '.($i + 10).', Building '.($i + 1),
                'service_type' => $service,
                'weight' => rand(1, 20) / 2,
                'pieces' => rand(1, 4),
                'description' => 'Assorted parcel',
                'shipping_cost' => $cost = rand(40, 200),
                'payment_method' => $cod ? 'cod' : 'prepaid',
                'goods_value' => $goods = ($cod ? rand(200, 1500) : 0),
                'shipping_payer' => $payer = ($i % 4 === 0 ? 'receiver' : 'sender'),
                'total_amount' => $cost + $goods,
                'cod_amount' => ($cod ? $goods : 0) + ($payer === 'receiver' ? $cost : 0),
                'package_type' => 'parcel',
                'pickup_type' => 'pickup',
                'status' => $status,
                'expected_delivery' => $createdAt->copy()->addDays(config('shipping.delivery_days.'.$service)),
                'delivered_at' => $status === 'delivered' ? $createdAt->copy()->addDays(1) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $shipment->histories()->create(['user_id' => $employee->id, 'status' => 'pending', 'location' => 'Cairo', 'note' => 'Shipment created', 'created_at' => $createdAt, 'updated_at' => $createdAt]);
            if ($status !== 'pending') {
                $shipment->histories()->create(['user_id' => $manager->id, 'status' => $status, 'note' => 'Demo update', 'created_at' => $createdAt->copy()->addHours(5), 'updated_at' => $createdAt->copy()->addHours(5)]);
            }
        }
    }
}
