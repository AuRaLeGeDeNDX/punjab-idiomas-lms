import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';

function CourseHierarchy({ courseId, csrfToken, userRole }) {
    const [modules, setModules] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showModuleModal, setShowModuleModal] = useState(false);
    const [showSubpageModal, setShowSubpageModal] = useState(false);
    const [selectedModule, setSelectedModule] = useState(null);
    const [editingModule, setEditingModule] = useState(null);
    const [editingSubpage, setEditingSubpage] = useState(null);
    const [moduleForm, setModuleForm] = useState({ title: '', description: '' });
    const [subpageForm, setSubpageForm] = useState({ title: '', description: '', is_active: true });

    useEffect(() => {
        fetchModules();
    }, []);

    const fetchModules = async () => {
        console.log('[CourseHierarchy] Fetching modules for course:', courseId);
        try {
            const response = await fetch(`/api/courses/${courseId}/modules?t=${Date.now()}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            const data = await response.json();
            console.log('[CourseHierarchy] Fetch modules response:', data);
            console.log('[CourseHierarchy] Modules array:', data.data?.modules);
            setModules(data.data?.modules || []);
        } catch (error) {
            console.error('[CourseHierarchy] Error fetching modules:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleCreateModule = async (e) => {
        e.preventDefault();
        console.log('[CourseHierarchy] Creating module:', moduleForm);
        
        try {
            const response = await fetch(`/api/courses/${courseId}/modules`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(moduleForm)
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Create module response:', data);
            
            if (response.ok && data.success) {
                setShowModuleModal(false);
                setModuleForm({ title: '', description: '' });
                fetchModules();
            } else {
                console.error('[CourseHierarchy] Failed to create module:', data);
                alert('Failed to create module: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error creating module:', error);
            alert('Error creating module: ' + error.message);
        }
    };

    const handleUpdateModule = async (e) => {
        e.preventDefault();
        console.log('[CourseHierarchy] Updating module:', editingModule.id, moduleForm);
        
        try {
            const response = await fetch(`/api/courses/${courseId}/modules/${editingModule.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(moduleForm)
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Update module response:', data);
            
            if (response.ok && data.success) {
                setShowModuleModal(false);
                setEditingModule(null);
                setModuleForm({ title: '', description: '' });
                fetchModules();
            } else {
                console.error('[CourseHierarchy] Failed to update module:', data);
                alert('Failed to update module: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error updating module:', error);
            alert('Error updating module: ' + error.message);
        }
    };

    const openEditModuleModal = (module) => {
        setEditingModule(module);
        setModuleForm({ 
            title: module.title, 
            description: module.description || '' 
        });
        setShowModuleModal(true);
    };

    const handleCreateSubpage = async (e) => {
        e.preventDefault();
        console.log('[CourseHierarchy] Creating subpage:', subpageForm, 'for module:', selectedModule.id);
        
        try {
            const response = await fetch(`/api/courses/${courseId}/modules/${selectedModule.id}/subpages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(subpageForm)
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Create subpage response:', data);
            
            if (response.ok && data.success) {
                setShowSubpageModal(false);
                setSubpageForm({ title: '', description: '', is_active: true });
                setSelectedModule(null);
                fetchModules();
            } else {
                console.error('[CourseHierarchy] Failed to create subpage:', data);
                alert('Failed to create subpage: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error creating subpage:', error);
            alert('Error creating subpage: ' + error.message);
        }
    };

    const handleUpdateSubpage = async (e) => {
        e.preventDefault();
        console.log('[CourseHierarchy] Updating subpage:', editingSubpage.id, subpageForm);
        
        try {
            const response = await fetch(`/api/courses/${courseId}/modules/${selectedModule.id}/subpages/${editingSubpage.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(subpageForm)
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Update subpage response:', data);
            
            if (response.ok && data.success) {
                setShowSubpageModal(false);
                setEditingSubpage(null);
                setSubpageForm({ title: '', description: '', is_active: true });
                setSelectedModule(null);
                fetchModules();
            } else {
                console.error('[CourseHierarchy] Failed to update subpage:', data);
                alert('Failed to update subpage: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error updating subpage:', error);
            alert('Error updating subpage: ' + error.message);
        }
    };

    const openEditSubpageModal = (module, subpage) => {
        setSelectedModule(module);
        setEditingSubpage(subpage);
        setSubpageForm({ 
            title: subpage.title, 
            description: subpage.description || '',
            is_active: subpage.is_active
        });
        setShowSubpageModal(true);
    };

    const handleDeleteModule = async (moduleId, moduleTitle) => {
        if (!confirm(`Are you sure you want to delete the module "${moduleTitle}"? This action cannot be undone.`)) {
            return;
        }
        
        console.log('[CourseHierarchy] Deleting module:', moduleId);
        
        try {
            const response = await fetch(`/api/courses/${courseId}/modules/${moduleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Delete module response:', data);
            
            if (response.ok && data.success) {
                fetchModules();
            } else {
                alert('Failed to delete module: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error deleting module:', error);
            alert('Error deleting module: ' + error.message);
        }
    };

    const handleDeleteSubpage = async (moduleId, subpageId, subpageTitle) => {
        if (!confirm(`Are you sure you want to delete the subpage "${subpageTitle}"? This action cannot be undone.`)) {
            return;
        }
        
        console.log('[CourseHierarchy] Deleting subpage:', subpageId);
        
        try {
            const response = await fetch(`/api/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Delete subpage response:', data);
            
            if (response.ok && data.success) {
                fetchModules();
            } else {
                alert('Failed to delete subpage: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error deleting subpage:', error);
            alert('Error deleting subpage: ' + error.message);
        }
    };

    const handleTogglePublish = async (moduleId, isPublished) => {
        console.log('[CourseHierarchy] Toggling publish for module:', moduleId, 'Current status:', isPublished);
        
        const action = isPublished ? 'unpublish' : 'publish';
        
        try {
            const response = await fetch(`/${userRole}/courses/${courseId}/modules/${moduleId}/${action}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            console.log('[CourseHierarchy] Toggle publish response:', data);
            
            if (response.ok && data.success) {
                fetchModules();
            } else {
                alert('Failed to ' + action + ' module: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('[CourseHierarchy] Error toggling publish:', error);
            alert('Error toggling publish: ' + error.message);
        }
    };

    const openSubpageModal = (module) => {
        setSelectedModule(module);
        setEditingSubpage(null);
        setSubpageForm({ title: '', description: '', is_active: true });
        setShowSubpageModal(true);
    };

    if (loading) {
        return React.createElement('div', { className: 'text-center py-4' },
            React.createElement('div', { className: 'spinner-border', role: 'status' },
                React.createElement('span', { className: 'visually-hidden' }, 'Loading...')
            )
        );
    }

    return React.createElement('div', { className: 'creative-card mt-4' },
        // Card Header
        React.createElement('div', { className: 'creative-card-header d-flex justify-content-between align-items-center' },
            React.createElement('h3', { className: 'mb-0' }, 
                React.createElement('i', { className: 'fas fa-layer-group me-2' }),
                'Course Modules & Subpages'
            ),
            React.createElement('button', {
                type: 'button',
                className: 'creative-btn creative-btn-primary btn-sm',
                onClick: () => setShowModuleModal(true)
            },
                React.createElement('i', { className: 'fas fa-plus me-1' }),
                'Add Module'
            )
        ),
        
        // Card Body
        React.createElement('div', { className: 'creative-card-body' },
            modules.length === 0 
                ? React.createElement('div', { className: 'alert alert-info mb-0' },
                    React.createElement('i', { className: 'fas fa-info-circle me-2' }),
                    'No modules yet. Click "Add Module" to create your first module.'
                )
                : React.createElement('div', { className: 'accordion', id: 'modulesAccordion' },
                    modules.map((module, index) =>
                        React.createElement('div', { key: module.id, className: 'accordion-item' },
                            // Accordion Header
                            React.createElement('h2', { className: 'accordion-header', id: `heading${module.id}` },
                                React.createElement('div', { className: 'd-flex align-items-center w-100' },
                                    React.createElement('button', {
                                        className: `accordion-button ${index === 0 ? '' : 'collapsed'} flex-grow-1`,
                                        type: 'button',
                                        'data-bs-toggle': 'collapse',
                                        'data-bs-target': `#collapse${module.id}`,
                                        'aria-expanded': index === 0 ? 'true' : 'false'
                                    },
                                        React.createElement('strong', null, module.title),
                                        module.description && React.createElement('small', { className: 'text-muted ms-2' },
                                            `- ${module.description.substring(0, 50)}${module.description.length > 50 ? '...' : ''}`
                                        )
                                    ),
                                    React.createElement('button', {
                                        type: 'button',
                                        className: 'creative-btn creative-btn-outline btn-sm me-2',
                                        onClick: (e) => {
                                            e.stopPropagation();
                                            openEditModuleModal(module);
                                        },
                                        title: 'Edit Module'
                                    },
                                        React.createElement('i', { className: 'fas fa-edit' })
                                    ),
                                    React.createElement('button', {
                                        type: 'button',
                                        className: `creative-btn ${module.is_published ? 'creative-btn-success' : 'creative-btn-outline'} btn-sm me-2`,
                                        onClick: (e) => {
                                            e.stopPropagation();
                                            handleTogglePublish(module.id, module.is_published);
                                        },
                                        title: module.is_published ? 'Unpublish Module' : 'Publish Module'
                                    },
                                        React.createElement('i', { className: `fas ${module.is_published ? 'fa-eye-slash' : 'fa-eye'}` })
                                    ),
                                    React.createElement('button', {
                                        type: 'button',
                                        className: 'creative-btn creative-btn-outline btn-sm me-2',
                                        onClick: (e) => {
                                            e.stopPropagation();
                                            handleDeleteModule(module.id, module.title);
                                        },
                                        title: 'Delete Module'
                                    },
                                        React.createElement('i', { className: 'fas fa-trash' })
                                    )
                                )
                            ),
                            
                            // Accordion Body
                            React.createElement('div', {
                                id: `collapse${module.id}`,
                                className: `accordion-collapse collapse ${index === 0 ? 'show' : ''}`,
                                'data-bs-parent': '#modulesAccordion'
                            },
                                React.createElement('div', { className: 'accordion-body' },
                                    // Subpages Header
                                    React.createElement('div', { className: 'd-flex justify-content-between align-items-center mb-3' },
                                        React.createElement('h6', { className: 'mb-0' }, 'Subpages'),
                                        React.createElement('button', {
                                            type: 'button',
                                            className: 'creative-btn creative-btn-success btn-sm',
                                            onClick: () => openSubpageModal(module)
                                        },
                                            React.createElement('i', { className: 'fas fa-plus me-1' }),
                                            'Add Subpage'
                                        )
                                    ),
                                    
                                    // Subpages List
                                    module.subpages && module.subpages.length > 0
                                        ? React.createElement('div', { className: 'list-group' },
                                            module.subpages.map(subpage =>
                                                React.createElement('a', {
                                                    key: subpage.id,
                                                    href: `/${userRole}/courses/${courseId}/modules/${module.id}/subpages/${subpage.id}`,
                                                    className: 'list-group-item list-group-item-action d-flex justify-content-between align-items-center'
                                                },
                                                    React.createElement('div', null,
                                                        React.createElement('i', { className: 'fas fa-file-alt me-2' }),
                                                        React.createElement('strong', null, subpage.title),
                                                        subpage.description && React.createElement('br'),
                                                        subpage.description && React.createElement('small', { className: 'text-muted ms-4' },
                                                            subpage.description.substring(0, 80) + (subpage.description.length > 80 ? '...' : '')
                                                        )
                                                    ),
                                                    React.createElement('div', { className: 'd-flex align-items-center gap-2' },
                                                        React.createElement('span', {
                                                            className: `creative-badge creative-badge-${subpage.is_active ? 'success' : 'secondary'}`
                                                        }, subpage.is_active ? 'Active' : 'Inactive'),
                                                        React.createElement('a', {
                                                            href: `/${userRole}/courses/${courseId}/modules/${module.id}/subpages/${subpage.id}/content-builder`,
                                                            className: 'creative-btn creative-btn-primary btn-sm',
                                                            onClick: (e) => e.stopPropagation()
                                                        },
                                                            React.createElement('i', { className: 'fas fa-edit' }),
                                                            ' Edit Content'
                                                        ),
                                                        React.createElement('button', {
                                                            type: 'button',
                                                            className: 'creative-btn creative-btn-outline btn-sm',
                                                            onClick: (e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                openEditSubpageModal(module, subpage);
                                                            },
                                                            title: 'Edit Subpage'
                                                        },
                                                            React.createElement('i', { className: 'fas fa-pen' })
                                                        ),
                                                        React.createElement('button', {
                                                            type: 'button',
                                                            className: 'creative-btn creative-btn-outline btn-sm',
                                                            onClick: (e) => {
                                                                e.preventDefault();
                                                                e.stopPropagation();
                                                                handleDeleteSubpage(module.id, subpage.id, subpage.title);
                                                            },
                                                            title: 'Delete Subpage'
                                                        },
                                                            React.createElement('i', { className: 'fas fa-trash' })
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                        : React.createElement('div', { className: 'alert alert-info mb-0' },
                                            React.createElement('i', { className: 'fas fa-info-circle me-2' }),
                                            'No subpages yet. Click "Add Subpage" to create one.'
                                        )
                                )
                            )
                        )
                    )
                )
        ),
        
        // Module Modal
        showModuleModal && React.createElement('div', {
            className: 'modal fade show',
            style: { display: 'block', backgroundColor: 'rgba(0,0,0,0.5)' },
            onClick: () => {
                setShowModuleModal(false);
                setEditingModule(null);
                setModuleForm({ title: '', description: '' });
            }
        },
            React.createElement('div', {
                className: 'modal-dialog',
                onClick: (e) => e.stopPropagation()
            },
                React.createElement('div', { className: 'modal-content' },
                    React.createElement('form', { onSubmit: editingModule ? handleUpdateModule : handleCreateModule },
                        React.createElement('div', { className: 'modal-header' },
                            React.createElement('h5', { className: 'modal-title' }, 
                                editingModule ? 'Edit Module' : 'Add New Module'
                            ),
                            React.createElement('button', {
                                type: 'button',
                                className: 'btn-close',
                                onClick: () => {
                                    setShowModuleModal(false);
                                    setEditingModule(null);
                                    setModuleForm({ title: '', description: '' });
                                }
                            })
                        ),
                        React.createElement('div', { className: 'modal-body' },
                            React.createElement('div', { className: 'mb-3' },
                                React.createElement('label', { className: 'creative-form-label' }, 'Module Title ', React.createElement('span', { className: 'text-danger' }, '*')),
                                React.createElement('input', {
                                    type: 'text',
                                    className: 'creative-form-input',
                                    value: moduleForm.title,
                                    onChange: (e) => setModuleForm({ ...moduleForm, title: e.target.value }),
                                    required: true
                                })
                            ),
                            React.createElement('div', { className: 'mb-3' },
                                React.createElement('label', { className: 'creative-form-label' }, 'Description'),
                                React.createElement('textarea', {
                                    className: 'creative-form-input',
                                    rows: 3,
                                    value: moduleForm.description,
                                    onChange: (e) => setModuleForm({ ...moduleForm, description: e.target.value })
                                })
                            )
                        ),
                        React.createElement('div', { className: 'modal-footer' },
                            React.createElement('button', {
                                type: 'button',
                                className: 'creative-btn creative-btn-outline',
                                onClick: () => {
                                    setShowModuleModal(false);
                                    setEditingModule(null);
                                    setModuleForm({ title: '', description: '' });
                                }
                            }, 'Cancel'),
                            React.createElement('button', {
                                type: 'submit',
                                className: 'creative-btn creative-btn-primary'
                            }, editingModule ? 'Update Module' : 'Create Module')
                        )
                    )
                )
            )
        ),
        
        // Subpage Modal
        showSubpageModal && React.createElement('div', {
            className: 'modal fade show',
            style: { display: 'block', backgroundColor: 'rgba(0,0,0,0.5)' },
            onClick: () => {
                setShowSubpageModal(false);
                setEditingSubpage(null);
                setSubpageForm({ title: '', description: '', is_active: true });
                setSelectedModule(null);
            }
        },
            React.createElement('div', {
                className: 'modal-dialog',
                onClick: (e) => e.stopPropagation()
            },
                React.createElement('div', { className: 'modal-content' },
                    React.createElement('form', { onSubmit: editingSubpage ? handleUpdateSubpage : handleCreateSubpage },
                        React.createElement('div', { className: 'modal-header' },
                            React.createElement('h5', { className: 'modal-title' }, 
                                editingSubpage 
                                    ? 'Edit Subpage'
                                    : React.createElement('span', null, 
                                        'Add New Subpage to ',
                                        React.createElement('strong', null, selectedModule?.title)
                                    )
                            ),
                            React.createElement('button', {
                                type: 'button',
                                className: 'btn-close',
                                onClick: () => {
                                    setShowSubpageModal(false);
                                    setEditingSubpage(null);
                                    setSubpageForm({ title: '', description: '', is_active: true });
                                    setSelectedModule(null);
                                }
                            })
                        ),
                        React.createElement('div', { className: 'modal-body' },
                            React.createElement('div', { className: 'mb-3' },
                                React.createElement('label', { className: 'creative-form-label' }, 'Subpage Title ', React.createElement('span', { className: 'text-danger' }, '*')),
                                React.createElement('input', {
                                    type: 'text',
                                    className: 'creative-form-input',
                                    value: subpageForm.title,
                                    onChange: (e) => setSubpageForm({ ...subpageForm, title: e.target.value }),
                                    required: true
                                })
                            ),
                            React.createElement('div', { className: 'mb-3' },
                                React.createElement('label', { className: 'creative-form-label' }, 'Description'),
                                React.createElement('textarea', {
                                    className: 'creative-form-input',
                                    rows: 3,
                                    value: subpageForm.description,
                                    onChange: (e) => setSubpageForm({ ...subpageForm, description: e.target.value })
                                })
                            ),
                            React.createElement('div', { className: 'mb-3' },
                                React.createElement('div', { className: 'form-check' },
                                    React.createElement('input', {
                                        type: 'checkbox',
                                        className: 'form-check-input',
                                        checked: subpageForm.is_active,
                                        onChange: (e) => setSubpageForm({ ...subpageForm, is_active: e.target.checked })
                                    }),
                                    React.createElement('label', { className: 'form-check-label' }, 'Active (visible to students)')
                                )
                            )
                        ),
                        React.createElement('div', { className: 'modal-footer' },
                            React.createElement('button', {
                                type: 'button',
                                className: 'creative-btn creative-btn-outline',
                                onClick: () => {
                                    setShowSubpageModal(false);
                                    setEditingSubpage(null);
                                    setSubpageForm({ title: '', description: '', is_active: true });
                                    setSelectedModule(null);
                                }
                            }, 'Cancel'),
                            React.createElement('button', {
                                type: 'submit',
                                className: 'creative-btn creative-btn-primary'
                            }, editingSubpage ? 'Update Subpage' : 'Create Subpage')
                        )
                    )
                )
            )
        )
    );
}

// Initialize on DOM load - Updated: 2026-01-14 18:00
document.addEventListener('DOMContentLoaded', function() {
    console.log('[CourseHierarchy] DOM loaded, initializing... [v2026-01-14-18:00]');
    const element = document.getElementById('course-hierarchy-app');
    
    if (element) {
        console.log('[CourseHierarchy] Element found:', element);
        const courseId = element.dataset.courseId;
        const csrfToken = element.dataset.csrfToken;
        const userRole = element.dataset.userRole || 'teacher';
        
        console.log('[CourseHierarchy] Data:', { courseId, csrfToken, userRole });
        
        const root = createRoot(element);
        root.render(React.createElement(CourseHierarchy, { courseId, csrfToken, userRole }));
        console.log('[CourseHierarchy] Component rendered successfully! [v2026-01-14-18:00]');
    } else {
        console.error('[CourseHierarchy] Element #course-hierarchy-app NOT FOUND!');
        console.error('[CourseHierarchy] Available elements:', document.querySelectorAll('[id*="course"]'));
    }
});
