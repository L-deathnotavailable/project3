<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    public function __construct(private readonly NoteService $noteService) {}

    public function index(Request $request): JsonResponse
    {
        $notes = $this->noteService->listFor($request->user());

        return ApiResponse::success(
            'Notes récupérées.',
            NoteResource::collection($notes)->resolve(),
        );
    }

    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = $this->noteService->createFor($request->user(), $request->validated());

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

        $note = $this->noteService->update($note, $request->validated());

        return ApiResponse::success(
            'Note mise à jour.',
            (new NoteResource($note->load('tag')))->resolve(),
        );
    }

    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);
        $this->noteService->delete($note);

        return ApiResponse::success('Note supprimée.');
    }
}
