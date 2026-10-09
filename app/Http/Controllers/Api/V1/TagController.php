<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Http\Responses\ApiResponse;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function __construct(private readonly TagService $tagService) {}

    public function index(): JsonResponse
    {
        $tags = $this->tagService->list();

        return ApiResponse::success(
            'Tags récupérés.',
            TagResource::collection($tags)->resolve(),
        );
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tagService->create($request->validated());

        return ApiResponse::success(
            'Tag créé.',
            (new TagResource($tag))->resolve(),
            Response::HTTP_CREATED,
        );
    }

    public function show(Tag $tag): JsonResponse
    {
        return ApiResponse::success(
            'Tag récupéré.',
            (new TagResource($tag->loadCount('notes')))->resolve(),
        );
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $tag = $this->tagService->update($tag, $request->validated());

        return ApiResponse::success(
            'Tag mis à jour.',
            (new TagResource($tag->loadCount('notes')))->resolve(),
        );
    }

    public function destroy(Tag $tag): JsonResponse
    {
        if ($this->tagService->isUsed($tag)) {
            return ApiResponse::error(
                'Ce tag ne peut pas être supprimé car il est utilisé par une ou plusieurs notes.',
                null,
                Response::HTTP_CONFLICT,
            );
        }

        $this->tagService->delete($tag);

        return ApiResponse::success('Tag supprimé.');
    }
}
