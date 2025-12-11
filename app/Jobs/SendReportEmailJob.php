<?php

namespace App\Jobs;

use App\Mail\ReportWeeklyMonthlyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $subject;
    public $message;
    public $fileInfo;

    public function __construct($user, $subject, $fileInfo, $message = null)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->fileInfo = $fileInfo;
        $this->message = $message;
        // $this->queue = 'emails';
    }

    public function handle()
    {
        try {
            Log::info('Sending report email to: ' . $this->user->email, [
                'user' => json_encode($this->user),
                'subject' => $this->subject,
            ]);
            Mail::to($this->user->email)
                ->send(new ReportWeeklyMonthlyMail(
                    $this->user,
                    $this->subject,
                    $this->fileInfo,
                    $this->message
                ));
            Log::info('Report email sent to: ' . $this->user->email, [
                'user' => json_encode($this->user),
                'subject' => $this->subject,
            ]);
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            Log::error('Failed to send report email: ' . $e->getMessage());
        }
    }
}
