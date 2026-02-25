import React from 'react';

const SuccessAlert = ({ message, onClose, className = '' }) => {
    return (
        <div className={`alert alert-success alert-dismissible ${className}`} role="alert">
            <i className="fas fa-check-circle me-2"></i>
            {message}
            {onClose && (
                <button
                    type="button"
                    className="btn-close"
                    onClick={onClose}
                    aria-label="Close"
                ></button>
            )}
        </div>
    );
};

export default SuccessAlert;