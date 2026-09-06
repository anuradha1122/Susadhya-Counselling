<?php

use App\Http\Controllers\Admin\Cms\CmsDashboardController;
use App\Http\Controllers\Admin\Cms\CmsFaqController;
use App\Http\Controllers\Admin\Cms\CmsMediaController;
use App\Http\Controllers\Admin\Cms\CmsTestimonialController;
use App\Http\Controllers\Admin\Cms\FixedCmsContentController;
use App\Http\Controllers\Admin\Cms\PublicSiteSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'active',
    'can:cms.view',
])
    ->prefix('admin/cms')
    ->name('admin.cms.')
    ->group(function (): void {
        /*
        |--------------------------------------------------------------------------
        | CMS Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            CmsDashboardController::class
        )->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Fixed Website Content
        |--------------------------------------------------------------------------
        |
        | Website structure is controlled by the application.
        |
        | Administrators can edit:
        | - headings
        | - descriptions
        | - images
        | - buttons
        | - SEO
        | - fixed section content
        |
        | Administrators cannot:
        | - create pages
        | - create sections
        | - remove sections
        | - change section types
        | - reorder sections
        |
        */

        Route::middleware(
            'can:cms.pages.manage'
        )->group(function (): void {
            Route::get(
                '/content',
                [
                    FixedCmsContentController::class,
                    'index',
                ]
            )->name('content.index');

            Route::get(
                '/content/{page}',
                [
                    FixedCmsContentController::class,
                    'edit',
                ]
            )
                ->whereIn(
                    'page',
                    array_keys(
                        config(
                            'cms_fixed.pages',
                            []
                        )
                    )
                )
                ->name('content.edit');

            Route::patch(
                '/content/{page}/page',
                [
                    FixedCmsContentController::class,
                    'updatePage',
                ]
            )
                ->whereIn(
                    'page',
                    array_keys(
                        config(
                            'cms_fixed.pages',
                            []
                        )
                    )
                )
                ->name(
                    'content.page.update'
                );
        });

        /*
        |--------------------------------------------------------------------------
        | Fixed Section Content Updates
        |--------------------------------------------------------------------------
        |
        | These routes update only existing developer-defined content slots.
        | There are deliberately no create, delete, archive, toggle or move routes.
        */

        Route::middleware(
            'can:cms.sections.manage'
        )->group(function (): void {
            Route::patch(
                '/content/{page}/{section}',
                [
                    FixedCmsContentController::class,
                    'updateSection',
                ]
            )
                ->whereIn(
                    'page',
                    array_keys(
                        config(
                            'cms_fixed.pages',
                            []
                        )
                    )
                )
                ->name(
                    'content.section.update'
                );
        });

        /*
        |--------------------------------------------------------------------------
        | Public Website Settings
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            'can:cms.settings.manage'
        )->group(function (): void {
            Route::get(
                '/settings',
                [
                    PublicSiteSettingController::class,
                    'edit',
                ]
            )->name(
                'settings.edit'
            );

            Route::patch(
                '/settings',
                [
                    PublicSiteSettingController::class,
                    'update',
                ]
            )->name(
                'settings.update'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | FAQ Management
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            'can:cms.faqs.manage'
        )->group(function (): void {
            Route::get(
                '/faqs',
                [
                    CmsFaqController::class,
                    'index',
                ]
            )->name(
                'faqs.index'
            );

            Route::post(
                '/faqs',
                [
                    CmsFaqController::class,
                    'store',
                ]
            )->name(
                'faqs.store'
            );

            Route::patch(
                '/faqs/{cmsFaq}',
                [
                    CmsFaqController::class,
                    'update',
                ]
            )->name(
                'faqs.update'
            );

            Route::post(
                '/faqs/{cmsFaq}/toggle',
                [
                    CmsFaqController::class,
                    'toggle',
                ]
            )->name(
                'faqs.toggle'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Testimonial Management
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            'can:cms.testimonials.manage'
        )->group(function (): void {
            Route::get(
                '/testimonials',
                [
                    CmsTestimonialController::class,
                    'index',
                ]
            )->name(
                'testimonials.index'
            );

            Route::post(
                '/testimonials',
                [
                    CmsTestimonialController::class,
                    'store',
                ]
            )->name(
                'testimonials.store'
            );

            Route::patch(
                '/testimonials/{cmsTestimonial}',
                [
                    CmsTestimonialController::class,
                    'update',
                ]
            )->name(
                'testimonials.update'
            );

            Route::post(
                '/testimonials/{cmsTestimonial}/archive',
                [
                    CmsTestimonialController::class,
                    'archive',
                ]
            )->name(
                'testimonials.archive'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Media Library
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            'can:cms.media.manage'
        )->group(function (): void {
            Route::get(
                '/media',
                [
                    CmsMediaController::class,
                    'index',
                ]
            )->name(
                'media.index'
            );

            Route::post(
                '/media',
                [
                    CmsMediaController::class,
                    'store',
                ]
            )->name(
                'media.store'
            );

            Route::post(
                '/media/{cmsMedia}/archive',
                [
                    CmsMediaController::class,
                    'archive',
                ]
            )->name(
                'media.archive'
            );

            Route::post(
                '/media/{cmsMedia}/restore',
                [
                    CmsMediaController::class,
                    'restore',
                ]
            )->name(
                'media.restore'
            );
        });
    });
