import React, { useState, useEffect } from 'react';
import Modal from '../Common/Modal';

const CreateSubpageModal = ({ show, moduleId, onClose, onSubmit }) => {
    const [formData, setFormData] = useState({
        title: '',
        description: ''
    });
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    // Reset form when modal is opened
    useEffect(() => {
        if (show) {
            setFormData({ title: '', description: '' });
            setErrors({});
        }
    }, [show]);

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
            newErrors.title = 'Subpage title is required';
        }
        
        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            return;
        }

        setLoading(true);
        try {
            const success = await onSubmit(formData);
            if (success) {
                // Reset form
                setFormData({ title: '', description: '' });
                setErrors({});
            }
        } finally {
            setLoading(false);
        }
    };

    const handleClose = () => {
        if (!loading) {
            setFormData({ title: '', description: '' });
            setErrors({});
            onClose();
        }
    };

    return (
        <Modal
            show={show}
            onClose={handleClose}
            title="Create New Subpage"
            size="md"
        >
            <form onSubmit={handleSubmit}>
                <div className="modal-body">
                    <div className="mb-3">
                        <label htmlFor="subpage-title" className="form-label">
                            Subpage Title <span className="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            className={`form-control ${errors.title ? 'is-invalid' : ''}`}
                            id="subpage-title"
                            name="title"
                            value={formData.title}
                            onChange={handleChange}
                            placeholder="Enter subpage title"
                            disabled={loading}
                            autoFocus
                        />
                        {errors.title && (
                            <div className="invalid-feedback">{errors.title}</div>
                        )}
                    </div>

                    <div className="mb-3">
                        <label htmlFor="subpage-description" className="form-label">
                            Description
                        </label>
                        <textarea
                            className="form-control"
                            id="subpage-description"
                            name="description"
                            rows="3"
                            value={formData.description}
                            onChange={handleChange}
                            placeholder="Enter subpage description (optional)"
                            disabled={loading}
                        />
                        <div className="form-text">
                            Provide a brief description of what this subpage covers.
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
                                Creating...
                            </>
                        ) : (
                            <>
                                <i className="fas fa-plus me-2"></i>
                                Create Subpage
                            </>
                        )}
                    </button>
                </div>
            </form>
        </Modal>
    );
};

export default CreateSubpageModal;