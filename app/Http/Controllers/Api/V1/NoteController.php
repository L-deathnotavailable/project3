<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notes = $request->user()->notes()->with('tag')->latest()->get();

        return ApiResponse::success(
            'Notes récupérées.',
            NoteResource::collection($notes)->resolve(),
        );
    }

    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = $request->user()->notes()->create($request->validated());
        $note->load('tag');

        return ApiResponse::success(
            'Note créée.',
            (new NoteResource($note))->resolve(),
            Response::HTTP_CREATED,
        );
    }

    public function show(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        return ApiResponse::success(
            'Note récupérée.',
            (new NoteResource($note->load('tag')))->resolve(),
        );
    }

    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        $this->authorize('update', $note);

        $note->update($request->validated());

        return ApiResponse::success(
            'Note mise à jour.',
            (new NoteResource($note->load('tag')))->resolve(),
        );
    }

    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);
        $note->delete();

        return ApiResponse::success('Note supprimée.');
    }
}
