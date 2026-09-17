<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures\Http\Controllers;

use Codav\WebsiteFeatures\Http\Requests\ReorderWebsiteFeaturesRequest;
use Codav\WebsiteFeatures\Http\Requests\StoreWebsiteFeatureRequest;
use Codav\WebsiteFeatures\Http\Requests\UpdateWebsiteFeatureRequest;
use Codav\WebsiteFeatures\Http\Resources\WebsiteFeatureResource;
use Codav\WebsiteFeatures\Models\WebsiteFeature;
use Codav\WebsiteFeatures\Services\WebsiteFeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class WebsiteFeatureController extends Controller
{
    public function __construct(
        private readonly WebsiteFeatureService $service,
    ) {
    }

    public function index(
        Request $request,
    ): AnonymousResourceCollection {
        $query = WebsiteFeature::query()->search(
            $request->input("search"),
        );

        if ($request->has("active")) {
            $request->boolean("active")
                ? $query->active()
                : $query->inactive();
        }

        $features = $query
            ->ordered()
            ->paginate(
                max(
                    1,
                    min(
                        $request->integer(
                            "per_page",
                            15,
                        ),
                        100,
                    ),
                ),
            );

        return WebsiteFeatureResource::collection(
            $features,
        );
    }

    public function store(
        StoreWebsiteFeatureRequest $request,
    ): WebsiteFeatureResource {
        return new WebsiteFeatureResource(
            $this->service->create(
                $request->validated(),
            ),
        );
    }

    public function show(
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $websiteFeature,
        );
    }

    public function update(
        UpdateWebsiteFeatureRequest $request,
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $this->service->update(
                $websiteFeature,
                $request->validated(),
            ),
        );
    }

    public function destroy(
        string $feature,
    ): JsonResponse {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        $this->service->delete($websiteFeature);

        return response()->json([
            "message" =>
                "Website feature deleted successfully.",
        ]);
    }

    public function activate(
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $this->service->activate(
                $websiteFeature,
            ),
        );
    }

    public function deactivate(
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $this->service->deactivate(
                $websiteFeature,
            ),
        );
    }

    public function toggle(
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $this->service->toggle(
                $websiteFeature,
            ),
        );
    }

    public function moveUp(
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $this->service->moveUp(
                $websiteFeature,
            ),
        );
    }

    public function moveDown(
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        return new WebsiteFeatureResource(
            $this->service->moveDown(
                $websiteFeature,
            ),
        );
    }

    public function moveTo(
        Request $request,
        string $feature,
    ): WebsiteFeatureResource {
        $websiteFeature = WebsiteFeature::query()
            ->where("slug", $feature)
            ->firstOrFail();

        $validated = $request->validate([
            "position" => [
                "required",
                "integer",
                "min:1",
            ],
        ]);

        return new WebsiteFeatureResource(
            $this->service->moveTo(
                $websiteFeature,
                (int) $validated["position"],
            ),
        );
    }

    public function reorder(
        ReorderWebsiteFeaturesRequest $request,
    ): AnonymousResourceCollection {
        return WebsiteFeatureResource::collection(
            $this->service->reorder(
                $request->validated("features"),
            ),
        );
    }
}