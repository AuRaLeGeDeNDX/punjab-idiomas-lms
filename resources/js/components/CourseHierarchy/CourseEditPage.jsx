import React, { useState, useEffect } from 'react';
import axios from 'axios';
import ModuleList from './ModuleList';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';
import SuccessAlert from '../Common/SuccessAlert';

const CourseEditPage = ({ courseId, csrfToken, userRole = 'teacher' }) => {
    const [courseData, setCourseData] = useState(null);
    const [modules, setModules] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    // Configure axios defaults
    useEffect(() => {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }, [csrfToken]);

    // Load course hierarchy data
    useEffect(() => {
        loadCourseHierarchy();
    }, [courseId]);

    const loadCourseHierarchy = async () => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/courses/${courseId}/hierarchy`);
            
            if (response.data.success) {
                setCourseData(response.data.data.course);
                setModules(response.data.data.modules);
            } else {
                setError('Failed to load course data');
            }
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load course data');
        } finally {
            setLoading(false);
        }
    };

    const showSuccess = (message) => {
        setSuccess(message);
        setTimeout(() => setSuccess(null), 5000);
    };

    const showError = (message) => {
        setError(message);
        setTimeout(() => setError(null), 5000);
    };

    const handleCreateModule = async (moduleData) => {
        try {
            const response = await axios.post(`/api/courses/${courseId}/modules`, moduleData);
            
            if (response.data.success) {
                setModules(prev => [...prev, response.data.data]);
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to create module');
            return false;
        }
    };

    const handleUpdateModule = async (moduleId, moduleData) => {
        try {
            const response = await axios.put(`/api/courses/${courseId}/modules/${moduleId}`, moduleData);
            
            if (response.data.success) {
                setModules(prev => prev.map(module => 
                    module.id === moduleId 
                        ? { ...module, ...response.data.data }
                        : module
                ));
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to update module');
            return false;
        }
    };

    const handleDeleteModule = async (moduleId) => {
        try {
            const response = await axios.delete(`/api/courses/${courseId}/modules/${moduleId}`);
            
            if (response.data.success) {
                setModules(prev => prev.filter(module => module.id !== moduleId));
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to delete module');
            return false;
        }
    };

    const handleReorderModules = async (moduleIds) => {
        try {
            const response = await axios.post(`/api/courses/${courseId}/modules/reorder`, {
                module_ids: moduleIds
            });
            
            if (response.data.success) {
                // Update local state to reflect new order
                const reorderedModules = moduleIds.map((id, index) => {
                    const module = modules.find(m => m.id === id);
                    return { ...module, order_index: index + 1 };
                });
                setModules(reorderedModules);
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to reorder modules');
            return false;
        }
    };

    const handleCreateSubpage = async (moduleId, subpageData) => {
        try {
            const response = await axios.post(`/api/courses/${courseId}/modules/${moduleId}/subpages`, subpageData);
            
            if (response.data.success) {
                setModules(prev => prev.map(module => 
                    module.id === moduleId 
                        ? { 
                            ...module, 
                            subpages: [...module.subpages, response.data.data],
                            subpages_count: module.subpages_count + 1
                        }
                        : module
                ));
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to create subpage');
            return false;
        }
    };

    const handleUpdateSubpage = async (moduleId, subpageId, subpageData) => {
        try {
            const response = await axios.put(`/api/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}`, subpageData);
            
            if (response.data.success) {
                setModules(prev => prev.map(module => 
                    module.id === moduleId 
                        ? {
                            ...module,
                            subpages: module.subpages.map(subpage =>
                                subpage.id === subpageId
                                    ? { ...subpage, ...response.data.data }
                                    : subpage
                            )
                        }
                        : module
                ));
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to update subpage');
            return false;
        }
    };

    const handleDeleteSubpage = async (moduleId, subpageId) => {
        try {
            const response = await axios.delete(`/api/courses/${courseId}/modules/${moduleId}/subpages/${subpageId}`);
            
            if (response.data.success) {
                setModules(prev => prev.map(module => 
                    module.id === moduleId 
                        ? {
                            ...module,
                            subpages: module.subpages.filter(subpage => subpage.id !== subpageId),
                            subpages_count: module.subpages_count - 1
                        }
                        : module
                ));
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to delete subpage');
            return false;
        }
    };

    const handleReorderSubpages = async (moduleId, subpageIds) => {
        try {
            const response = await axios.post(`/api/courses/${courseId}/modules/${moduleId}/subpages/reorder`, {
                subpage_ids: subpageIds
            });
            
            if (response.data.success) {
                // Update local state to reflect new order
                setModules(prev => prev.map(module => 
                    module.id === moduleId 
                        ? {
                            ...module,
                            subpages: subpageIds.map((id, index) => {
                                const subpage = module.subpages.find(s => s.id === id);
                                return { ...subpage, order_index: index + 1 };
                            })
                        }
                        : module
                ));
                showSuccess(response.data.message);
                return true;
            } else {
                showError(response.data.message);
                return false;
            }
        } catch (err) {
            showError(err.response?.data?.message || 'Failed to reorder subpages');
            return false;
        }
    };

    if (loading) {
        return <LoadingSpinner message="Loading course hierarchy..." />;
    }

    return (
        <div className="course-hierarchy-manager">
            {error && <ErrorAlert message={error} onClose={() => setError(null)} />}
            {success && <SuccessAlert message={success} onClose={() => setSuccess(null)} />}
            
            <div className="card">
                <div className="card-header">
                    <div className="d-flex justify-content-between align-items-center">
                        <h5 className="mb-0">
                            <i className="fas fa-sitemap me-2"></i>
                            Course Structure
                        </h5>
                        <small className="text-muted">
                            {courseData?.title}
                        </small>
                    </div>
                </div>
                <div className="card-body">
                    <ModuleList
                        courseId={courseId}
                        modules={modules}
                        userRole={userRole}
                        onCreateModule={handleCreateModule}
                        onUpdateModule={handleUpdateModule}
                        onDeleteModule={handleDeleteModule}
                        onReorderModules={handleReorderModules}
                        onCreateSubpage={handleCreateSubpage}
                        onUpdateSubpage={handleUpdateSubpage}
                        onDeleteSubpage={handleDeleteSubpage}
                        onReorderSubpages={handleReorderSubpages}
                    />
                </div>
            </div>
        </div>
    );
};

export default CourseEditPage;