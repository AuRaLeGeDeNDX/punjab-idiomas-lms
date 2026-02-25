import React from 'react';
import Modal from './Modal';

const ConfirmDeleteModal = ({ 
    show, 
    onClose, 
    onConfirm, 
    title = 'Confirm Delete', 
    message = 'Are you sure you want to delete this item?',
    itemName = '',
    loading = false 
}) => {
    const handleConfirm = () => {
        onConfirm();
    };

    return (
        <Modal
            show={show}
            onClose={onClose}
            title={title}
            size="sm"
        >
            <div className="modal-body">
                <div className="text-center">
                    <i className="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h6 className="mb-3">{message}</h6>
                    {itemName && (
                        <p className="text-muted mb-3">
                            <strong>"{itemName}"</strong>
                        </p>
                    )}
                    <p className="text-muted small">
                        This action cannot be undone.
                    </p>
                </div>
            </div>
            <div className="modal-footer">
                <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={onClose}
                    disabled={loading}
                >
                    Cancel
                </button>
                <button
                    type="button"
                    className="btn btn-danger"
                    onClick={handleConfirm}
                    disabled={loading}
                >
                    {loading ? (
                        <>
                            <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Deleting...
                        </>
                    ) : (
                        <>
                            <i className="fas fa-trash me-2"></i>
                            Delete
                        </>
                    )}
                </button>
            </div>
        </Modal>
    );
};

export default ConfirmDeleteModal;