<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TalentController extends Controller
{
    public function talentServices(Request $request)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $services = $talent->services ?? [];

        return response()->json([
            'services' => $services,
        ]);
    }

    public function talentBookings(Request $request)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookings = $talent->talentBookings ?? [];

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    public function addService(Request $request)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'advance_payment' => 'required|boolean',
            'advance_payment_value' => 'required_if:advance_payment,true|numeric',
            'advance_payment_type' => 'required_if:advance_payment,true|in:fixed,percentage',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 422);
        }

        // Create the service for the talent
        $service = $talent->services()->create([
            'title' => $request->input('service_name'),
            'description' => $request->input('description'),
            'duration' => $request->input('duration'),
            'price' => $request->input('price'),
            'discount' => $request->input('discount', 0),
            'discount_type' => $request->input('discount_type', 'percentage'),
            'advance_payment' => $request->input('advance_payment', false),
            'advance_payment_value' => $request->input('advance_payment_value'),
            'advance_payment_type' => $request->input('advance_payment_type'),
        ]);

        return response()->json(['status' => true, 'message' => 'Service added successfully', 'service' => $service], 200);
    }

    public function removeService(Request $request)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 422);
        }

        $serviceId = $request->input('service_id');

        $service = $talent->services()->find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $service->bookings()->delete(); // Delete associated bookings if any
        $service->delete();

        return response()->json(['status' => true, 'message' => 'Service removed successfully'], 200);
    }

    public function updateService(Request $request)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'service_name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:fixed,percentage',
            'advance_payment' => 'required|boolean',
            'advance_payment_value' => 'required_if:advance_payment,true|numeric',
            'advance_payment_type' => 'required_if:advance_payment,true|in:fixed,percentage',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 422);
        }

        $serviceId = $request->input('service_id');
        $service = $talent->services()->find($serviceId);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Update the service details
        $service->update([
            'title' => $request->input('service_name'),
            'description' => $request->input('description'),
            'duration' => $request->input('duration'),
            'price' => $request->input('price'),
            'discount' => $request->input('discount', 0),
            'discount_type' => $request->input('discount_type', 'percentage'),
            'advance_payment' => $request->input('advance_payment', false),
            'advance_payment_value' => $request->input('advance_payment_value'),
            'advance_payment_type' => $request->input('advance_payment_type'),
        ]);

        return response()->json(['status' => true, 'message' => 'Service updated successfully', 'service' => $service], 200);
    }

    public function acceptBooking(Request $request, $bookingId)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = $talent->talentBookings()->find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'accepted';
        $booking->save();

        return response()->json(['status' => true, 'message' => 'Booking accepted successfully', 'booking' => $booking], 200);
    }

    public function rejectBooking(Request $request, $bookingId)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = $talent->talentBookings()->find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'rejected';
        $booking->save();

        return response()->json(['status' => true, 'message' => 'Booking rejected successfully', 'booking' => $booking], 200);
    }

    public function completedBooking(Request $request, $bookingId)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = $talent->talentBookings()->find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'completed';
        $booking->save();

        return response()->json(['status' => true, 'message' => 'Booking marked as completed', 'booking' => $booking], 200);
    }

    public function cancelledBooking(Request $request, $bookingId)
    {
        $talent = Auth::guard('sanctum')->user();

        if (!$talent) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = $talent->talentBookings()->find($bookingId);

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json(['status' => true, 'message' => 'Booking cancelled successfully', 'booking' => $booking], 200);
    }
}
