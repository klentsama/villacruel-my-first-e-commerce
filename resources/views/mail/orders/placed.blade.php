<x-mail::message>
    # Order Placed successfully!

    Thank you for your order! Your order number is: {{ $order->id }}.

    <x-mail::button :url="$url">
    View Order
    </x-mail::button>

    
</x-mail::message>
