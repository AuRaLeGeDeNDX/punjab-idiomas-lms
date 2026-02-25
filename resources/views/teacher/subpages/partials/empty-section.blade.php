{{-- Empty section placeholder --}}
<div class="empty-section text-center py-4 border border-dashed rounded">
    <i class="{{ $section['icon'] }} fa-2x text-muted mb-2"></i>
    <p class="text-muted mb-2">No content in {{ $section['label'] }} yet</p>
    <button type="button" class="btn btn-sm btn-primary" data-add-content data-section="{{ $section['value'] }}">
        <i class="fas fa-plus"></i> Add First Block
    </button>
</div>