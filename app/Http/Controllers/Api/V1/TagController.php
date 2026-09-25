<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Http\Responses\ApiResponse;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::query()->withCount('notes')->orderBy('name')->get();

        return ApiResponse::success(
            'Tags récupérés.',
            TagResource::collection($tags)->resolve(),
        );
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = Tag::query()->create($request->validated());
        $tag->loadCount('notes');

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
        $tag->update($request->validated());

        return ApiResponse::success(
            'Tag mis à jour.',
            (new TagResource($tag->loadCount('notes')))->resolve(),
        );
    }

    public function destroy(Tag $tag): JsonResponse
    {
        if ($tag->notes()->exists()) {
            return ApiResponse::error(
                'Ce tag ne peut pas être supprimé car il est utilisé par une ou plusieurs notes.',
                null,
                Response::HTTP_CONFLICT,
            );
        }

        $tag->delete();

        return ApiResponse::success('Tag supprimé.');
    }
}
