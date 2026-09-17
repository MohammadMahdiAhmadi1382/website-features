<?php

declare(strict_types=1);

use Codav\WebsiteFeatures\Http\Controllers\WebsiteFeatureController;
use Illuminate\Support\Facades\Route;

Route::prefix(
    config(
        "website-features.routes.prefix",
        "website-features",
    ),
)
    ->middleware(
        config(
            "website-features.routes.middleware",
            [],
        ),
    )
    ->group(function (): void {
        Route::post(
            "reorder",
            [
                WebsiteFeatureController::class,
                "reorder",
            ],
        )->name(
            "website-features.reorder",
        );

        Route::get(
            "/",
            [
                WebsiteFeatureController::class,
                "index",
            ],
        )->name(
            "website-features.index",
        );

        Route::post(
            "/",
            [
                WebsiteFeatureController::class,
                "store",
            ],
        )->name(
            "website-features.store",
        );

        Route::get(
            "{feature}",
            [
                WebsiteFeatureController::class,
                "show",
            ],
        )->name(
            "website-features.show",
        );

        Route::put(
            "{feature}",
            [
                WebsiteFeatureController::class,
                "update",
            ],
        )->name(
            "website-features.update",
        );

        Route::delete(
            "{feature}",
            [
                WebsiteFeatureController::class,
                "destroy",
            ],
        )->name(
            "website-features.destroy",
        );

        Route::patch(
            "{feature}/activate",
            [
                WebsiteFeatureController::class,
                "activate",
            ],
        )->name(
            "website-features.activate",
        );

        Route::patch(
            "{feature}/deactivate",
            [
                WebsiteFeatureController::class,
                "deactivate",
            ],
        )->name(
            "website-features.deactivate",
        );

        Route::patch(
            "{feature}/toggle",
            [
                WebsiteFeatureController::class,
                "toggle",
            ],
        )->name(
            "website-features.toggle",
        );

        Route::patch(
            "{feature}/move-up",
            [
                WebsiteFeatureController::class,
                "moveUp",
            ],
        )->name(
            "website-features.move-up",
        );

        Route::patch(
            "{feature}/move-down",
            [
                WebsiteFeatureController::class,
                "moveDown",
            ],
        )->name(
            "website-features.move-down",
        );

        Route::patch(
            "{feature}/move-to",
            [
                WebsiteFeatureController::class,
                "moveTo",
            ],
        )->name(
            "website-features.move-to",
        );
    });