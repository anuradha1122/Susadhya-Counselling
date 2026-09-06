<?php

use App\Http\Controllers\PublicSite\CmsPageController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\CounsellorController;
use App\Http\Controllers\PublicSite\FaqController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\RobotsController;
use App\Http\Controllers\PublicSite\ServiceController;
use App\Http\Controllers\PublicSite\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    HomeController::class
)->name('public.home');

Route::get(
    '/about',
    [
        CmsPageController::class,
        'about',
    ]
)->name('public.about');

Route::get(
    '/services',
    [
        ServiceController::class,
        'index',
    ]
)->name(
    'public.services.index'
);

Route::get(
    '/services/{service:slug}',
    [
        ServiceController::class,
        'show',
    ]
)->name(
    'public.services.show'
);

Route::get(
    '/counsellors',
    [
        CounsellorController::class,
        'index',
    ]
)->name(
    'public.counsellors.index'
);

Route::get(
    '/counsellors/{counsellor:uuid}',
    [
        CounsellorController::class,
        'show',
    ]
)->name(
    'public.counsellors.show'
);

Route::get(
    '/faq',
    FaqController::class
)->name('public.faq');

Route::get(
    '/contact',
    ContactController::class
)->name(
    'public.contact'
);

Route::get(
    '/sitemap.xml',
    SitemapController::class
)->name(
    'public.sitemap'
);

Route::get(
    '/robots.txt',
    RobotsController::class
)->name(
    'public.robots'
);

Route::get(
    '/pages/{slug}',
    [
        CmsPageController::class,
        'show',
    ]
)
    ->where(
        'slug',
        '[a-z0-9]+(?:-[a-z0-9]+)*'
    )
    ->name(
        'public.page.show'
    );
