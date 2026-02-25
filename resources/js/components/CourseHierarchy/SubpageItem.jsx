import React, { useState } from 'react';
import EditSubpageModal from './EditSubpageModal';
import ConfirmDeleteModal from '../Common/ConfirmDeleteModal';

const SubpageItem = ({
    courseId,
    moduleId,
    subpage,
    userRole = 'teacher',
    dragHandleProps,
    onUpdate,
    onDelete
}) => {
    const [showEditModal, setShowEditModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);

    // Generate role-based URLs
    const getSubpageUrl = () => {
        if (userRole === 'admin') {
            return `/admin/courses/${courseId}/modules/${moduleId}/subpages/${subpage.id}`;
        }
        return `/teacher/courses/${courseId}/modules/${moduleId}/subpages/${subpage.id}`;
    };

    const getContentBuilderUrl = () => {
        if (userRole === 'admin') {
            return `/admin/courses/${courseId}/modules/${moduleId}/subpages/${subpage.id}/content-builder`;
        }
        return `/teacher/courses/${courseId}/modules/${moduleId}/subpages/${subpage.id}/content-builder`;
    };

    const handleUpdate = async (subpageData) => {
        const success = await onUpdate(moduleId, subpage.id, subpageData);
        if (success) {
            setShowEditModal(false);
        }
        return success;
    };

    const handleDelete = async () => {
        const success = await onDelete(moduleId, subpage.id);
        if (success) {
            setShowDeleteModal(false);
        }
        return success;
    };

    return (
        <div className="subpage-item border rounded p-3 mb-2 bg-white">
            <div className="d-flex align-items-center">
                {/* Drag Handle */}
                <div
                    {...dragHandleProps}
                    className="drag-handle me-3"
                    title="Drag to reorder"
                >
                    <i className="fas fa-grip-vertical text-muted"></i>
                </div>

                {/* Subpage Icon */}
                <div className="me-3">
                    <i className="fas fa-file-alt text-primary"></i>
                </div>

                {/* Subpage Info */}
                <div className="flex-grow-1">
                    <div className="d-flex align-items-center">
                        <h6 className="mb-0 me-2">
                            <a 
                                href={getSubpageUrl()}
                                className="text-decoration-none text-primary"
                                title="View subpage"
                            >
                                {subpage.title}
                            </a>
                        </h6>
                        {subpage.is_active ? (
                            <span className="badge bg-success">Active</span>
                        ) : (
                            <span className="badge bg-secondary">Inactive</span>
                        )}
                    </div>
                    {subpage.description && (
                        <small className="text-muted">{subpage.description}</small>
                    )}
                </div>

                {/* Action Buttons */}
                <div className="btn-group">
                    <a
                        href={getContentBuilderUrl()}
                        className="btn btn-sm btn-success"
                        title="Content Builder - Upload and manage content"
                    >
                        <i className="fas fa-plus"></i>
                    </a>
                    <button
                        type="button"
                        className="btn btn-sm btn-outline-primary"
                        onClick={() => setShowEditModal(true)}
                        title="Edit subpage"
                    >
                        <i className="fas fa-edit"></i>
                    </button>
                    <button
                        type="button"
                        className="btn btn-sm btn-outline-danger"
                        onClick={() => setShowDeleteModal(true)}
                        title="Delete subpage"
                    >
                        <i className="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            {/* Edit Modal */}
            <EditSubpageModal
                show={showEditModal}
                subpage={subpage}
                onClose={() => setShowEditModal(false)}
                onSubmit={handleUpdate}
            />

            {/* Delete Confirmation Modal */}
            <ConfirmDeleteModal
                show={showDeleteModal}
                title="Delete Subpage"
                message={`Are you sure you want to delete the subpage "${subpage.title}"?`}
                itemName={subpage.title}
                onClose={() => setShowDeleteModal(false)}
                onConfirm={handleDelete}
            />
        </div>
    );
};

export default SubpageItem;