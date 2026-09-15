<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DocumentsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
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
        return new DocumentResource($document->load(['user', 'category']));
    }

    public function update(DocumentRequest $request, Document $document): DocumentResource
    {
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

    public function destroy(Request $request, Document $document): Response
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $document->delete();

        return response()->noContent();
    }
}
