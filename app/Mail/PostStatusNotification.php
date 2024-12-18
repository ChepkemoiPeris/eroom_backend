<?php
namespace App\Mail;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PostStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $post;
    public $status;
    public $declineReason;

    public function __construct(Post $post, $status, $declineReason = null)
    {
        $this->post = $post;
        $this->status = $status;
        $this->declineReason = $declineReason;
    }

    public function build()
    {
        if ($this->status == 1) {
            return $this->subject('Your Post Has Been Approved')
                        ->view('emails.post-approved')
                        ->with([
                            'post' => $this->post,
                        ]);
        }

        return $this->subject('Your Post Has Been Declined')
                    ->view('emails.post-declined')
                    ->with([
                        'post' => $this->post,
                        'declineReason' => $this->declineReason,
                    ]);
    }
}
