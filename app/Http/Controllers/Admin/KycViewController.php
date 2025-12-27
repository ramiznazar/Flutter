<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use Illuminate\Http\Request;

class KycViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index(Request $request)
    {
        $editId = $request->get('edit_id');
        $editKYC = null;
        
        if ($editId) {
            $editKYC = KycSubmission::with('user')->find($editId);
        }

        $kycSubmissions = KycSubmission::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($submission) {
                return [
                    'id' => $submission->id,
                    'user_id' => $submission->user_id,
                    'user_email' => $submission->user ? $submission->user->email : 'N/A',
                    'full_name' => $submission->full_name,
                    'dob' => $submission->dob,
                    'front_image' => $submission->front_image,
                    'back_image' => $submission->back_image,
                    'status' => $submission->status,
                    'admin_notes' => $submission->admin_notes,
                    'didit_request_id' => $submission->didit_request_id,
                    'didit_status' => $submission->didit_status,
                    'didit_verified_at' => $submission->didit_verified_at,
                    'created_at' => $submission->created_at,
                ];
            });

        return view('admin.kyc.index', compact('kycSubmissions', 'editKYC'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'kyc_id' => 'required|integer|exists:kyc_submissions,id',
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $kyc = KycSubmission::findOrFail($request->kyc_id);
        
        $kyc->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes ?? $kyc->admin_notes,
            'updated_at' => now()
        ]);

        return redirect()->route('admin.kyc.index')
            ->with('message', 'KYC status updated successfully.')
            ->with('messageType', 'success');
    }
}



















