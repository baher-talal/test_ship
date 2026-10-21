<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_public_tracking_page_loads()
    {
        $this->get('/track')->assertOk();
    }

    public function test_employee_can_create_shipment_and_status_changes_are_logged()
    {
        $branch = Branch::create(['name' => 'Branch', 'code' => 'F1', 'city' => 'Cairo']);
        $user = User::create(['name' => 'u', 'email' => 'u@t.test', 'password' => bcrypt('password'), 'branch_id' => $branch->id]);
        $roleId = \DB::table('roles')->where('slug', 'employee')->value('id');
        \DB::table('role_user')->insert(['role_id' => $roleId, 'user_id' => $user->id]);
        \DB::table('branch_user')->insert(['branch_id' => $branch->id, 'user_id' => $user->id]);
        $this->actingAs($user)->post('/shipments', [
            'sender_name' => 'New sender', 'sender_phone' => '01099999999', 'origin_city' => 'Cairo', 'receiver_name' => 'Receiver',
            'receiver_phone' => '011', 'receiver_city' => 'Aswan', 'receiver_address' => 'St. 1',
            'service_type' => 'standard', 'weight' => 2, 'pieces' => 1, 'shipping_cost' => 50,
            'payment_method' => 'prepaid', 'shipping_payer' => 'sender', 'pickup_type' => 'pickup', 'package_type' => 'parcel',
        ])->assertRedirect();

        $this->assertEquals(1, Customer::where('phone', '01099999999')->count());

        $shipment = Shipment::first();
        $this->assertNotNull($shipment);
        $this->assertEquals('pending', $shipment->status);

        $this->post("/shipments/{$shipment->id}/status", ['status' => 'in_transit'])->assertRedirect();
        $this->assertEquals(2, $shipment->histories()->count());
    }
}
