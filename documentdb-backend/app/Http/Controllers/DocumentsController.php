<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class DocumentsController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): AnonymousResourceCollection
    {
        // Semua role yang sudah login boleh lihat senarai (auth:sanctum cukup).
        // Uncomment Policy di bawah untuk demo viewAny:
        // $this->authorize('viewAny', Document::class);

        $query = Document::query()
            ->with(['user', 'category'])
            ->orderBy('created_at', 'asc');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->string('search').'%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return DocumentResource::collection($query->paginate());
    }

    public function store(DocumentRequest $request): DocumentResource
    {
        // --- DEMO GATE (aktif) ---
        Gate::authorize('create-document');

        // --- DEMO POLICY (uncomment untuk demo Policy; comment Gate di atas jika mahu asingkan) ---
        $this->authorize('create', Document::class);

        $documentKey = null;

        if ($request->hasFile('document')) {
            $documentKey = $request->file('document')->store(
                'documents/'.now()->format('d-m-Y'),
                'r2'
            );
        }

        $document = Document::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'document_key' => $documentKey,
            'user_id' => $request->user()->id,
            'category_id' => $request->input('category_id'),
        ]);

        return new DocumentResource($document->load(['user', 'category']));
    }

    public function show(Document $document): DocumentResource
    {
        // $this->authorize('view', $document);

        return new DocumentResource($document->load(['user', 'category']));
    }

    public function update(DocumentRequest $request, Document $document): DocumentResource
    {
        // --- DEMO GATE (aktif) ---
        Gate::authorize('update-document');

        // --- DEMO POLICY (uncomment untuk demo Policy) ---
        // $this->authorize('update', $document);

        $data = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category_id' => $request->input('category_id'),
        ];

        if ($request->hasFile('document')) {
            $data['document_key'] = $request->file('document')->store(
                'documents/'.now()->format('d-m-Y'),
                'r2'
            );
        }

        $document->update($data);

        return new DocumentResource($document->load(['user', 'category']));
    }

    public function destroy(Document $document): Response
    {
        // --- DEMO GATE (aktif) ---
        Gate::authorize('delete-document');

        // --- DEMO POLICY (uncomment untuk demo Policy) ---
        // $this->authorize('delete', $document);

        $document->delete();

        return response()->noContent();
    }
}
