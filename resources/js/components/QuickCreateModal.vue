<template>
  <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-plus-circle text-primary me-2"></i>
            Quick Create Subpage
          </h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        
        <form @submit.prevent="createSubpage">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-8">
                <!-- Basic Information -->
                <div class="mb-3">
                  <label for="title" class="form-label">
                    Title <span class="text-danger">*</span>
                  </label>
                  <input 
                    type="text" 
                    class="form-control"
                    :class="{ 'is-invalid': errors.title }"
                    id="title"
                    v-model="form.title"
                    placeholder="Enter subpage title..."
                    maxlength="255"
                    required
                    ref="titleInput">
                  <div v-if="errors.title" class="invalid-feedback">
                    {{ errors.title }}
                  </div>
                  <div class="form-text">
                    {{ form.title.length }}/255 characters
                  </div>
                </div>

                <div class="mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea 
                    class="form-control"
                    :class="{ 'is-invalid': errors.description }"
                    id="description"
                    v-model="form.description"
                    placeholder="Brief description of the subpage content..."
                    rows="3"
                    maxlength="500"></textarea>
                  <div v-if="errors.description" class="invalid-feedback">
                    {{ errors.description }}
                  </div>
                  <div class="form-text">
                    {{ form.description.length }}/500 characters
                  </div>
                </div>

                <!-- Advanced Options (Collapsible) -->
                <div class="card">
                  <div class="card-header py-2">
                    <button 
                      type="button" 
                      class="btn btn-link btn-sm p-0 text-decoration-none"
                      @click="showAdvanced = !showAdvanced">
                      <i class="fas" :class="showAdvanced ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                      Advanced Options
                    </button>
                  </div>
                  
                  <div v-show="showAdvanced" class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Status</label>
                          <div class="form-check form-switch">
                            <input 
                              class="form-check-input" 
                              type="checkbox" 
                              id="isActive"
                              v-model="form.is_active">
                            <label class="form-check-label" for="isActive">
                              Active (visible to students)
                            </label>
                          </div>
                        </div>
                      </div>
                      
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="position" class="form-label">Position</label>
                          <select class="form-select" id="position" v-model="form.position">
                            <option value="end">At the end</option>
                            <option value="beginning">At the beginning</option>
                            <option value="after" v-if="existingSubpages.length > 0">After existing subpage</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div v-if="form.position === 'after'" class="mb-3">
                      <label for="afterSubpage" class="form-label">Insert After</label>
                      <select class="form-select" id="afterSubpage" v-model="form.after_subpage_id">
                        <option value="">Select subpage...</option>
                        <option 
                          v-for="subpage in existingSubpages" 
                          :key="subpage.id" 
                          :value="subpage.id">
                          {{ subpage.title }}
                        </option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Initial Content</label>
                      <div class="form-check">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          id="addIntroContent"
                          v-model="form.add_intro_content">
                        <label class="form-check-label" for="addIntroContent">
                          Add introduction content block
                        </label>
                      </div>
                      <div class="form-check">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          id="addExercise"
                          v-model="form.add_exercise">
                        <label class="form-check-label" for="addExercise">
                          Add practice exercise
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <!-- Preview -->
                <div class="card bg-light">
                  <div class="card-header py-2">
                    <h6 class="card-title mb-0">
                      <i class="fas fa-eye me-1"></i>
                      Preview
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="subpage-preview">
                      <div class="d-flex align-items-center mb-2">
                        <span class="badge me-2" :class="form.is_active ? 'bg-success' : 'bg-secondary'">
                          {{ form.is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <small class="text-muted">Order: {{ previewOrder }}</small>
                      </div>
                      
                      <h6 class="mb-2">{{ form.title || 'Untitled Subpage' }}</h6>
                      
                      <p class="text-muted small mb-3">
                        {{ form.description || 'No description provided.' }}
                      </p>

                      <div class="preview-content">
                        <div v-if="form.add_intro_content" class="content-item mb-2">
                          <i class="fas fa-file-text text-primary me-1"></i>
                          <small>Introduction Content</small>
                        </div>
                        <div v-if="form.add_exercise" class="content-item mb-2">
                          <i class="fas fa-dumbbell text-success me-1"></i>
                          <small>Practice Exercise</small>
                        </div>
                        <div v-if="!form.add_intro_content && !form.add_exercise" class="text-muted small">
                          <i class="fas fa-info-circle me-1"></i>
                          Empty subpage - content can be added later
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                  <div class="card-header py-2">
                    <h6 class="card-title mb-0">
                      <i class="fas fa-bolt me-1"></i>
                      Quick Actions
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="d-grid gap-2">
                      <button 
                        type="button" 
                        class="btn btn-outline-primary btn-sm"
                        @click="useTemplate('lesson')">
                        <i class="fas fa-book-open me-1"></i>
                        Lesson Template
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-outline-success btn-sm"
                        @click="useTemplate('exercise')">
                        <i class="fas fa-dumbbell me-1"></i>
                        Exercise Template
                      </button>
                      <button 
                        type="button" 
                        class="btn btn-outline-info btn-sm"
                        @click="useTemplate('resource')">
                        <i class="fas fa-folder-open me-1"></i>
                        Resource Template
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="$emit('close')">
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="!form.title.trim() || creating">
              <span v-if="creating" class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Creating...</span>
              </span>
              <i v-else class="fas fa-plus me-2"></i>
              {{ creating ? 'Creating...' : 'Create Subpage' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuickCreateModal',
  
  props: {
    module: {
      type: Object,
      required: true
    },
    existingSubpages: {
      type: Array,
      default: () => []
    }
  },

  data() {
    return {
      form: {
        title: '',
        description: '',
        is_active: true,
        position: 'end',
        after_subpage_id: '',
        add_intro_content: false,
        add_exercise: false
      },
      errors: {},
      creating: false,
      showAdvanced: false
    }
  },

  computed: {
    previewOrder() {
      if (this.form.position === 'beginning') {
        return 1
      } else if (this.form.position === 'after' && this.form.after_subpage_id) {
        const afterSubpage = this.existingSubpages.find(s => s.id == this.form.after_subpage_id)
        return afterSubpage ? afterSubpage.order_index + 1 : this.existingSubpages.length + 1
      } else {
        return this.existingSubpages.length + 1
      }
    }
  },

  mounted() {
    // Focus on title input
    this.$nextTick(() => {
      if (this.$refs.titleInput) {
        this.$refs.titleInput.focus()
      }
    })

    // Handle escape key
    document.addEventListener('keydown', this.handleEscape)
  },

  beforeUnmount() {
    document.removeEventListener('keydown', this.handleEscape)
  },

  methods: {
    handleEscape(event) {
      if (event.key === 'Escape') {
        this.$emit('close')
      }
    },

    useTemplate(templateType) {
      const templates = {
        lesson: {
          title: 'Lesson: ',
          description: 'Interactive lesson with content and exercises',
          add_intro_content: true,
          add_exercise: true
        },
        exercise: {
          title: 'Exercise: ',
          description: 'Practice exercises and activities',
          add_intro_content: false,
          add_exercise: true
        },
        resource: {
          title: 'Resources: ',
          description: 'Additional resources and materials',
          add_intro_content: true,
          add_exercise: false
        }
      }

      const template = templates[templateType]
      if (template) {
        Object.assign(this.form, template)
        
        // Focus on title to continue typing
        this.$nextTick(() => {
          if (this.$refs.titleInput) {
            this.$refs.titleInput.focus()
            this.$refs.titleInput.setSelectionRange(this.form.title.length, this.form.title.length)
          }
        })
      }
    },

    async createSubpage() {
      if (this.creating) return

      this.errors = {}
      this.creating = true

      try {
        const response = await fetch(`/teacher/courses/${this.module.course_id}/modules/${this.module.id}/subpages/quick-create`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(this.form)
        })

        const data = await response.json()

        if (data.success) {
          this.$emit('created', data.subpage)
        } else {
          if (data.errors) {
            this.errors = data.errors
          } else {
            this.$emit('error', data.message || 'Failed to create subpage')
          }
        }
      } catch (error) {
        console.error('Create subpage error:', error)
        this.$emit('error', 'Failed to create subpage')
      } finally {
        this.creating = false
      }
    }
  }
}
</script>

<style scoped>
.modal {
  animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.subpage-preview {
  border: 1px dashed #dee2e6;
  border-radius: 0.375rem;
  padding: 1rem;
  background: white;
}

.content-item {
  padding: 0.25rem 0.5rem;
  background: #f8f9fa;
  border-radius: 0.25rem;
  border-left: 3px solid #007bff;
}

.card-header {
  background: transparent;
  border-bottom: 1px solid rgba(0,0,0,0.125);
}

.btn-link {
  color: #495057;
  text-decoration: none;
}

.btn-link:hover {
  color: #007bff;
}

.form-text {
  font-size: 0.75rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}
</style>