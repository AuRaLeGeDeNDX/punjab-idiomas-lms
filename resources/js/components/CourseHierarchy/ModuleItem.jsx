import React, { useState } from 'react';
import SubpageList from './SubpageList';
import EditModuleModal from './EditModuleModal';
import ConfirmDeleteModal from '../Common/ConfirmDeleteModal';

const ModuleItem = ({
    courseId,
    module,
    userRole = 'teacher',
    dragHandleProps,
    onUpdate,
    onDelete,
    onCreateSubpage,
    onUpdateSubpage,
    onDeleteSubpage,
    onReorderSubpages
}) => {
    const [isExpanded, setIsExpanded] = useState(false);
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    const handleUpdate = async (moduleData) => {
        const success = await onUpdate(module.id, moduleData);
        if (success) {
            setShowEditModal(false);
        }
        return success;
    };

    const handleDelete = async () => {
        const success = await onDelete(module.id);
        if (success) {
            setShowDeleteModal(false);
        }
        return success;
    };

    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };

    return (
        <div className="module-item card mb-3">
            <div className="card-header">
                <div className="d-flex align-items-center">
                    {/* Drag Handle */}
                    <div
                        {...dragHandleProps}
                        className="drag-handle me-3"
                        title="Drag to reorder"
                    >
                        <i className="fas fa-grip-vertical text-muted"></i>
                    </div>

                    {/* Expand/Collapse Button */}
                    <button
                        type="button"
                        className="btn btn-sm btn-ghost me-2"
                        onClick={toggleExpanded}
                        title={isExpanded ? 'Collapse' : 'Expand'}
                    >
                        <i className={`fas fa-chevron-${isExpanded ? 'down' : 'right'}`}></i>
                    </button>

                    {/* Module Info */}
                    <div className="flex-grow-1">
                        <div className="d-flex align-items-center">
                            <h6 className="mb-0 me-2">{module.title}</h6>
                            <span className="badge bg-secondary me-2">
                                {module.subpages_count} subpages
                            </span>
                            {module.is_published ? (
                                <span className="badge bg-success">Published</span>
                            ) : (
                                <span className="badge bg-warning">Draft</span>
                            )}
                        </div>
                        {module.description && (
                            <small className="text-muted">{module.description}</small>
                        )}
                    </div>

                    {/* Action Buttons */}
                    <div className="btn-group">
                        <button
                            type="button"
                            className="btn btn-sm btn-outline-primary"
                            onClick={() => setShowEditModal(true)}
                            title="Edit module"
                        >
                            <i className="fas fa-edit"></i>
                        </button>
                        <button
                            type="button"
                            className="btn btn-sm btn-outline-danger"
                            onClick={() => setShowDeleteModal(true)}
                            title="Delete module"
                        >
                            <i className="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            {/* Subpages Section */}
            {isExpanded && (
                <div className="card-body">
                    <SubpageList
                        courseId={courseId}
                        moduleId={module.id}
                        subpages={module.subpages}
                        userRole={userRole}
                        onCreateSubpage={onCreateSubpage}
                        onUpdateSubpage={onUpdateSubpage}
                        onDeleteSubpage={onDeleteSubpage}
                        onReorderSubpages={onReorderSubpages}
                    />
                </div>
            )}

            {/* Edit Modal */}
            <EditModuleModal
                show={showEditModal}
                module={module}
                onClose={() => setShowEditModal(false)}
                onSubmit={handleUpdate}
            />

            {/* Delete Confirmation Modal */}
            <ConfirmDeleteModal
                show={showDeleteModal}
                title="Delete Module"
                message={`Are you sure you want to delete the module "${module.title}"?`}
                itemName={module.title}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={handleDelete}
            />
        </div>
    );
};

export default ModuleItem;