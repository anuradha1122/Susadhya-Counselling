<?php

namespace App\Services\Cms;

use App\Models\CmsPage;
use App\Models\CmsSection;
use Illuminate\Support\Collection;

class FixedCmsContentService
{
    public function pageConfig(
        string $slug
    ): array {
        $config =
            config(
                "cms_fixed.pages.{$slug}"
            );

        abort_unless(
            is_array($config),
            404
        );

        return $config;
    }

    public function page(
        string $slug
    ): CmsPage {
        return CmsPage::query()
            ->where(
                'slug',
                $slug
            )
            ->firstOrFail();
    }

    public function editorPayload(
        string $slug
    ): array {
        $page =
            $this->page($slug);

        $config =
            $this->pageConfig($slug);

        $storedSections =
            $page
                ->sections()
                ->get()
                ->keyBy('key');

        $sections =
            collect(
                $config['sections']
                ?? []
            )
                ->map(
                    function (
                        array $sectionConfig,
                        string $key
                    ) use (
                        $storedSections
                    ): array {
                        $stored =
                            $storedSections
                                ->get($key);

                        return $this
                            ->resolvedSection(
                                $key,
                                $sectionConfig,
                                $stored
                            );
                    }
                )
                ->values();

        return [
            'pageDefinition' => [
                'slug' => $slug,

                'label' => $config['label']
                    ?? ucfirst($slug),
            ],

            'page' => [
                'title' => $page->title,

                'excerpt' => $page->excerpt,

                'body' => $page->body,

                'meta_title' => $page->meta_title,

                'meta_description' => $page
                    ->meta_description,

                'og_image_path' => $page
                    ->og_image_path,

                'robots_index' => (bool)
                    $page
                        ->robots_index,

                'robots_follow' => (bool)
                    $page
                        ->robots_follow,
            ],

            'sections' => $sections,
        ];
    }

    public function publicPayload(
        string $slug
    ): array {
        $page =
            $this->page($slug);

        $config =
            $this->pageConfig($slug);

        $storedSections =
            $page
                ->sections()
                ->where(
                    'is_active',
                    true
                )
                ->get()
                ->keyBy('key');

        $sections =
            collect(
                $config['sections']
                ?? []
            )
                ->mapWithKeys(
                    function (
                        array $sectionConfig,
                        string $key
                    ) use (
                        $storedSections
                    ): array {
                        return [
                            $key => $this
                                ->resolvedSection(
                                    $key,
                                    $sectionConfig,
                                    $storedSections
                                        ->get(
                                            $key
                                        )
                                ),
                        ];
                    }
                )
                ->all();

        return [
            'page' => [
                'title' => $page->title,

                'excerpt' => $page->excerpt,

                'seo' => [
                    'title' => $page
                        ->meta_title
                        ?: $page->title,

                    'description' => $page
                        ->meta_description
                        ?: $page
                            ->excerpt,

                    'canonical' => $this
                        ->canonical(
                            $slug
                        ),

                    'og_title' => $page
                        ->og_title
                        ?: $page
                            ->meta_title
                        ?: $page->title,

                    'og_description' => $page
                        ->og_description
                        ?: $page
                            ->meta_description
                        ?: $page
                            ->excerpt,

                    'og_image' => $page
                        ->og_image_path,

                    'robots' => sprintf(
                        '%s,%s',
                        $page
                            ->robots_index
                            ? 'index'
                            : 'noindex',
                        $page
                            ->robots_follow
                            ? 'follow'
                            : 'nofollow'
                    ),
                ],
            ],

            'sections' => $sections,
        ];
    }

    public function sectionDefinition(
        string $page,
        string $section
    ): array {
        $definition =
            config(
                "cms_fixed.pages.{$page}.sections.{$section}"
            );

        abort_unless(
            is_array($definition),
            404
        );

        return $definition;
    }

    public function saveSection(
        string $pageSlug,
        string $sectionKey,
        array $data,
        int $userId
    ): CmsSection {
        $page =
            $this->page(
                $pageSlug
            );

        $definition =
            $this
                ->sectionDefinition(
                    $pageSlug,
                    $sectionKey
                );

        $section =
            CmsSection::query()
                ->firstOrNew([
                    'cms_page_id' => $page->id,

                    'key' => $sectionKey,
                ]);

        if (! $section->exists) {
            $section->created_by =
                $userId;

            $section->display_order =
                $this
                    ->sectionOrder(
                        $pageSlug,
                        $sectionKey
                    );
        }

        $section->type =
            $definition['type'];

        $section->heading =
            $data['heading']
            ?? null;

        $section->subheading =
            $data['subheading']
            ?? null;

        $section->content =
            $data['content']
            ?? [];

        $section->settings = [];

        $section->is_active = true;

        $section->updated_by =
            $userId;

        $section->save();

        return $section;
    }

    public function fixedPages(): Collection
    {
        return collect(
            config(
                'cms_fixed.pages',
                []
            )
        )
            ->map(
                fn (
                    array $page,
                    string $slug
                ): array => [
                    'slug' => $slug,

                    'label' => $page['label']
                        ?? ucfirst(
                            $slug
                        ),

                    'section_count' => count(
                        $page[
                            'sections'
                        ]
                        ?? []
                    ),
                ]
            )
            ->values();
    }

    private function resolvedSection(
        string $key,
        array $definition,
        ?CmsSection $stored
    ): array {
        return [
            'key' => $key,

            'label' => $definition['label']
                ?? ucfirst($key),

            'type' => $definition['type'],

            'heading' => $stored?->heading
                ?? $definition[
                    'heading'
                ]
                ?? null,

            'subheading' => $stored?->subheading
                ?? $definition[
                    'subheading'
                ]
                ?? null,

            'content' => array_replace_recursive(
                $definition[
                    'content'
                ]
                ?? [],
                $stored?->content
                ?? []
            ),

            'fields' => $definition['fields']
                ?? [],
        ];
    }

    private function sectionOrder(
        string $page,
        string $section
    ): int {
        $keys =
            array_keys(
                config(
                    "cms_fixed.pages.{$page}.sections",
                    []
                )
            );

        $position =
            array_search(
                $section,
                $keys,
                true
            );

        if ($position === false) {
            return 1000;
        }

        return (
            $position + 1
        ) * 10;
    }

    private function canonical(
        string $slug
    ): string {
        return match ($slug) {
            'home' => route(
                'public.home'
            ),

            'about' => route(
                'public.about'
            ),

            'services' => route(
                'public.services.index'
            ),

            'counsellors' => route(
                'public.counsellors.index'
            ),

            'faq' => route(
                'public.faq'
            ),

            'contact' => route(
                'public.contact'
            ),

            default => route(
                'public.home'
            ),
        };
    }
}
