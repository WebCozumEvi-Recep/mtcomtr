<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $totalAmount = $product->price * $request->quantity;

            // 1. Müşteri (Customer) İşlemleri ve Fraud (Risk) Skoru Hesabı
            $customer = Customer::firstOrCreate(
                ['phone' => $request->phone],
                ['name' => $request->name, 'email' => $request->email]
            );

            // Basit risk algoritması (Kara listedeyse 90 puan, çok sipariş verdiyse risksiz vb.)
            $fraudScore = $customer->is_blacklisted ? 90.0 : ($customer->orders()->count() > 5 ? 10.0 : 0.0);

            // 2. Siparişin Başlatılması (Onay Bekliyor - COD)
            $order = Order::create([
                'domain_id' => $request->domain_id,
                'customer_id' => $customer->id,
                'grand_total' => $totalAmount,
                'fraud_score' => $fraudScore,
                'status' => 'pending',
                'order_notes' => 'Gönderim Adresi: ' . $request->address
            ]);

            // 3. Sipariş İçeriğinin (Ürünlerin) Eklenmesi
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->price,
                'total_price' => $totalAmount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sipariş başarıyla alındı. Müşteri temsilcisi onaylayacaktır.',
                'order_id' => $order->id,
                'fraud_risk' => $fraudScore > 50 ? 'Yüksek' : 'Düşük'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Sipariş sırasında hata oluştu.', 'error' => $e->getMessage()], 500);
        }
    }
}
