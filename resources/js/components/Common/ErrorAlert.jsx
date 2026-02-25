import React from 'react';

const ErrorAlert = ({ message, onClose, className = '' }) => {
    return (
        <div className={`alert alert-danger alert-dismissible ${className}`} role="alert">
            <i className="fas fa-exclamation-triangle me-2"></i>
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

export default ErrorAlert;