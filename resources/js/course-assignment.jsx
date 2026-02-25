import React from 'react';
import { createRoot } from 'react-dom/client';
import AssignStudentsModal from './components/CourseAssignment/AssignStudentsModal';

// Make components available globally for use in Blade templates
window.CourseAssignment = {
    AssignStudentsModal,
    React,
    createRoot
};

// Initialize assignment modal functionality
window.initializeAssignmentModal = function (config) {
    const modalContainer = document.getElementById(config.containerId);
    if (!modalContainer) {
        console.error('Modal container not found:', config.containerId);
        return null;
    }

    let modalRoot = null;

    return {
        open: () => {
            if (!modalRoot) {
                modalRoot = createRoot(modalContainer);
            }

            modalRoot.render(
                React.createElement(AssignStudentsModal, {
                    show: true,
                    onClose: () => {
                        // Close logic
                        modalRoot.render(
                            React.createElement(AssignStudentsModal, {
                                show: false,
                                onClose: config.onClose,
                                course: config.course,
                                onAssignmentComplete: config.onAssignmentComplete
                            })
                        );
                        if (config.onClose) config.onClose();
                    },
                    course: config.course,
                    onAssignmentComplete: config.onAssignmentComplete
                })
            );
        },
        close: () => {
            if (modalRoot) {
                modalRoot.render(
                    React.createElement(AssignStudentsModal, {
                        show: false,
                        onClose: config.onClose,
                        course: config.course,
                        onAssignmentComplete: config.onAssignmentComplete
                    })
                );
            }
        }
    };
};