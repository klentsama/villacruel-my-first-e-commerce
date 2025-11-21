<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Orders')]
class MyOrderspage extends Component
{
    use WithPagination;

    public function render()
    {
        $my_orders = Order::where('user_id', Auth::id())->latest()->paginate(10);
        return view('livewire.my-orderspage',[
            'orders' => $my_orders,
        ]);
    }
}
