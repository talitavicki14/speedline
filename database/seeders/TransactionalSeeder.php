<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\TransactionSparepart;
use App\Models\Service;
use App\Models\Sparepart;
use App\Models\Transaction;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionalSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();
        $mekanik  = User::where('role', 'mekanik')->first();
        $kasir    = User::where('role', 'kasir')->first();

        if (!$customer || !$mekanik || !$kasir) {
            $this->command->warn('Skipping TransactionalSeeder: missing customer, mekanik, or kasir user.');
            return;
        }

        $services   = Service::all()->keyBy('service_name');
        $spareparts = Sparepart::all()->keyBy('name');

        $vehicle = Vehicle::firstOrCreate(
            ['user_id' => $customer->id, 'license_plate' => 'B 1234 ABC'],
            [
                'brand'   => 'Toyota',
                'model'   => 'Avanza',
                'year'    => 2022,
                'color'   => 'Black',
            ]
        );
        
        $booking1 = Booking::create([
            'user_id'      => $customer->id,
            'vehicle_id'   => $vehicle->id,
            'booking_date' => Carbon::now()->subDays(5)->toDateString(),
            'booking_time' => '09:00:00',
            'status'       => 'completed',
            'complaint'    => 'Ganti oli dan cek rem.',
        ]);

        $svc1 = $services->get('Ganti Oli & Filter');
        $svc2 = $services->get('Servis Rem Menyeluruh');
        if ($svc1) $booking1->services()->attach($svc1->id, ['price' => $svc1->price]);
        if ($svc2) $booking1->services()->attach($svc2->id, ['price' => $svc2->price]);

        $sp1 = $spareparts->get('Mobil 1 Full Synthetic 5W-30');
        $totalSparepart1 = 0;
        if ($sp1 && $sp1->stock >= 4) {
            $totalSparepart1 = $sp1->price * 4;
            $sp1->decrement('stock', 4);
        }

        $totalService1 = $booking1->services->sum('pivot.price');

        $transaction1 = Transaction::create([
            'booking_id'      => $booking1->id,
            'mekanik_id'      => $mekanik->id,
            'kasir_id'        => $kasir->id,
            'total_service'   => $totalService1,
            'total_sparepart' => $totalSparepart1,
            'grand_total'     => $totalService1 + $totalSparepart1,
        ]);

        if ($sp1 && $totalSparepart1 > 0) {
            TransactionSparepart::create([
                'transaction_id' => $transaction1->id,
                'booking_id'     => $booking1->id,
                'sparepart_id'   => $sp1->id,
                'qty'            => 4,
                'price'          => $sp1->price,
                'subtotal'       => $totalSparepart1,
            ]);
        }

        Payment::create([
            'transaction_id' => $transaction1->id,
            'payment_date'   => Carbon::now()->subDays(5)->toDateString(),
            'amount_paid'    => $transaction1->grand_total,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $booking2 = Booking::create([
            'user_id'      => $customer->id,
            'vehicle_id'   => $vehicle->id,
            'booking_date' => Carbon::now()->toDateString(),
            'booking_time' => '10:00:00',
            'status'       => 'ready',
            'complaint'    => 'Tune-up mesin.',
        ]);

        $svc3 = $services->get('Tune-Up Mesin Intensif');
        if ($svc3) $booking2->services()->attach($svc3->id, ['price' => $svc3->price]);

        $sp2 = $spareparts->get('NGK Spark Plug Iridium');
        $totalSparepart2 = 0;
        if ($sp2 && $sp2->stock >= 4) {
            $totalSparepart2 = $sp2->price * 4;
            $sp2->decrement('stock', 4);
        }

        $totalService2 = $booking2->services->sum('pivot.price');

        $transaction2 = Transaction::create([
            'booking_id'      => $booking2->id,
            'mekanik_id'      => $mekanik->id,
            'kasir_id'        => $kasir->id,
            'total_service'   => $totalService2,
            'total_sparepart' => $totalSparepart2,
            'grand_total'     => $totalService2 + $totalSparepart2,
        ]);

        if ($sp2 && $totalSparepart2 > 0) {
            TransactionSparepart::create([
                'transaction_id' => $transaction2->id,
                'booking_id'     => $booking2->id,
                'sparepart_id'   => $sp2->id,
                'qty'            => 4,
                'price'          => $sp2->price,
                'subtotal'       => $totalSparepart2,
            ]);
        }

        Payment::create([
            'transaction_id' => $transaction2->id,
            'amount_paid'    => 0,
            'payment_status' => 'unpaid',
        ]);

        Booking::create([
            'user_id'      => $customer->id,
            'vehicle_id'   => $vehicle->id,
            'booking_date' => Carbon::now()->addDays(2)->toDateString(),
            'booking_time' => '08:00:00',
            'status'       => 'pending',
            'complaint'    => 'Roda bergetar saat kecepatan tinggi, minta spooring.',
        ]);

        $spareparts_list = Sparepart::limit(5)->get();
        $bookingA = Booking::create([
            'user_id'      => $customer->id,
            'vehicle_id'   => $vehicle->id,
            'booking_date' => Carbon::now()->toDateString(),
            'booking_time' => '13:00:00',
            'status'       => 'completed',
            'complaint'    => 'Audit Test A: Ganti Oli',
            'updated_at'   => Carbon::now(),
        ]);
        $svA = Service::first();
        if ($svA) $bookingA->services()->attach($svA->id, ['price' => 100000]);
        $spA = $spareparts_list[0] ?? Sparepart::first();
        $trA = Transaction::create([
            'booking_id'      => $bookingA->id,
            'mekanik_id'      => $mekanik->id,
            'kasir_id'        => $kasir->id,
            'total_service'   => 100000,
            'total_sparepart' => 50000,
            'grand_total'     => 150000,
            'created_at'      => Carbon::now(),
        ]);
        TransactionSparepart::create([
            'transaction_id' => $trA->id,
            'booking_id'     => $bookingA->id,
            'sparepart_id'   => $spA->id,
            'qty'            => 1,
            'price'          => 50000,
            'subtotal'       => 50000,
        ]);
        Payment::create([
            'transaction_id' => $trA->id,
            'payment_date'   => Carbon::now()->toDateString(),
            'amount_paid'    => 150000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $bookingB = Booking::create([
            'user_id'      => $customer->id,
            'vehicle_id'   => $vehicle->id,
            'booking_date' => Carbon::now()->toDateString(),
            'booking_time' => '14:00:00',
            'status'       => 'completed',
            'complaint'    => 'Audit Test B: Servis Rem + Part',
            'updated_at'   => Carbon::now(),
        ]);
        if ($svA) $bookingB->services()->attach($svA->id, ['price' => 200000]);
        $spB = $spareparts_list[1] ?? Sparepart::first();
        $trB = Transaction::create([
            'booking_id'      => $bookingB->id,
            'mekanik_id'      => $mekanik->id,
            'kasir_id'        => $kasir->id,
            'total_service'   => 200000,
            'total_sparepart' => 100000,
            'grand_total'     => 300000,
            'created_at'      => Carbon::now(),
        ]);
        TransactionSparepart::create([
            'transaction_id' => $trB->id,
            'booking_id'     => $bookingB->id,
            'sparepart_id'   => $spB->id,
            'qty'            => 2,
            'price'          => 50000,
            'subtotal'       => 100000,
        ]);
        Payment::create([
            'transaction_id' => $trB->id,
            'payment_date'   => Carbon::now()->toDateString(),
            'amount_paid'    => 300000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $trC = Transaction::create([
            'kasir_id'        => $kasir->id,
            'total_service'   => 0,
            'total_sparepart' => 75000,
            'grand_total'     => 75000,
            'created_at'      => Carbon::now(),
        ]);
        $spC = $spareparts_list[2] ?? Sparepart::first();
        TransactionSparepart::create([
            'transaction_id' => $trC->id,
            'sparepart_id'   => $spC->id,
            'qty'            => 1,
            'price'          => 75000,
            'subtotal'       => 75000,
        ]);
        Payment::create([
            'transaction_id' => $trC->id,
            'payment_date'   => Carbon::now()->toDateString(),
            'amount_paid'    => 75000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);
    }
}
