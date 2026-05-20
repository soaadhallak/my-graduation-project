<?php

namespace App\Listeners;

use App\Events\MemberInvited;
use App\Mail\InvitationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendInvitationMail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MemberInvited $event): void
    {
        Mail::to($event->invitation->email)
        ->send(new InvitationMail($event->invitation, $event->projectName, $event->inviterName));
    }
}
