<?php
/**
 * Pagination Helper
 */

declare(strict_types=1);

namespace App\Helpers;

class Paginator
{
    public int $total;
    public int $perPage;
    public int $currentPage;
    public int $lastPage;
    public int $offset;

    public function __construct(int $total, int $perPage = 12, int $currentPage = 1)
    {
        $this->total       = $total;
        $this->perPage     = max(1, $perPage);
        $this->lastPage    = max(1, (int) ceil($total / $this->perPage));
        $this->currentPage = max(1, min($currentPage, $this->lastPage));
        $this->offset      = ($this->currentPage - 1) * $this->perPage;
    }

    public static function fromRequest(int $total, int $perPage = 12): self
    {
        $page = (int) ($_GET['page'] ?? 1);
        return new self($total, $perPage, $page);
    }

    public function hasPrevious(): bool { return $this->currentPage > 1; }
    public function hasNext(): bool     { return $this->currentPage < $this->lastPage; }

    /** Render Bootstrap 5 pagination links */
    public function render(string $baseUrl = ''): string
    {
        if ($this->lastPage <= 1) return '';

        $baseUrl = $baseUrl ?: Url::current();
        // Strip existing page param
        $baseUrl = preg_replace('/([?&])page=\d+/', '$1', $baseUrl);
        $sep = str_contains($baseUrl, '?') ? '&' : '?';

        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center flex-wrap">';

        // Previous
        $prevDisabled = !$this->hasPrevious() ? ' disabled' : '';
        $prevPage     = $this->currentPage - 1;
        $html .= "<li class=\"page-item{$prevDisabled}\">
            <a class=\"page-link\" href=\"{$baseUrl}{$sep}page={$prevPage}\" aria-label=\"Previous\">
                <span aria-hidden=\"true\">&laquo;</span>
            </a></li>";

        // Pages
        $range = $this->pageRange();
        $prevWasEllipsis = false;
        foreach ($range as $page) {
            if ($page === '...') {
                if (!$prevWasEllipsis) {
                    $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                $prevWasEllipsis = true;
                continue;
            }
            $prevWasEllipsis = false;
            $active = ($page === $this->currentPage) ? ' active' : '';
            $html .= "<li class=\"page-item{$active}\">
                <a class=\"page-link\" href=\"{$baseUrl}{$sep}page={$page}\">{$page}</a></li>";
        }

        // Next
        $nextDisabled = !$this->hasNext() ? ' disabled' : '';
        $nextPage     = $this->currentPage + 1;
        $html .= "<li class=\"page-item{$nextDisabled}\">
            <a class=\"page-link\" href=\"{$baseUrl}{$sep}page={$nextPage}\" aria-label=\"Next\">
                <span aria-hidden=\"true\">&raquo;</span>
            </a></li>";

        $html .= '</ul></nav>';
        return $html;
    }

    /** Generate page range with ellipsis */
    private function pageRange(): array
    {
        $range = [];
        $window = 2; // pages around current
        for ($i = 1; $i <= $this->lastPage; $i++) {
            if ($i === 1 || $i === $this->lastPage ||
                ($i >= $this->currentPage - $window && $i <= $this->currentPage + $window)) {
                $range[] = $i;
            } else {
                $range[] = '...';
            }
        }
        // De-duplicate consecutive ellipsis
        return array_values(array_filter($range, function ($v, $k) use ($range) {
            return $v !== '...' || ($k === 0 || $range[$k - 1] !== '...');
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function info(): string
    {
        $from = $this->offset + 1;
        $to   = min($this->offset + $this->perPage, $this->total);
        return "Showing {$from}–{$to} of {$this->total} results";
    }
}
