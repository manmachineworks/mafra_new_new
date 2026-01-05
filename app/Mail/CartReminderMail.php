<?php

namespace App\Mail;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CartReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Collection $cartItems;
    public array $summary;
    public string $checkoutUrl;

    public function __construct(User $user, Collection $cartItems, array $summary, string $checkoutUrl)
    {
        $this->user = $user;
        $this->cartItems = $cartItems;
        $this->summary = $summary;
        $this->checkoutUrl = $checkoutUrl;
    }

    public function build()
    {
        return $this->subject(translate('Complete your order'))
            ->view('emails.cart_reminder')
            ->with([
                'user' => $this->user,
                'cartItems' => $this->cartItems,
                'summary' => $this->summary,
                'checkoutUrl' => $this->checkoutUrl,
            ]);
    }
}
