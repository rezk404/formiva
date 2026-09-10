<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\InquirySubmissionRequest;
use App\Mail\InquiryReceived;
use App\Mail\InquirySubmitted;
use App\Services\InquiryPipeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class InquirySubmissionController extends Controller
{
    public function __invoke(InquirySubmissionRequest $request, InquiryPipeline $pipeline): JsonResponse|RedirectResponse
    {
        $inquiry = $pipeline->create([
            ...$request->inquiryAttributes(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to(config('formiva.inquiries.recipient', config('mail.from.address')))->send(new InquiryReceived($inquiry));
            if ($inquiry->email) {
                Mail::to($inquiry->email)->send(new InquirySubmitted($inquiry));
            }
        } catch (Throwable $exception) {
            Log::error('Inquiry notification delivery failed.', ['inquiry_id' => $inquiry->id, 'exception' => $exception]);
        }

        $payload = [
            'ok' => true,
            'message' => 'Your project brief is with us.',
            'reference' => $inquiry->reference,
        ];

        return $request->expectsJson()
            ? response()->json($payload, 201)
            : redirect()->route('contact')->with('inquiry_success', $payload);
    }
}
