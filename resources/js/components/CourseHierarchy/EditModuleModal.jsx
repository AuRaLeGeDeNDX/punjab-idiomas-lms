import React, { useState, useEffect } from 'react';
import Modal from '../Common/Modal';

const EditModuleModal = ({ show, module, onClose, onSubmit }) => {
    const [formData, setFormData] = useState({
        title: '',
        description: ''
    });
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    // Initialize form data when module changes
    useEffect(() => {
        if (module) {
            setFormData({
                title: module.title || '',
                description: module.description || ''
            });
        }
    }, [module]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: null
            }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        // Validate form
        const newErrors = {};
        if (!formData.title.trim()) {
            newErrors.title = 'Module title is required';
        }
        
        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setLoading(true);
        try {
            const success = await onSubmit(formData);
            if (success) {
                setErrors({});
            }
        } finally {
            setLoading(false);
        }
    };

    const handleClose = () => {
        if (!loading) {
            setErrors({});
            onClose();
        }
    };

    return (
        <Modal
            show={show}
            onClose={handleClose}
            title="Edit Module"
            size="md"
        >
            <form onSubmit={handleSubmit}>
                <div className="modal-body">
                    <div className="mb-3">
                        <label htmlFor="edit-module-title" className="form-label">
                            Module Title <span className="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            className={`form-control ${errors.title ? 'is-invalid' : ''}`}
                            id="edit-module-title"
                            name="title"
                            value={formData.title}
                            onChange={handleChange}
                            placeholder="Enter module title"
                            disabled={loading}
                            autoFocus
                        />
                        {errors.title && (
                            <div className="invalid-feedback">{errors.title}</div>
                        )}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="edit-module-description" className="form-label">
                            Description
                        </label>
                        <textarea
                            className="form-control"
                            id="edit-module-description"
                            name="description"
                            rows="3"
                            value={formData.description}
                            onChange={handleChange}
                            placeholder="Enter module description (optional)"
                            disabled={loading}
                        />
                        <div className="form-text">
                            Provide a brief description of what this module covers.
                        </div>
                    </div>
                </div>

                <div className="modal-footer">
                    <button
                        type="button"
                        className="btn btn-secondary"
                        onClick={handleClose}
                        disabled={loading}
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="btn btn-primary"
                        disabled={loading}
                    >
                        {loading ? (
                            <>
                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Updating...
                            </>
                        ) : (
                            <>
                                <i className="fas fa-save me-2"></i>
                                Update Module
                            </>
                        )}
                    </button>
                </div>
            </form>
        </Modal>
    );
};

export default EditModuleModal;