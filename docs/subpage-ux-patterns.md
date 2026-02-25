# Subpage UX Patterns for Large Lists

## Overview

This document outlines UX patterns and implementations for managing large numbers of subpages in the Institute LMS. The patterns focus on performance, usability, and scalability.

## 1. Pagination & Lazy Loading Patterns

### Infinite Scroll with Intersection Observer

**Pattern**: Load content progressively as user scrolls
**Benefits**: 
- Reduces initial load time
- Smooth user experience
- Handles thousands of items efficiently

**Implementation**:
```javascript
// Intersection Observer for lazy loading
setupIntersectionObserver() {
  this.observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && this.hasMorePages && !this.isLoading) {
        this.loadMoreSubpages()
      }
    })
  }, { threshold: 0.1 })
}
```

**Key Features**:
- 20 items per page default
- Visual loading indicators
- Graceful error handling
- Maintains scroll position

### Virtual Scrolling (Advanced)

**Pattern**: Render only visible items in viewport
**Benefits**:
- Handles 10,000+ items smoothly
- Constant memory usage
- Instant scrolling

**Use Case**: When dealing with extremely large datasets (1000+ subpages)

## 2. Search & Filtering Patterns

### Real-time Search with Debouncing

**Pattern**: Search as user types with 300ms delay
**Benefits**:
- Immediate feedback
- Reduces server requests
- Highlights matching terms

**Implementation**:
```javascript
// Debounced search
debouncedSearch: debounce(function() {
  this.applyFilters()
}, 300)

// Highlight search terms
highlightSearchTerm(text) {
  const searchTerm = this.searchQuery.trim()
  if (!searchTerm) return text
  
  const regex = new RegExp(`(${searchTerm})`, 'gi')
  return text.replace(regex, '<span class="search-highlight">$1</span>')
}
```

### Multi-faceted Filtering

**Filters Available**:
- Status (Active/Inactive/Deleted)
- Content type
- Last modified date
- Content count
- Assignment count

**Pattern**: Combine multiple filters with clear visual feedback

### Advanced Search Features

**Keyboard Shortcuts**:
- `Ctrl/Cmd + F`: Focus search
- `Escape`: Clear search
- `Enter`: Apply filters

**Search Operators**:
- `title:lesson` - Search in title only
- `content:>5` - Items with more than 5 content pieces
- `modified:<7d` - Modified in last 7 days

## 3. View Modes & Layout Patterns

### Adaptive View Modes

**List View**: 
- Compact, information-dense
- Best for scanning many items
- Shows detailed metadata

**Grid View**:
- Visual cards with thumbnails
- Better for visual learners
- Shows content preview

**Kanban View**:
- Status-based columns (Draft/Active/Archived)
- Drag-and-drop status changes
- Visual workflow management

**Implementation**:
```javascript
// View persistence
setView(view) {
  this.currentView = view
  localStorage.setItem('subpage_view_preference', view)
}
```

### Responsive Design Patterns

**Mobile Adaptations**:
- Single column layout
- Swipe gestures for actions
- Collapsible details
- Touch-friendly buttons

**Tablet Adaptations**:
- Two-column grid
- Side panel for details
- Drag-and-drop support

## 4. Collapsible & Expandable Content

### Progressive Disclosure

**Pattern**: Show summary first, expand for details
**Benefits**:
- Reduces cognitive load
- Faster scanning
- Customizable detail level

**Implementation**:
```css
.subpage-item.collapsed .subpage-content {
  display: none;
}

.collapse-toggle {
  cursor: pointer;
  transition: transform 0.2s ease;
}

.collapse-toggle.collapsed {
  transform: rotate(-90deg);
}
```

### Bulk Collapse Controls

**Features**:
- "Collapse All" toggle
- Remember collapsed state
- Keyboard shortcuts (`C` for collapse all)

## 5. Quick Actions & Shortcuts

### Floating Action Button (FAB)

**Pattern**: Multi-level FAB with contextual actions
**Actions**:
- Quick create
- Create from template
- Import subpages
- Bulk operations

**Implementation**:
```vue
<div class="fab-container">
  <button class="fab fab-main" @click="toggleFab">
    <i class="fas" :class="fabOpen ? 'fa-times' : 'fa-plus'"></i>
  </button>
  
  <transition-group name="fab-item" class="fab-items">
    <button v-if="fabOpen" class="fab fab-item" @click="quickCreate">
      <i class="fas fa-plus"></i>
    </button>
  </transition-group>
</div>
```

### Keyboard Shortcuts

**Global Shortcuts**:
- `N`: New subpage
- `Ctrl/Cmd + A`: Select all
- `Delete`: Delete selected
- `Ctrl/Cmd + D`: Duplicate selected

**Item Shortcuts** (when focused):
- `Space`: Toggle selection
- `Enter`: Open/edit
- `E`: Quick edit
- `D`: Duplicate

### Context Menus

**Right-click Actions**:
- Edit
- Duplicate
- Change status
- Move to position
- Delete

## 6. Bulk Operations

### Selection Patterns

**Multi-select Methods**:
- Individual checkboxes
- Shift+click for range selection
- Ctrl/Cmd+click for individual selection
- "Select All" with filters applied

**Visual Feedback**:
- Selected item highlighting
- Selection counter
- Bulk action toolbar

### Bulk Action Confirmation

**Pattern**: Progressive confirmation for destructive actions
```javascript
// Confirmation modal with details
showBulkActionModal(action) {
  const modal = {
    title: `${action} ${this.selectedCount} subpages?`,
    items: this.selectedSubpages.map(s => s.title),
    action: action,
    destructive: ['delete', 'archive'].includes(action)
  }
  this.showModal(modal)
}
```

## 7. Performance Optimization Patterns

### Caching Strategies

**Client-side Caching**:
- Cache search results
- Store view preferences
- Remember collapsed states

**Server-side Caching**:
- Redis cache for frequent queries
- Cache invalidation on updates
- Paginated cache keys

### Optimistic Updates

**Pattern**: Update UI immediately, sync with server
```javascript
// Optimistic status toggle
async toggleStatus(subpageId) {
  // Update UI immediately
  const subpage = this.subpages.find(s => s.id === subpageId)
  subpage.is_active = !subpage.is_active
  
  try {
    // Sync with server
    await this.syncStatusWithServer(subpageId)
  } catch (error) {
    // Revert on error
    subpage.is_active = !subpage.is_active
    this.showError('Failed to update status')
  }
}
```

### Database Optimization

**Query Patterns**:
- Eager loading relationships
- Index on search fields
- Pagination with cursor-based approach for large datasets

```sql
-- Optimized query with proper indexes
SELECT s.*, COUNT(c.id) as content_count 
FROM subpages s 
LEFT JOIN contents c ON s.id = c.subpage_id 
WHERE s.module_id = ? 
  AND s.title LIKE ? 
GROUP BY s.id 
ORDER BY s.order_index 
LIMIT 20 OFFSET ?
```

## 8. Accessibility Patterns

### Keyboard Navigation

**Tab Order**:
1. Search input
2. Filter controls
3. View toggles
4. Subpage items (in order)
5. Action buttons

**ARIA Labels**:
```html
<div role="grid" aria-label="Subpages list">
  <div role="row" aria-selected="false">
    <div role="gridcell" aria-describedby="subpage-1-desc">
      <h3 id="subpage-1-title">Subpage Title</h3>
      <p id="subpage-1-desc">Subpage description</p>
    </div>
  </div>
</div>
```

### Screen Reader Support

**Announcements**:
- Search result counts
- Filter changes
- Status updates
- Loading states

## 9. Error Handling & Loading States

### Loading State Patterns

**Skeleton Loading**:
```css
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

**Progressive Loading**:
1. Show skeleton for structure
2. Load critical data first
3. Load additional details
4. Load images/media last

### Error Recovery

**Retry Mechanisms**:
- Automatic retry for network errors
- Manual retry button for failures
- Offline mode detection

**Graceful Degradation**:
- Show cached data when offline
- Disable features that require server
- Clear error messaging

## 10. Mobile-Specific Patterns

### Touch Interactions

**Swipe Gestures**:
- Swipe right: Mark as active
- Swipe left: Delete/archive
- Long press: Multi-select mode

**Pull-to-Refresh**:
```javascript
// Pull to refresh implementation
handleTouchStart(e) {
  this.touchStartY = e.touches[0].clientY
}

handleTouchMove(e) {
  const touchY = e.touches[0].clientY
  const pullDistance = touchY - this.touchStartY
  
  if (pullDistance > 100 && window.scrollY === 0) {
    this.showRefreshIndicator = true
  }
}
```

### Mobile Navigation

**Bottom Sheet Actions**:
- Slide up panel for actions
- Context-sensitive options
- Easy thumb reach

**Compact Mode**:
- Reduced padding
- Smaller fonts
- Essential information only

## Implementation Checklist

### Phase 1: Core Features
- [ ] Infinite scroll pagination
- [ ] Real-time search with debouncing
- [ ] Basic filtering (status, date)
- [ ] List/Grid view toggle
- [ ] Collapsible content

### Phase 2: Advanced Features
- [ ] Bulk operations
- [ ] Quick create modal
- [ ] Keyboard shortcuts
- [ ] Advanced search operators
- [ ] Kanban view

### Phase 3: Performance & Polish
- [ ] Virtual scrolling for 1000+ items
- [ ] Optimistic updates
- [ ] Offline support
- [ ] Advanced caching
- [ ] Mobile gestures

### Phase 4: Analytics & Optimization
- [ ] Usage analytics
- [ ] Performance monitoring
- [ ] A/B testing for UX patterns
- [ ] User feedback collection

## Metrics to Track

**Performance Metrics**:
- Initial load time
- Search response time
- Scroll performance (FPS)
- Memory usage

**User Experience Metrics**:
- Time to find specific subpage
- Number of actions per session
- Feature adoption rates
- Error rates

**Business Metrics**:
- Subpage creation rate
- Content completion rate
- User engagement time
- Support ticket reduction