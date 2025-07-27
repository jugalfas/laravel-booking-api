<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function getTalents(Request $request)
    {
        $talents = User::role('talent')->get();

        return response()->json([
            'talents' => $talents,
        ]);
    }

    public function getTalentServices(Request $request)
    {
         $validatedData = Validator::make($request->all(), [
            'talent_stage_name' => 'required|exists:users,stage_name',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 422);
        }

        $talent_stage_name = $request->input('talent_stage_name');

        $talent = User::where('stage_name', $talent_stage_name)->first();
        $services = $talent->services ?? [];

        return response()->json([
            'services' => $services,
        ]);
    }

    public function bookTalent(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'talent_stage_name' => 'required|exists:users,stage_name',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 422);
        }

        $talent_stage_name = $request->input('talent_stage_name');
        $service_id = $request->input('service_id');

        $talent = User::where('stage_name', $talent_stage_name)->first();
        if (!$talent) {
            return response()->json(['message' => 'Talent not found'], 404);
        }

        $service = $talent->services()->find($service_id);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $booking = $talent->talentBookings()->create([
            'service_id' => $service->id,
            'client_id' => Auth::guard('sanctum')->user()->id, 
            'price' => $service->price,
            'booking_date' => Carbon::parse($request->input('booking_date')),
            'booking_time' => $request->input('booking_time'),
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking requested successfully.',
            'booking' => $booking,
        ], 201);
    }

    public function getBookings(Request $request)
    {
        $client = Auth::guard('sanctum')->user();
        if (!$client) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookings = $client->clientBookings ?? [];

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    public function completedBooking(Request $request, $bookingId)
    {
        $client = Auth::guard('sanctum')->user();

        if (!$client) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = $client->clientBookings()->find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'completed';
        $booking->save();

        return response()->json(['status' => true, 'message' => 'Booking marked as completed', 'booking' => $booking], 200);
    }

    public function cancelledBooking(Request $request, $bookingId)
    {
        $client = Auth::guard('sanctum')->user();

        if (!$client) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = $client->clientBookings()->find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['status' => true, 'message' => 'Booking cancelled successfully', 'booking' => $booking], 200);
    }
}
