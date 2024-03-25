<?php

namespace App\Events;

use App\Models\Tweet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TweetDeleting
{
    use Dispatchable, SerializesModels;

    public $tweet;

    public function __construct(Tweet $tweet)
    {
        $this->tweet = $tweet;
        Log::info('event fired');
    }
}