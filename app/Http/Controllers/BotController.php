<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\Barber;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    // Handle Rating Submission from Bot
    public function rate(Request $request)
    {
        try {
            // Validate
            $request->validate([
                'phone' => 'required',
                'score' => 'required|integer|min:1|max:5',
            ]);

            $phone = $request->phone;
            $score = $request->score;

            // Find the LAST COMPLETED appointment for this phone that DOES NOT have a review yet.
            // We search by formatted phone (remove +57, etc) or just use SQL LIKE
            
            // Clean phone for matching (remove all non-digits)
            $cleanPhone = preg_replace('/\D/', '', $phone); 
            
            // Extract last 10 digits to be more flexible with prefixes (like 57)
            $searchNumber = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;

            Log::info("Bot Rating Attempt - Phone: $phone, Search: $searchNumber");

            $appointment = Appointment::where('status', 'completed')
                ->where(function($q) use ($searchNumber) {
                    // Remove characters from DB column and match end of string
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(client_phone, '+', ''), ' ', ''), '-', '') LIKE ?", ["%{$searchNumber}"]);
                })
                ->whereDoesntHave('review') // Ensure not already rated
                ->orderBy('scheduled_at', 'desc')
                ->first();

            if (!$appointment) {
                return response()->json(['success' => false, 'message' => 'No active appointment to rate']);
            }

            // Create Review
            Review::create([
                'appointment_id' => $appointment->id,
                'barber_id' => $appointment->barber_id,
                'score' => $score
            ]);

            Log::info("⭐ Rating Saved: $score stars for Barber ID {$appointment->barber_id} from {$cleanPhone}");

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error("Bot Rating Error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
