<template>
  <div class="subpage-manager">
    <!-- Header with Search and Controls -->
    <div class="subpage-header">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h3 mb-1">{{ module.title }} Subpages</h1>
          <div class="d-flex align-items-center gap-3">
            <span class="badge badge-info">{{ module.type_display }}</span>
            <span class="text-muted">
              <i class="fas fa-file-alt"></i> {{ stats.total }} total
            </span>
            <span class="text-muted">
              <i class="fas fa-eye"></i> {{ stats.active }} active
            </span>
          </div>
        </div>
        
        <div class="d-flex gap-2">
          <!-- View Toggle -->
          <div class="btn-group view-toggle" role="group">
            <button 
              type="button" 
              class="btn btn-sm"
              :class="{ 'btn-primary': currentView === 'list', 'btn-outline-primary': currentView !== 'list' }"
              @click="setView('list')"
              title="List View">
              <i class="fas fa-list"></i>
            </button>
            <button 
              type="button" 
              class="btn btn-sm"
              :class="{ 'btn-primary': currentView === 'grid', 'btn-outline-primary': currentView !== 'grid' }"
              @click="setView('grid')"
              title="Grid View">
              <i class="fas fa-th"></i>
            </button>
            <button 
              type="button" 
              class="btn btn-sm"
              :class="{ 'btn-primary': currentView === 'kanban', 'btn-outline-primary': currentView !== 'kanban' }"
              @click="setView('kanban')"
              title="Kanban View">
              <i class="fas fa-columns"></i>
            </button>
          </div>

          <!-- Actions Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <i class="fas fa-cog"></i> Actions
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" @click="bulkAction('activate')">
                <i class="fas fa-eye"></i> Activate Selected
              </a></li>
              <li><a class="dropdown-item" href="#" @click="bulkAction('deactivate')">
                <i class="fas fa-eye-slash"></i> Deactivate Selected
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" @click="bulkAction('delete')">
                <i class="fas fa-trash"></i> Delete Selected
              </a></li>
              <li><a class="dropdown-item" href="#" @click="exportSubpages">
                <i class="fas fa-download"></i> Export List
              </a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="search-filters-container">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="search-input-container">
              <input 
                type="text" 
                class="form-control" 
                placeholder="Search subpages..." 
                v-model="searchQuery"
                @input="debouncedSearch">
              <i class="fas fa-search search-icon"></i>
              <button 
                v-if="searchQuery" 
                class="btn btn-sm btn-link clear-search" 
                @click="clearSearch">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          
          <div class="col-md-2">
            <select class="form-select" v-model="statusFilter" @change="applyFilters">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="deleted">Deleted</option>
            </select>
          </div>
          
          <div class="col-md-2">
            <select class="form-select" v-model="sortBy" @change="applyFilters">
              <option value="order">Order</option>
              <option value="title">Title</option>
              <option value="updated">Last Updated</option>
              <option value="content_count">Content Count</option>
            </select>
          </div>
          
          <div class="col-md-2">
            <div class="form-check form-switch">
              <input 
                class="form-check-input" 
                type="checkbox" 
                id="collapseAll"
                v-model="collapseAll"
                @change="toggleAllCollapse">
              <label class="form-check-label" for="collapseAll">
                Collapse All
              </label>
            </div>
          </div>
          
          <div class="col-md-2">
            <button class="btn btn-outline-primary w-100" @click="showQuickCreate">
              <i class="fas fa-plus"></i> Quick Add
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2 text-muted">Loading subpages...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="subpages.length === 0 && !loading" class="empty-state text-center py-5">
      <i class="fas fa-search fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">No subpages found</h5>
      <p class="text-muted">Try adjusting your search criteria or create a new subpage.</p>
      <button class="btn btn-primary" @click="showQuickCreate">
        <i class="fas fa-plus"></i> Create First Subpage
      </button>
    </div>

    <!-- Subpages Display -->
    <div v-else class="subpages-container">
      <!-- List View -->
      <div v-if="currentView === 'list'" class="subpages-list">
        <div class="list-group">
          <SubpageListItem
            v-for="subpage in subpages"
            :key="subpage.id"
            :subpage="subpage"
            :selected="selectedSubpages.has(subpage.id)"
            :collapsed="collapsedItems.has(subpage.id)"
            @toggle-select="toggleSelection"
            @toggle-collapse="toggleCollapse"
            @toggle-status="toggleStatus"
            @delete="deleteSubpage"
            @duplicate="duplicateSubpage"
          />
        </div>
      </div>

      <!-- Grid View -->
      <div v-else-if="currentView === 'grid'" class="subpages-grid">
        <SubpageGridItem
          v-for="subpage in subpages"
          :key="subpage.id"
          :subpage="subpage"
          :selected="selectedSubpages.has(subpage.id)"
          @toggle-select="toggleSelection"
          @toggle-status="toggleStatus"
          @delete="deleteSubpage"
          @duplicate="duplicateSubpage"
        />
      </div>

      <!-- Kanban View -->
      <div v-else-if="currentView === 'kanban'" class="subpages-kanban">
        <SubpageKanban
          :subpages="subpages"
          @move-subpage="moveSubpage"
          @toggle-status="toggleStatus"
          @delete="deleteSubpage"
        />
      </div>
    </div>

    <!-- Infinite Scroll Trigger -->
    <div 
      v-if="hasMorePages && !loading" 
      ref="loadTrigger" 
      class="load-more-trigger text-center py-3">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Loading more...</span>
      </div>
      <span class="ms-2 text-muted">Loading more subpages...</span>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-actions">
      <div class="fab-container">
        <button 
          class="fab fab-main" 
          :class="{ 'fab-open': fabOpen }"
          @click="toggleFab">
          <i class="fas" :class="fabOpen ? 'fa-times' : 'fa-plus'"></i>
        </button>
        
        <transition-group name="fab-item" tag="div" class="fab-items">
          <button 
            v-if="fabOpen"
            key="quick-create"
            class="fab fab-item" 
            @click="showQuickCreate"
            title="Quick Create">
            <i class="fas fa-plus"></i>
          </button>
          <button 
            v-if="fabOpen"
            key="template"
            class="fab fab-item" 
            @click="showTemplateSelector"
            title="Create from Template">
            <i class="fas fa-layer-group"></i>
          </button>
          <button 
            v-if="fabOpen"
            key="import"
            class="fab fab-item" 
            @click="showImportDialog"
            title="Import Subpages">
            <i class="fas fa-upload"></i>
          </button>
        </transition-group>
      </div>
    </div>

    <!-- Quick Create Modal -->
    <QuickCreateModal
      v-if="showQuickCreateModal"
      :module="module"
      @close="showQuickCreateModal = false"
      @created="onSubpageCreated"
    />

    <!-- Template Selector Modal -->
    <TemplateSelector
      v-if="showTemplateSelectorModal"
      :module="module"
      @close="showTemplateSelectorModal = false"
      @selected="createFromTemplate"
    />

    <!-- Bulk Action Confirmation Modal -->
    <BulkActionModal
      v-if="showBulkActionModal"
      :action="bulkActionType"
      :selected-count="selectedSubpages.size"
      :selected-items="selectedSubpagesList"
      @confirm="executeBulkAction"
      @cancel="showBulkActionModal = false"
    />
  </div>
</template>

<script>
import { debounce } from 'lodash-es'
import SubpageListItem from './SubpageListItem.vue'
import SubpageGridItem from './SubpageGridItem.vue'
import SubpageKanban from './SubpageKanban.vue'
import QuickCreateModal from './QuickCreateModal.vue'
import TemplateSelector from './TemplateSelector.vue'
import BulkActionModal from './BulkActionModal.vue'

export default {
  name: 'SubpageManager',
  
  components: {
    SubpageListItem,
    SubpageGridItem,
    SubpageKanban,
    QuickCreateModal,
    TemplateSelector,
    BulkActionModal
  },

  props: {
    course: {
      type: Object,
      required: true
    },
    module: {
      type: Object,
      required: true
    },
    initialStats: {
      type: Object,
      default: () => ({})
    }
  },

  data() {
    return {
      // Data state
      subpages: [],
      stats: { ...this.initialStats },
      
      // UI state
      currentView: 'list',
      loading: false,
      
      // Pagination
      currentPage: 1,
      hasMorePages: true,
      
      // Search and filters
      searchQuery: '',
      statusFilter: '',
      sortBy: 'order',
      
      // Selection and interaction
      selectedSubpages: new Set(),
      collapsedItems: new Set(),
      collapseAll: false,
      
      // Floating Action Button
      fabOpen: false,
      
      // Modals
      showQuickCreateModal: false,
      showTemplateSelectorModal: false,
      showBulkActionModal: false,
      bulkActionType: null,
      
      // Intersection Observer
      observer: null
    }
  },

  computed: {
    selectedSubpagesList() {
      return this.subpages.filter(subpage => 
        this.selectedSubpages.has(subpage.id)
      )
    },

    debouncedSearch() {
      return debounce(this.applyFilters, 300)
    }
  },

  mounted() {
    this.loadSubpages()
    this.setupIntersectionObserver()
    
    // Load view preference from localStorage
    const savedView = localStorage.getItem('subpage_view_preference')
    if (savedView && ['list', 'grid', 'kanban'].includes(savedView)) {
      this.currentView = savedView
    }
  },

  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect()
    }
  },

  methods: {
    async loadSubpages(reset = false) {
      if (this.loading) return
      
      this.loading = true

      try {
        const params = new URLSearchParams({
          page: reset ? 1 : this.currentPage,
          search: this.searchQuery,
          status: this.statusFilter,
          sort: this.sortBy,
          per_page: 20
        })

        const response = await fetch(`/teacher/courses/${this.course.id}/modules/${this.module.id}/subpages?${params}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        const data = await response.json()

        if (reset || this.currentPage === 1) {
          this.subpages = data.subpages
        } else {
          this.subpages.push(...data.subpages)
        }

        this.hasMorePages = data.has_more_pages
        this.stats.total = data.total

        if (!reset) {
          this.currentPage++
        }

      } catch (error) {
        console.error('Error loading subpages:', error)
        this.$emit('error', 'Failed to load subpages')
      } finally {
        this.loading = false
      }
    },

    setupIntersectionObserver() {
      this.observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && this.hasMorePages && !this.loading) {
            this.loadSubpages()
          }
        })
      }, { threshold: 0.1 })

      this.$nextTick(() => {
        if (this.$refs.loadTrigger) {
          this.observer.observe(this.$refs.loadTrigger)
        }
      })
    },

    applyFilters() {
      this.currentPage = 1
      this.hasMorePages = true
      this.selectedSubpages.clear()
      this.loadSubpages(true)
    },

    clearSearch() {
      this.searchQuery = ''
      this.applyFilters()
    },

    setView(view) {
      this.currentView = view
      localStorage.setItem('subpage_view_preference', view)
    },

    toggleSelection(subpageId) {
      if (this.selectedSubpages.has(subpageId)) {
        this.selectedSubpages.delete(subpageId)
      } else {
        this.selectedSubpages.add(subpageId)
      }
    },

    toggleCollapse(subpageId) {
      if (this.collapsedItems.has(subpageId)) {
        this.collapsedItems.delete(subpageId)
      } else {
        this.collapsedItems.add(subpageId)
      }
    },

    toggleAllCollapse(collapsed) {
      if (collapsed) {
        this.subpages.forEach(subpage => {
          this.collapsedItems.add(subpage.id)
        })
      } else {
        this.collapsedItems.clear()
      }
    },

    toggleFab() {
      this.fabOpen = !this.fabOpen
    },

    showQuickCreate() {
      this.showQuickCreateModal = true
      this.fabOpen = false
    },

    showTemplateSelector() {
      this.showTemplateSelectorModal = true
      this.fabOpen = false
    },

    async toggleStatus(subpageId) {
      try {
        const response = await fetch(`/teacher/courses/${this.course.id}/modules/${this.module.id}/subpages/${subpageId}/toggle-active`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })

        const data = await response.json()
        
        if (data.success) {
          // Update local state
          const subpage = this.subpages.find(s => s.id === subpageId)
          if (subpage) {
            subpage.is_active = data.is_active
          }
          this.$emit('success', data.message)
        } else {
          this.$emit('error', 'Failed to update subpage status')
        }
      } catch (error) {
        console.error('Toggle status error:', error)
        this.$emit('error', 'Failed to update subpage status')
      }
    },

    async deleteSubpage(subpageId) {
      if (!confirm('Are you sure you want to delete this subpage? This action can be undone.')) {
        return
      }

      try {
        const response = await fetch(`/teacher/courses/${this.course.id}/modules/${this.module.id}/subpages/${subpageId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })

        if (response.ok) {
          // Remove from local state
          this.subpages = this.subpages.filter(s => s.id !== subpageId)
          this.selectedSubpages.delete(subpageId)
          this.$emit('success', 'Subpage deleted successfully')
        } else {
          this.$emit('error', 'Failed to delete subpage')
        }
      } catch (error) {
        console.error('Delete error:', error)
        this.$emit('error', 'Failed to delete subpage')
      }
    },

    async duplicateSubpage(subpageId) {
      try {
        const response = await fetch(`/teacher/courses/${this.course.id}/modules/${this.module.id}/subpages/${subpageId}/duplicate`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })

        const data = await response.json()
        
        if (data.success) {
          // Refresh the list to show the new subpage
          this.applyFilters()
          this.$emit('success', data.message)
        } else {
          this.$emit('error', 'Failed to duplicate subpage')
        }
      } catch (error) {
        console.error('Duplicate error:', error)
        this.$emit('error', 'Failed to duplicate subpage')
      }
    },

    bulkAction(action) {
      if (this.selectedSubpages.size === 0) {
        this.$emit('error', 'Please select at least one subpage')
        return
      }

      this.bulkActionType = action
      this.showBulkActionModal = true
    },

    async executeBulkAction() {
      try {
        const response = await fetch(`/teacher/courses/${this.course.id}/modules/${this.module.id}/subpages/bulk-action`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            action: this.bulkActionType,
            subpage_ids: Array.from(this.selectedSubpages)
          })
        })

        const data = await response.json()
        
        if (data.success) {
          this.showBulkActionModal = false
          this.selectedSubpages.clear()
          this.applyFilters()
          this.$emit('success', data.message)
          
          if (data.errors && data.errors.length > 0) {
            data.errors.forEach(error => {
              this.$emit('warning', error)
            })
          }
        } else {
          this.$emit('error', data.message || 'Bulk action failed')
        }
      } catch (error) {
        console.error('Bulk action error:', error)
        this.$emit('error', 'Failed to execute bulk action')
      }
    },

    exportSubpages() {
      const params = new URLSearchParams({
        search: this.searchQuery,
        status: this.statusFilter,
        sort: this.sortBy,
        export: 'csv'
      })

      window.location.href = `/teacher/courses/${this.course.id}/modules/${this.module.id}/subpages?${params}`
    },

    onSubpageCreated(subpage) {
      this.showQuickCreateModal = false
      this.applyFilters() // Refresh the list
      this.$emit('success', 'Subpage created successfully')
    },

    createFromTemplate(template) {
      this.showTemplateSelectorModal = false
      // Implementation for creating from template
      // This would typically open a more detailed creation form
      this.$emit('info', `Creating subpage from ${template.name} template...`)
    },

    moveSubpage(subpageId, newStatus) {
      // Implementation for kanban view drag and drop
      this.toggleStatus(subpageId)
    }
  }
}
</script>

<style scoped>
.subpage-manager {
  position: relative;
}

.subpage-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: white;
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 1rem;
  margin-bottom: 1rem;
}

.search-input-container {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  pointer-events: none;
}

.search-input-container input {
  padding-left: 2.5rem;
  padding-right: 2.5rem;
}

.clear-search {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  padding: 0.25rem;
  color: #6c757d;
}

.view-toggle .btn {
  border-radius: 0.375rem;
}

.subpages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.load-more-trigger {
  min-height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.floating-actions {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
}

.fab-container {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.fab {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  margin-bottom: 0.5rem;
}

.fab-main {
  background: #007bff;
  color: white;
  font-size: 1.25rem;
}

.fab-main:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.fab-main.fab-open {
  transform: rotate(45deg);
}

.fab-item {
  background: white;
  color: #007bff;
  border: 1px solid #007bff;
  font-size: 1rem;
  width: 48px;
  height: 48px;
}

.fab-item:hover {
  background: #007bff;
  color: white;
}

.fab-items {
  position: absolute;
  bottom: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.fab-item-enter-active,
.fab-item-leave-active {
  transition: all 0.3s ease;
}

.fab-item-enter-from,
.fab-item-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.8);
}

.empty-state {
  min-height: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

@media (max-width: 768px) {
  .subpages-grid {
    grid-template-columns: 1fr;
  }
  
  .floating-actions {
    bottom: 1rem;
    right: 1rem;
  }
  
  .fab {
    width: 48px;
    height: 48px;
  }
  
  .fab-main {
    font-size: 1rem;
  }
}
</style>