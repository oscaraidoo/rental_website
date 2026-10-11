<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\{Security, Request, Database, Str, Upload};
use App\Models\HomeSlideModel;
use App\Services\AuditService;

class CmsController extends BaseController
{
    // ── CMS Pages ─────────────────────────────────────────────

    public function index(): void
    {
        $this->adminOnly();
        $pages = Database::fetchAll("SELECT * FROM cms_pages ORDER BY sort_order, title");
        $this->adminView('admin.cms.index', compact('pages') + [
            'title' => 'CMS Pages', 'breadcrumb' => ['CMS'],
        ]);
    }

    public function edit(string $slug): void
    {
        $this->adminOnly();
        $page = Database::fetchOne("SELECT * FROM cms_pages WHERE slug = ?", [$slug]);
        if (!$page) $this->notFound();

        $this->adminView('admin.cms.edit', compact('page') + [
            'title' => 'Edit: ' . $page['title'], 'breadcrumb' => ['CMS', $page['title']],
        ]);
    }

    public function update(string $slug): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $page = Database::fetchOne("SELECT * FROM cms_pages WHERE slug = ?", [$slug]);
        if (!$page) $this->notFound();

        $data = [
            'title'            => Security::sanitize(Request::post('title', '')),
            'content'          => Request::post('content', ''),   // allow HTML from editor
            'meta_title'       => Security::sanitize(Request::post('meta_title', '')),
            'meta_description' => Security::sanitize(Request::post('meta_description', '')),
            'status'           => Request::post('status', 'published') === 'published' ? 'published' : 'draft',
            'updated_by'       => $this->currentUserId(),
        ];

        Database::update('cms_pages', $data, 'slug = ?', [$slug]);
        AuditService::log('update_cms_page', 'cms', (int) $page['id'], 'cms_page', $page, $data);

        $this->success("Page \"{$data['title']}\" updated.");
        $this->redirect('admin/cms');
    }

    // ── FAQs ──────────────────────────────────────────────────

    public function faqs(): void
    {
        $this->adminOnly();
        $faqs = Database::fetchAll("SELECT * FROM faqs ORDER BY category, sort_order");
        $this->adminView('admin.cms.faqs', compact('faqs') + [
            'title' => 'FAQs', 'breadcrumb' => ['CMS', 'FAQs'],
        ]);
    }

    public function storeFaq(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $data = [
            'category'   => Security::sanitize(Request::post('category', 'general')),
            'question'   => Security::sanitize(Request::post('question', '')),
            'answer'     => Request::post('answer', ''),
            'sort_order' => (int) Request::post('sort_order', 0),
            'active'     => 1,
        ];

        if (empty($data['question']) || empty($data['answer'])) {
            $this->error('Question and answer are required.');
            $this->redirect('admin/faqs');
        }

        Database::insert('faqs', $data);
        $this->success('FAQ added.');
        $this->redirect('admin/faqs');
    }

    public function updateFaq(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $data = [
            'question'   => Security::sanitize(Request::post('question', '')),
            'answer'     => Request::post('answer', ''),
            'category'   => Security::sanitize(Request::post('category', 'general')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'active'     => (int) !empty(Request::post('active')),
        ];

        Database::update('faqs', $data, 'id = ?', [(int) $id]);
        $this->success('FAQ updated.');
        $this->redirect('admin/faqs');
    }

    public function deleteFaq(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();
        Database::query("DELETE FROM faqs WHERE id = ?", [(int) $id]);
        $this->success('FAQ deleted.');
        $this->redirect('admin/faqs');
    }

    // ── Blog ──────────────────────────────────────────────────

    public function blog(): void
    {
        $this->adminOnly();
        $posts = Database::fetchAll(
            "SELECT bp.*, CONCAT(u.first_name,' ',u.last_name) AS author_name
             FROM blog_posts bp JOIN users u ON u.id = bp.author_id
             ORDER BY bp.created_at DESC LIMIT 50"
        );
        $this->adminView('admin.cms.blog', compact('posts') + [
            'title' => 'Blog Posts', 'breadcrumb' => ['CMS', 'Blog'],
        ]);
    }

    public function createBlog(): void
    {
        $this->adminOnly();
        $this->adminView('admin.cms.blog_create', ['title' => 'New Blog Post', 'breadcrumb' => ['CMS', 'Blog', 'New']]);
    }

    public function storeBlog(): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $title   = Security::sanitize(Request::post('title', ''));
        $status  = Request::post('status', 'draft');

        if (empty($title)) {
            $this->error('Title is required.');
            $this->redirect('admin/blog/create');
        }

        $slug = Str::slug($title);
        $existing = Database::fetchColumn("SELECT id FROM blog_posts WHERE slug = ?", [$slug]);
        if ($existing) $slug .= '-' . time();

        $data = [
            'author_id'       => $this->currentUserId(),
            'category'        => Security::sanitize(Request::post('category', '')),
            'title'           => $title,
            'slug'            => $slug,
            'excerpt'         => Security::sanitize(Request::post('excerpt', '')),
            'content'         => Request::post('content', ''),
            'status'          => in_array($status, ['published','draft','archived']) ? $status : 'draft',
            'published_at'    => $status === 'published' ? date('Y-m-d H:i:s') : null,
            'meta_title'      => Security::sanitize(Request::post('meta_title', '')),
            'meta_description'=> Security::sanitize(Request::post('meta_description', '')),
        ];

        Database::insert('blog_posts', $data);
        $this->success('Blog post created.');
        $this->redirect('admin/blog');
    }

    public function editBlog(string $id): void
    {
        $this->adminOnly();
        $post = Database::fetchOne("SELECT * FROM blog_posts WHERE id = ?", [(int) $id]);
        if (!$post) $this->notFound();

        $this->adminView('admin.cms.blog_edit', compact('post') + [
            'title' => 'Edit Post', 'breadcrumb' => ['CMS', 'Blog', 'Edit'],
        ]);
    }

    public function updateBlog(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $post   = Database::fetchOne("SELECT * FROM blog_posts WHERE id = ?", [(int) $id]);
        if (!$post) $this->notFound();

        $status = Request::post('status', 'draft');
        $data   = [
            'title'            => Security::sanitize(Request::post('title', '')),
            'category'         => Security::sanitize(Request::post('category', '')),
            'excerpt'          => Security::sanitize(Request::post('excerpt', '')),
            'content'          => Request::post('content', ''),
            'status'           => in_array($status, ['published','draft','archived']) ? $status : 'draft',
            'meta_title'       => Security::sanitize(Request::post('meta_title', '')),
            'meta_description' => Security::sanitize(Request::post('meta_description', '')),
        ];
        if ($status === 'published' && empty($post['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        Database::update('blog_posts', $data, 'id = ?', [(int) $id]);
        $this->success('Blog post updated.');
        $this->redirect('admin/blog');
    }

    public function deleteBlog(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $post = Database::fetchOne("SELECT title FROM blog_posts WHERE id = ?", [(int) $id]);
        if (!$post) $this->notFound();

        Database::query("DELETE FROM blog_posts WHERE id = ?", [(int) $id]);
        AuditService::log('delete_blog_post', 'cms', (int) $id, 'blog_post', $post, []);
        $this->success("Blog post \"{$post['title']}\" deleted.");
        $this->redirect('admin/blog');
    }

    // ── Home Page Slides (hero slider: images & videos) ─────

    public function homeSlides(): void
    {
        $this->adminOnly();
        $slides = HomeSlideModel::all();
        $this->adminView('admin.cms.home_slides', compact('slides') + [
            'title' => 'Home Slides', 'breadcrumb' => ['CMS', 'Home Slides'],
        ]);
    }

    public function homeSlideStore(): void
    {
        $this->saveSlide(null);
    }

    public function homeSlideUpdate(string $id): void
    {
        $this->saveSlide((int) $id);
    }

    private function saveSlide(?int $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();

        $existing = $id ? HomeSlideModel::find($id) : null;
        if ($id && !$existing) $this->notFound();

        $type = Request::post('type', 'image') === 'video' ? 'video' : 'image';

        // ── Media resolution: uploaded file > pasted URL > keep existing ──
        $mediaPath = null;
        $file = Request::file('media_file');
        $url  = trim((string) Request::post('media_url', ''));

        if ($file !== null) {
            $up = Upload::handle($file, $type, 'hero');
            if (empty($up['success'])) {
                $this->error($up['error'] ?? 'Upload failed.');
                $this->redirect('admin/cms/home');
                return;
            }
            $mediaPath = $up['path'];
            // Replace: remove the old local file if there was one
            if ($existing && !empty($existing['media_path']) && str_starts_with($existing['media_path'], 'uploads/')) {
                Upload::delete($existing['media_path']);
            }
        } elseif ($url !== '') {
            if (!preg_match('#^https?://#i', $url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                $this->error('Media URL must be a valid http(s) link.');
                $this->redirect('admin/cms/home');
                return;
            }
            $mediaPath = $url;
        } elseif ($existing && !empty($existing['media_path'])) {
            $mediaPath = $existing['media_path']; // keep current media on plain text edit
        }

        if ($mediaPath === null) {
            $this->error('Please upload a file or paste a media URL for the slide.');
            $this->redirect('admin/cms/home');
            return;
        }

        $data = [
            'type'       => $type,
            'media_path' => $mediaPath,
            'label'      => Security::sanitize(Request::post('label', '')),
            'title'      => Security::sanitize(Request::post('title', '')),
            'subtitle'   => Security::sanitize(Request::post('subtitle', '')),
            'btn1_text'  => Security::sanitize(Request::post('btn1_text', '')),
            'btn1_url'   => Security::sanitize(Request::post('btn1_url', '')),
            'btn2_text'  => Security::sanitize(Request::post('btn2_text', '')),
            'btn2_url'   => Security::sanitize(Request::post('btn2_url', '')),
            'sort_order' => (int) Request::post('sort_order', 0),
            'active'     => (int) !empty(Request::post('active')),
        ];

        if ($existing) {
            HomeSlideModel::update((int) $existing['id'], $data);
            AuditService::log('update_home_slide', 'cms', (int) $existing['id'], 'home_slide', $existing, $data);
            $this->success('Slide updated.');
        } else {
            $newId = HomeSlideModel::create($data);
            AuditService::log('create_home_slide', 'cms', (int) $newId, 'home_slide', [], $data);
            $this->success('Slide added.');
        }
        $this->redirect('admin/cms/home');
    }

    public function homeSlideDelete(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();
        $slide = HomeSlideModel::find((int) $id);
        if (!$slide) $this->notFound();

        if (!empty($slide['media_path']) && str_starts_with($slide['media_path'], 'uploads/')) {
            Upload::delete($slide['media_path']);
        }
        HomeSlideModel::delete((int) $slide['id']);
        AuditService::log('delete_home_slide', 'cms', (int) $slide['id'], 'home_slide', $slide, []);
        $this->success('Slide deleted.');
        $this->redirect('admin/cms/home');
    }

    public function homeSlideToggle(string $id): void
    {
        $this->adminOnly();
        Security::requireCsrf();
        $slide = HomeSlideModel::find((int) $id);
        if (!$slide) $this->notFound();

        $newState = (int) !$slide['active'];
        HomeSlideModel::update((int) $slide['id'], ['active' => $newState]);
        AuditService::log('toggle_home_slide', 'cms', (int) $slide['id'], 'home_slide', $slide, ['active' => $newState]);
        $this->success($newState ? 'Slide is now visible on the home page.' : 'Slide hidden from the home page.');
        $this->redirect('admin/cms/home');
    }

    public function homeSlideMove(string $id, string $dir): void
    {
        $this->adminOnly();
        Security::requireCsrf();
        HomeSlideModel::move((int) $id, $dir === 'up' ? 'up' : 'down');
        $this->redirect('admin/cms/home');
    }
}
