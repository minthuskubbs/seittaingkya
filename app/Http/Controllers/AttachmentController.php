<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /** Whitelisted attachable models. */
    private array $types = [
        'patient' => Patient::class,
        'treatment' => Treatment::class,
    ];

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:patient,treatment',
            'id' => 'required|integer',
            'category' => 'required|in:xray,document',
            // png, pdf, jpg, jpeg and microsoft word type
            'files' => 'required|array',
            'files.*' => 'file|max:20480|mimes:png,jpg,jpeg,pdf,doc,docx',
        ]);

        $model = $this->types[$request->type]::findOrFail($request->id);

        foreach ($request->file('files') as $file) {
            $path = $file->store('attachments/'.$request->type, 'public');
            $model->attachments()->create([
                'clinic_id' => $model->clinic_id,
                'category' => $request->category,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        return back()->with('status', 'File(s) uploaded.');
    }

    public function destroy(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('status', 'Attachment deleted.');
    }
}
