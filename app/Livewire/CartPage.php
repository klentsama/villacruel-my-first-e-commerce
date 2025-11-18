<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cart Page')]
class CartPage extends Component
{
    public function render()
    {
        return view('livewire.cart-page');
    }
}
